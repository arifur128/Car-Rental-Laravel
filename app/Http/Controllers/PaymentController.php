<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    /**
     * -------------------------
     * Generic checkout (optional)
     * -------------------------
     */
    public function create()
    {
        return view('checkout');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amount'   => ['required','numeric','min:1'],
            'currency' => ['required','in:BDT,USD'],
            'note'     => ['nullable','string','max:255'],
        ]);

        $payment = Payment::create([
            'user_id'        => auth()->id(),
            'order_id'       => null,
            'amount'         => $data['amount'],
            'currency'       => $data['currency'],
            'status'         => 'pending',
            'transaction_id' => Str::uuid()->toString(),
            'meta'           => [
                'note'       => $data['note'] ?? null,
                'client_ip'  => $request->ip(),
                'user_agent' => $request->userAgent(),
                'source'     => 'generic_checkout',
            ],
        ]);

        $this->simulateGateway($payment);

        return $payment->status === 'success'
            ? redirect()->route('payment.success')->with('payment_id', $payment->id)
            : redirect()->route('payment.cancel')->with('payment_id', $payment->id);
    }

    /**
     * ---------------------------------
     * Reservation-based checkout (main)
     * ---------------------------------
     */
    public function createForReservation(Request $request, Reservation $reservation)
    {
        // amount resolve: তোমার reservations টেবিলে total_price আছে
        $amount   = (float) $reservation->total_price;
        $currency = 'BDT';

        abort_if($amount <= 0, 404, 'No payable amount found for this reservation.');

        return view('payment.checkout_reservation', compact('reservation','amount','currency'));
    }

    public function storeForReservation(Request $request, Reservation $reservation)
    {
        $data = $request->validate([
            'amount'   => ['required','numeric','min:1'],
            'currency' => ['required','in:BDT,USD'],
            'note'     => ['nullable','string','max:255'],
        ]);

        $payment = Payment::create([
            'user_id'        => auth()->id(),
            'order_id'       => $reservation->id, // order_id-তে reservation id
            'amount'         => $data['amount'],
            'currency'       => $data['currency'],
            'status'         => 'pending',
            'transaction_id' => Str::uuid()->toString(),
            'meta'           => [
                'note'        => $data['note'] ?? null,
                'source'      => 'reservation_checkout',
                'reservation' => $reservation->id,
                'client_ip'   => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ],
        ]);

        $this->simulateGateway($payment);

        // success হলে reservation.payment_status আপডেট (কলাম থাকলে)
        if ($payment->status === 'success' && Schema::hasColumn($reservation->getTable(), 'payment_status')) {
            $reservation->payment_status = 'Paid';
            $reservation->save();
        }

        return $payment->status === 'success'
            ? redirect()->route('payment.success')->with('payment_id', $payment->id)
            : redirect()->route('payment.cancel')->with('payment_id', $payment->id);
    }

    /**
     * --------------
     * Success/Cancel
     * --------------
     */
    public function success(Request $request)
    {
        $payment = $this->resolveFlashedPayment($request);
        return view('payment.success', compact('payment'));
    }

    public function cancel(Request $request)
    {
        $payment = $this->resolveFlashedPayment($request);
        return view('payment.cancel', compact('payment'));
    }

    private function resolveFlashedPayment(Request $request): ?Payment
    {
        $id = session('payment_id');
        if (!$id) return null;
        return Payment::find($id);
    }

    /**
     * --------------------
     * PDF Invoice Download
     * --------------------
     */
    public function downloadInvoice(Payment $payment)
    {
        // শুধুমাত্র owner বা admin ডাউনলোড করতে পারবে
        $user = auth()->user();
        if (!$user || ($payment->user_id !== $user->id && (($user->role ?? null) !== 'admin'))) {
            abort(403, 'Unauthorized');
        }

        $pdf = Pdf::loadView('payment.invoice', compact('payment'))
                  ->setPaper('a4');

        $fileName = 'invoice_'.$payment->id.'.pdf';
        return $pdf->download($fileName);
    }

    /**
     * -------------------
     * Fake Gateway Helper
     * -------------------
     */
    private function simulateGateway(Payment $payment): void
    {
        $autoSuccess = (bool) config('payment.auto_success', true); // config/payment.php
        $delayMs     = (int) config('payment.delay_ms', 600);

        if ($delayMs > 0) {
            usleep($delayMs * 1000); // simulate latency
        }

        $isSuccess = $autoSuccess ? true : (mt_rand(1, 100) <= 70); // 70% success when auto_success=false
        $payment->status = $isSuccess ? 'success' : 'failed';
        $payment->save();
    }
}
