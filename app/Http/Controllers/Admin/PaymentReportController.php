<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentReportController extends Controller
{
    public function index(Request $req)
    {
        $from = $req->input('from', now()->subDays(30)->toDateString());
        $to   = $req->input('to',   now()->toDateString());
        $status = $req->input('status'); // pending/success/failed/refunded or null

        $base = Payment::whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);
        if ($status) $base->where('status', $status);

        // KPIs
        $totalCount   = (clone $base)->count();
        $successCount = (clone $base)->where('status','success')->count();
        $failedCount  = (clone $base)->where('status','failed')->count();
        $sumAmount    = (clone $base)->where('status','success')->sum('amount');
        $successRate  = $totalCount ? round(($successCount/$totalCount)*100,2) : 0;

        // By day series
        $series = (clone $base)
            ->selectRaw('DATE(created_at) as d, SUM(CASE WHEN status="success" THEN amount ELSE 0 END) as revenue, COUNT(*) as cnt')
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        // Top customers (by paid amount)
        $topUsers = (clone $base)->where('status','success')
            ->selectRaw('user_id, SUM(amount) as total_paid, COUNT(*) as n')
            ->groupBy('user_id')
            ->orderByDesc('total_paid')
            ->with('user:id,name,email')
            ->limit(10)->get();

        // Reservations snapshot (optional)
        $resPaid = Reservation::whereBetween(DB::raw('DATE(created_at)'), [$from,$to])
                    ->selectRaw('payment_status, COUNT(*) c')
                    ->groupBy('payment_status')->pluck('c','payment_status');

        return view('admin.reports.payments', compact(
            'from','to','status','totalCount','successCount','failedCount','sumAmount','successRate','series','topUsers','resPaid'
        ));
    }

    public function exportCsv(Request $req)
    {
        $from = $req->input('from', now()->subDays(30)->toDateString());
        $to   = $req->input('to',   now()->toDateString());
        $q = Payment::whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->orderBy('created_at','desc')
            ->get(['id','user_id','order_id','amount','currency','status','transaction_id','created_at']);

        $fname = "payments_{$from}_to_{$to}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fname\"",
        ];

        $callback = function() use ($q) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id','user_id','order_id','amount','currency','status','transaction_id','created_at']);
            foreach($q as $p){
                fputcsv($out, [$p->id,$p->user_id,$p->order_id,$p->amount,$p->currency,$p->status,$p->transaction_id,$p->created_at]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
