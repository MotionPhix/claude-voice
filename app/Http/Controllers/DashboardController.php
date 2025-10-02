<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Payment;
use App\Enums\MembershipRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $membership = current_membership();

        if (!$membership) {
            abort(403, 'No active membership found.');
        }

        // Route to role-specific dashboard based on user's role
        return match ($membership->role) {
            MembershipRole::Owner, MembershipRole::Admin => $this->adminDashboard(),
            MembershipRole::Manager => $this->managerDashboard(),
            MembershipRole::Accountant => $this->accountantDashboard(),
            MembershipRole::User => $this->userDashboard(),
            default => abort(403, 'Invalid role'),
        };
    }

    private function adminDashboard()
    {
        $stats = $this->getAdminStats();
        $recentInvoices = $this->getRecentInvoices();
        $recentActivity = $this->getRecentActivity();
        $systemAlerts = $this->getSystemAlerts();
        $organizationMetrics = $this->getOrganizationMetrics();

        return Inertia::render('dashboard/AdminDashboard', [
            'stats' => $stats,
            'recentInvoices' => $recentInvoices,
            'recentActivity' => $recentActivity,
            'systemAlerts' => $systemAlerts,
            'organizationMetrics' => $organizationMetrics,
        ]);
    }

    private function managerDashboard()
    {
        $stats = $this->getManagerStats();
        $recentInvoices = $this->getRecentInvoices();
        $upcomingDue = $this->getUpcomingDue();
        $teamActivity = $this->getTeamActivity();
        $clientMetrics = $this->getClientMetrics();

        return Inertia::render('dashboard/ManagerDashboard', [
            'stats' => $stats,
            'recentInvoices' => $recentInvoices,
            'upcomingDue' => $upcomingDue,
            'teamActivity' => $teamActivity,
            'clientMetrics' => $clientMetrics,
        ]);
    }

    private function accountantDashboard()
    {
        $stats = $this->getAccountantStats();
        $recentInvoices = $this->getRecentInvoices();
        $recentPayments = $this->getRecentPayments();
        $paymentSummary = $this->getPaymentSummary();
        $overdueInvoices = $this->getOverdueInvoices();

        return Inertia::render('dashboard/AccountantDashboard', [
            'stats' => $stats,
            'recentInvoices' => $recentInvoices,
            'recentPayments' => $recentPayments,
            'paymentSummary' => $paymentSummary,
            'overdueInvoices' => $overdueInvoices,
        ]);
    }

    private function userDashboard()
    {
        $stats = $this->getUserStats();
        $recentInvoices = $this->getAccessibleRecentInvoices();
        $accessibleReports = $this->getAccessibleReports();

        return Inertia::render('dashboard/UserDashboard', [
            'stats' => $stats,
            'recentInvoices' => $recentInvoices,
            'accessibleReports' => $accessibleReports,
        ]);
    }

    private function getAdminStats(): array
    {
        $totalInvoices = Invoice::count();
        $totalRevenue = Invoice::where('status', 'paid')->sum('total');
        $pendingAmount = Invoice::whereIn('status', ['sent', 'overdue'])->sum('total');
        $overdueAmount = Invoice::where('status', 'overdue')->sum('total');
        $draftCount = Invoice::where('status', 'draft')->count();
        $paidCount = Invoice::where('status', 'paid')->count();
        $overdueCount = Invoice::where('status', 'overdue')->count();
        $clientsCount = Client::where('is_active', true)->count();

        $revenueGrowth = $this->calculateRevenueGrowth();
        $invoiceGrowth = $this->calculateInvoiceGrowth();

        $currentOrganization = current_organization();
        $membersCount = $currentOrganization ? $currentOrganization->memberships()->count() : 0;
        $activeMembers = $currentOrganization ? $currentOrganization->memberships()->where('is_active', true)->count() : 0;

        return [
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
            'members_count' => $membersCount,
            'active_members' => $activeMembers,
            'storage_used' => 0, // Placeholder for storage metrics
            'api_calls_today' => 0, // Placeholder for API metrics
        ];
    }

    private function getManagerStats(): array
    {
        $totalInvoices = Invoice::count();
        $totalRevenue = Invoice::where('status', 'paid')->sum('total');
        $pendingAmount = Invoice::whereIn('status', ['sent', 'overdue'])->sum('total');
        $overdueAmount = Invoice::where('status', 'overdue')->sum('total');
        $draftCount = Invoice::where('status', 'draft')->count();
        $paidCount = Invoice::where('status', 'paid')->count();
        $overdueCount = Invoice::where('status', 'overdue')->count();
        $clientsCount = Client::where('is_active', true)->count();

        $revenueGrowth = $this->calculateRevenueGrowth();
        $invoiceGrowth = $this->calculateInvoiceGrowth();

        // Manager-specific: monthly targets
        $monthlyTarget = 50000; // This could come from settings/database
        $monthlyAchieved = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ])
            ->sum('total');

        return [
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
            'monthly_target' => $monthlyTarget,
            'monthly_achieved' => $monthlyAchieved,
        ];
    }

    private function getAccountantStats(): array
    {
        $totalRevenue = Invoice::where('status', 'paid')->sum('total');
        $pendingAmount = Invoice::whereIn('status', ['sent', 'overdue'])->sum('total');
        $overdueAmount = Invoice::where('status', 'overdue')->sum('total');
        $paidAmount = Invoice::where('status', 'paid')->sum('total');
        $draftCount = Invoice::where('status', 'draft')->count();
        $paidCount = Invoice::where('status', 'paid')->count();
        $overdueCount = Invoice::where('status', 'overdue')->count();

        $revenueGrowth = $this->calculateRevenueGrowth();

        // Accountant-specific metrics
        $pendingPayments = Invoice::whereIn('status', ['sent', 'overdue'])->count();
        $processedPayments = Payment::whereMonth('created_at', Carbon::now()->month)->count();
        $paymentVariance = 0; // Placeholder for payment variance calculation

        return [
            'total_revenue' => $totalRevenue,
            'pending_amount' => $pendingAmount,
            'overdue_amount' => $overdueAmount,
            'paid_amount' => $paidAmount,
            'draft_count' => $draftCount,
            'paid_count' => $paidCount,
            'overdue_count' => $overdueCount,
            'revenue_growth' => round($revenueGrowth, 1),
            'pending_payments' => $pendingPayments,
            'processed_payments' => $processedPayments,
            'payment_variance' => $paymentVariance,
        ];
    }

    private function getUserStats(): array
    {
        // Users only see basic stats for invoices they have access to
        $accessibleInvoices = Invoice::count(); // This should be filtered by user access
        $totalRevenue = Invoice::where('status', 'paid')->sum('total');
        $paidCount = Invoice::where('status', 'paid')->count();
        $pendingCount = Invoice::whereIn('status', ['sent', 'overdue'])->count();

        return [
            'total_invoices' => $accessibleInvoices,
            'total_revenue' => $totalRevenue,
            'paid_count' => $paidCount,
            'pending_count' => $pendingCount,
        ];
    }

    private function calculateRevenueGrowth(): float
    {
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

        return $lastMonthRevenue > 0
            ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
            : 0;
    }

    private function calculateInvoiceGrowth(): float
    {
        $lastMonthInvoices = Invoice::whereBetween('created_at', [
            Carbon::now()->subMonth(2)->startOfMonth(),
            Carbon::now()->subMonth()->endOfMonth()
        ])->count();

        $thisMonthInvoices = Invoice::whereBetween('created_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])->count();

        return $lastMonthInvoices > 0
            ? (($thisMonthInvoices - $lastMonthInvoices) / $lastMonthInvoices) * 100
            : 0;
    }

    private function getRecentInvoices()
    {
        return Invoice::with('client')
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
                    'due_date' => $invoice->due_date?->toDateString(),
                    'created_at' => $invoice->created_at->toDateString(),
                ];
            });
    }

    private function getAccessibleRecentInvoices()
    {
        // For users, this should be filtered based on their access permissions
        return $this->getRecentInvoices();
    }

    private function getUpcomingDue()
    {
        return Invoice::whereIn('status', ['sent', 'overdue'])
            ->where('due_date', '<=', Carbon::now()->addDays(7))
            ->with('client')
            ->orderBy('due_date')
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
                    'due_date' => $invoice->due_date?->toDateString(),
                ];
            });
    }

    private function getOverdueInvoices()
    {
        return Invoice::where('status', 'overdue')
            ->with('client')
            ->orderBy('due_date')
            ->take(5)
            ->get()
            ->map(function ($invoice) {
                $daysOverdue = $invoice->due_date ? Carbon::now()->diffInDays($invoice->due_date) : 0;
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client' => [
                        'name' => $invoice->client->name,
                    ],
                    'total' => $invoice->total,
                    'days_overdue' => $daysOverdue,
                ];
            });
    }

    private function getRecentPayments()
    {
        return Payment::with('invoice.client')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_number' => $payment->invoice->invoice_number,
                    'client_name' => $payment->invoice->client->name,
                    'amount' => $payment->amount,
                    'payment_date' => $payment->created_at->toDateString(),
                ];
            });
    }

    private function getRecentActivity()
    {
        // Placeholder for system activity logs
        return [];
    }

    private function getSystemAlerts()
    {
        // Placeholder for system alerts
        return [];
    }

    private function getOrganizationMetrics()
    {
        // Placeholder for organization-specific metrics
        return [];
    }

    private function getTeamActivity()
    {
        // Placeholder for team activity data
        return [];
    }

    private function getClientMetrics()
    {
        // Placeholder for client metrics data
        return [];
    }

    private function getPaymentSummary()
    {
        // Placeholder for payment summary data
        return [];
    }

    private function getAccessibleReports()
    {
        // Placeholder for user-accessible reports
        return [];
    }
}
