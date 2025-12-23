<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($car_id)
    {
        $user = auth()->user();
        $car  = Car::find($car_id);
        return view('reservation.create', compact('car', 'user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $car_id)
    {
        // dd($request->all());
        $request->validate([
            'full-name'         => 'required|string|max:255',
            'email'             => 'required|email',
            'reservation_dates' => 'required',
        ]);

        $car  = Car::find($car_id);
        $user = Auth::user();

        // 👉 আগের 2-রিজার্ভেশন লিমিট চেক ব্লকটা মুছে ফেলা হয়েছে

        // extract start and end date from the request
        $reservation_dates = explode(' to ', $request->reservation_dates);
        $start = Carbon::parse($reservation_dates[0]);
        $end   = Carbon::parse($reservation_dates[1]);

        $reservation = new Reservation();
        $reservation->user()->associate($user);
        $reservation->car()->associate($car);
        $reservation->start_date    = $start;
        $reservation->end_date      = $end;
        $reservation->days          = $start->diffInDays($end);
        $reservation->price_per_day = $car->price_per_day;
        $reservation->total_price   = $reservation->days * $reservation->price_per_day;
        $reservation->status        = 'Pending';
        // চাইলে সরাসরি দেখাতে পারো যে কার্ডেই দেবে:
        // $reservation->payment_method  = 'Card (Fake)';
        $reservation->payment_method  = 'At store';
        $reservation->payment_status  = 'Pending';
        $reservation->save();

        // গাড়ির স্ট্যাটাস আপডেট
        $car->status = 'Reserved';
        $car->save();

        // ✅ reserve হলেই checkout/reservation/{id} এ নিয়ে যাও
        return redirect()
            ->route('checkout.reservation', $reservation->id)
            ->with('ok', 'Reservation created! Please complete payment.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation)
    {
        //
    }

    // Edit and Update Payment status (admin)
    public function editPayment(Reservation $reservation)
    {
        $reservation = Reservation::find($reservation->id);
        return view('admin.updatePayment', compact('reservation'));
    }

    public function updatePayment(Reservation $reservation, Request $request)
    {
        $reservation = Reservation::find($reservation->id);
        $reservation->payment_status = $request->payment_status;
        $reservation->save();
        return redirect()->route('adminDashboard');
    }

    // Edit and Update Reservation Status (admin)
    public function editStatus(Reservation $reservation)
    {
        $reservation = Reservation::find($reservation->id);
        return view('admin.updateStatus', compact('reservation'));
    }

    public function updateStatus(Reservation $reservation, Request $request)
    {
        $reservation = Reservation::find($reservation->id);
        $reservation->status = $request->status;

        $car = $reservation->car;
        if ($request->status == 'Ended' || $request->status == 'Canceled') {
            $car->status = 'Available';
            $car->save();
        }

        $reservation->save();
        return redirect()->route('adminDashboard');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reservation $reservation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        //
    }
}
