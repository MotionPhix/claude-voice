<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index()
    {
        return view('reports.index');
    }

    public function revenue(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'client_id' => 'nullable|exists:clients,id',
            'currency' => 'nullable|string|size:3',
            'group_by' => 'nullable|in:day,week,month,quarter,year',
        ]);

        $dateFrom = $validated['date_from'] ? \Carbon\Carbon::parse($validated['date_from']) : now()->subYear();
        $dateTo = $validated['date_to'] ? \Carbon\Carbon::parse($validated['date_to']) : now();
        $groupBy = $validated['group_by'] ?? 'month';

        // Revenue over time
        $revenueQuery = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [$dateFrom, $dateTo]);

        if (isset($validated['client_id'])) {
            $revenueQuery->where('client_id', $validated['client_id']);
        }

        if (isset($validated['currency'])) {
            $revenueQuery->where('currency', $validated['currency']);
        }

        $revenueData = $this->groupRevenueData($revenueQuery, $groupBy);

        // Top clients by revenue
        $topClientsQuery = Invoice::with('client')
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$dateFrom, $dateTo]);

        if (isset($validated['currency'])) {
            $topClientsQuery->where('currency', $validated['currency']);
        }

        $topClients = $topClientsQuery
            ->select('client_id', DB::raw('SUM(total) as total_revenue'), DB::raw('COUNT(*) as invoice_count'))
            ->groupBy('client_id')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();

        // Revenue by currency
        $revenueByCurrency = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [$dateFrom, $dateTo])
            ->when(isset($validated['client_id']), function($q) use ($validated) {
                return $q->where('client_id', $validated['client_id']);
            })
            ->select('currency', DB::raw('SUM(total) as total_revenue'), DB::raw('COUNT(*) as invoice_count'))
            ->groupBy('currency')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // Summary stats
        $stats = $this->invoiceService->getInvoiceStats([
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'client_id' => $validated['client_id'] ?? null,
            'currency' => $validated['currency'] ?? null,
        ]);

        $clients = Client::where('is_active', true)->get();

        return view('reports.revenue', compact(
            'revenueData', 'topClients', 'revenueByCurrency', 'stats', 'clients', 'validated'
        ));
    }

    public function outstanding(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'currency' => 'nullable|string|size:3',
            'overdue_only' => 'nullable|boolean',
        ]);

        $query = Invoice::with(['client', 'payments'])
            ->whereIn('status', ['sent', 'overdue'])
            ->where('total', '>', DB::raw('amount_paid'));

        if (isset($validated['client_id'])) {
            $query->where('client_id', $validated['client_id']);
        }

        if (isset($validated['currency'])) {
            $query->where('currency', $validated['currency']);
        }

        if ($validated['overdue_only'] ?? false) {
            $query->where('status', 'overdue');
        }

        $outstandingInvoices = $query->orderBy('due_date', 'asc')->paginate(20);

        // Summary by age
        $agingSummary = [
            'current' => Invoice::whereIn('status', ['sent', 'overdue'])
                ->where('due_date', '>=', now())
                ->sum(DB::raw('total - amount_paid')),
            '1_30_days' => Invoice::whereIn('status', ['sent', 'overdue'])
                ->whereBetween('due_date', [now()->subDays(30), now()->subDay()])
                ->sum(DB::raw('total - amount_paid')),
            '31_60_days' => Invoice::whereIn('status', ['sent', 'overdue'])
                ->whereBetween('due_date', [now()->subDays(60), now()->subDays(31)])
                ->sum(DB::raw('total - amount_paid')),
            '61_90_days' => Invoice::whereIn('status', ['sent', 'overdue'])
                ->whereBetween('due_date', [now()->subDays(90), now()->subDays(61)])
                ->sum(DB::raw('total - amount_paid')),
            'over_90_days' => Invoice::whereIn('status', ['sent', 'overdue'])
                ->where('due_date', '<', now()->subDays(90))
                ->sum(DB::raw('total - amount_paid')),
        ];

        $clients = Client::where('is_active', true)->get();

        return view('reports.outstanding', compact('outstandingInvoices', 'agingSummary', 'clients', 'validated'));
    }

    public function payments(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'client_id' => 'nullable|exists:clients,id',
            'method' => 'nullable|in:cash,check,bank_transfer,credit_card,paypal,other',
            'currency' => 'nullable|string|size:3',
        ]);

        $dateFrom = $validated['date_from'] ? \Carbon\Carbon::parse($validated['date_from']) : now()->subMonth();
        $dateTo = $validated['date_to'] ? \Carbon\Carbon::parse($validated['date_to']) : now();

        $query = Payment::with(['invoice.client'])
            ->whereBetween('payment_date', [$dateFrom, $dateTo]);

        if (isset($validated['client_id'])) {
            $query->whereHas('invoice', function($q) use ($validated) {
                $q->where('client_id', $validated['client_id']);
            });
        }

        if (isset($validated['method'])) {
            $query->where('method', $validated['method']);
        }

        if (isset($validated['currency'])) {
            $query->where('currency', $validated['currency']);
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(20);

        // Payment method breakdown
        $paymentMethods = Payment::whereBetween('payment_date', [$dateFrom, $dateTo])
            ->when(isset($validated['client_id']), function($q) use ($validated) {
                return $q->whereHas('invoice', function($subQ) use ($validated) {
                    $subQ->where('client_id', $validated['client_id']);
                });
            })
            ->when(isset($validated['currency']), function($q) use ($validated) {
                return $q->where('currency', $validated['currency']);
            })
            ->select('method', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as payment_count'))
            ->groupBy('method')
            ->orderBy('total_amount', 'desc')
            ->get();

        // Daily payment totals
        $dailyPayments = Payment::whereBetween('payment_date', [$dateFrom, $dateTo])
            ->when(isset($validated['client_id']), function($q) use ($validated) {
                return $q->whereHas('invoice', function($subQ) use ($validated) {
                    $subQ->where('client_id', $validated['client_id']);
                });
            })
            ->when(isset($validated['currency']), function($q) use ($validated) {
                return $q->where('currency', $validated['currency']);
            })
            ->select(DB::raw('DATE(payment_date) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $clients = Client::where('is_active', true)->get();

        return view('reports.payments', compact('payments', 'paymentMethods', 'dailyPayments', 'clients', 'validated'));
    }

    public function clients(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $dateFrom = $validated['date_from'] ? \Carbon\Carbon::parse($validated['date_from']) : now()->subYear();
        $dateTo = $validated['date_to'] ? \Carbon\Carbon::parse($validated['date_to']) : now();

        $clientStats = Client::with(['invoices' => function($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('issue_date', [$dateFrom, $dateTo]);
        }])
            ->withCount(['invoices as total_invoices'])
            ->get()
            ->map(function($client) use ($dateFrom, $dateTo) {
                $invoices = $client->invoices;

                return [
                    'client' => $client,
                    'total_invoices' => $invoices->count(),
                    'total_revenue' => $invoices->where('status', 'paid')->sum('total'),
                    'outstanding_amount' => $invoices->whereIn('status', ['sent', 'overdue'])->sum('total'),
                    'overdue_amount' => $invoices->where('status', 'overdue')->sum('total'),
                    'avg_payment_time' => $this->calculateAveragePaymentTime($invoices->where('status', 'paid')),
                ];
            })
            ->sortByDesc('total_revenue');

        return view('reports.clients', compact('clientStats', 'validated'));
    }

    public function exportPdf(Request $request)
    {
        $report = $request->input('report');
        $filters = $request->except('report');

        switch ($report) {
            case 'revenue':
                return $this->exportRevenuePdf($filters);
            case 'outstanding':
                return $this->exportOutstandingPdf($filters);
            case 'payments':
                return $this->exportPaymentsPdf($filters);
            case 'clients':
                return $this->exportClientsPdf($filters);
            default:
                abort(404);
        }
    }

    private function groupRevenueData($query, $groupBy)
    {
        $dateFormat = match($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'quarter' => '%Y-Q%q',
            'year' => '%Y',
            default => '%Y-%m',
        };

        return $query->select(
            DB::raw("DATE_FORMAT(paid_at, '{$dateFormat}') as period"),
            DB::raw('SUM(total) as total_revenue'),
            DB::raw('COUNT(*) as invoice_count')
        )
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    private function calculateAveragePaymentTime($paidInvoices)
    {
        if ($paidInvoices->isEmpty()) {
            return null;
        }

        $totalDays = $paidInvoices->sum(function($invoice) {
            return \Carbon\Carbon::parse($invoice->paid_at)->diffInDays($invoice->issue_date);
        });

        return round($totalDays / $paidInvoices->count(), 1);
    }

    private function exportRevenuePdf($filters)
    {
        // Implementation for PDF export would go here
        return response()->json(['message' => 'PDF export functionality coming soon']);
    }

    private function exportOutstandingPdf($filters)
    {
        // Implementation for PDF export would go here
        return response()->json(['message' => 'PDF export functionality coming soon']);
    }

    private function exportPaymentsPdf($filters)
    {
        // Implementation for PDF export would go here
        return response()->json(['message' => 'PDF export functionality coming soon']);
    }

    private function exportClientsPdf($filters)
    {
        // Implementation for PDF export would go here
        return response()->json(['message' => 'PDF export functionality coming soon']);
    }
}
