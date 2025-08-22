<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_invoices' => Invoice::count(),
            'total_revenue' => Invoice::where('status', 'paid')->sum('total'),
            'pending_amount' => Invoice::whereIn('status', ['sent', 'overdue'])->sum('total'),
            'overdue_invoices' => Invoice::overdue()->count(),
            'active_clients' => Client::where('is_active', true)->count(),
        ];

        $recent_invoices = Invoice::with('client')
            ->latest()
            ->take(5)
            ->get();

        $overdue_invoices = Invoice::overdue()
            ->with('client')
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Monthly revenue chart data
        $monthly_revenue = Invoice::where('status', 'paid')
            ->selectRaw('YEAR(paid_at) as year, MONTH(paid_at) as month, SUM(total) as revenue')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get()
            ->reverse();

        return view('dashboard', compact(
            'stats', 'recent_invoices', 'overdue_invoices', 'monthly_revenue'
        ));
    }
}
