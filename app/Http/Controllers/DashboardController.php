<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Calculate comprehensive stats
        $totalInvoices = Invoice::count();
        $totalRevenue = Invoice::where('status', 'paid')->sum('total');
        $pendingAmount = Invoice::whereIn('status', ['sent', 'overdue'])->sum('total');
        $overdueAmount = Invoice::where('status', 'overdue')->sum('total');
        $draftCount = Invoice::where('status', 'draft')->count();
        $paidCount = Invoice::where('status', 'paid')->count();
        $overdueCount = Invoice::where('status', 'overdue')->count();
        $clientsCount = Client::where('is_active', true)->count();

        // Calculate growth percentages (vs last month)
        $lastMonthRevenue = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [
                Carbon::now()->subMonth(2)->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth()
            ])
            ->sum('total');
        
        $thisMonthRevenue = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ])
            ->sum('total');

        $revenueGrowth = $lastMonthRevenue > 0 
            ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
            : 0;

        $lastMonthInvoices = Invoice::whereBetween('created_at', [
            Carbon::now()->subMonth(2)->startOfMonth(),
            Carbon::now()->subMonth()->endOfMonth()
        ])->count();

        $thisMonthInvoices = Invoice::whereBetween('created_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])->count();

        $invoiceGrowth = $lastMonthInvoices > 0
            ? (($thisMonthInvoices - $lastMonthInvoices) / $lastMonthInvoices) * 100
            : 0;

        $stats = [
            'total_invoices' => $totalInvoices,
            'total_revenue' => $totalRevenue,
            'pending_amount' => $pendingAmount,
            'overdue_amount' => $overdueAmount,
            'draft_count' => $draftCount,
            'paid_count' => $paidCount,
            'overdue_count' => $overdueCount,
            'clients_count' => $clientsCount,
            'revenue_growth' => round($revenueGrowth, 1),
            'invoice_growth' => round($invoiceGrowth, 1),
        ];

        // Recent invoices
        $recentInvoices = Invoice::with('client')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client' => [
                        'name' => $invoice->client->name,
                    ],
                    'total' => $invoice->total,
                    'status' => $invoice->status,
                    'due_date' => $invoice->due_date->toDateString(),
                    'created_at' => $invoice->created_at->toDateString(),
                ];
            });

        // Upcoming due invoices (next 30 days)
        $upcomingDue = Invoice::whereIn('status', ['sent', 'overdue'])
            ->where('due_date', '<=', Carbon::now()->addDays(30))
            ->with('client')
            ->orderBy('due_date')
            ->take(5)
            ->get()
            ->map(function ($invoice) {
                $daysUntilDue = Carbon::now()->diffInDays($invoice->due_date, false);
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client' => [
                        'name' => $invoice->client->name,
                    ],
                    'total' => $invoice->total,
                    'due_date' => $invoice->due_date->toDateString(),
                    'days_until_due' => $daysUntilDue,
                ];
            });

        // Monthly revenue data for chart (last 6 months)
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->where('paid_at', '>=', Carbon::now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(paid_at, "%Y-%m") as month, SUM(total) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::createFromFormat('Y-m', $item->month)->format('M Y'),
                    'revenue' => $item->revenue,
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recent_invoices' => $recentInvoices,
            'upcoming_due' => $upcomingDue,
            'monthly_revenue' => $monthlyRevenue,
        ]);
    }
}
