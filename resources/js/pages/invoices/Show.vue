<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Edit2,
    Send,
    Download,
    Copy,
    Trash2,
    Plus,
    MoreHorizontal,
    Calendar,
    User,
    Mail,
    Phone,
    MapPin,
    CreditCard
} from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface InvoiceItem {
    id: number;
    description: string;
    quantity: number;
    unit_price: number;
    total: number;
}

interface Payment {
    id: number;
    amount: number;
    payment_date: string;
    method: string;
    reference?: string;
    notes?: string;
}

interface Client {
    id: number;
    name: string;
    email: string;
    phone?: string;
    address?: string;
    city?: string;
    state?: string;
    postal_code?: string;
    country?: string;
}

interface Invoice {
    id: number;
    invoice_number: string;
    client: Client;
    issue_date: string;
    due_date: string;
    status: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled';
    subtotal: number;
    tax_rate: number;
    tax_amount: number;
    discount: number;
    total: number;
    amount_paid: number;
    currency: string;
    notes?: string;
    terms?: string;
    items: InvoiceItem[];
    payments: Payment[];
    created_at: string;
    updated_at: string;
}

interface Props {
    invoice: Invoice;
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Invoices', href: '/invoices' },
    { title: props.invoice.invoice_number, href: `/invoices/${props.invoice.id}` }
];

// Payment form state
const showPaymentDialog = ref(false);
const paymentForm = ref({
    amount: props.invoice.total - props.invoice.amount_paid,
    payment_date: new Date().toISOString().split('T')[0],
    method: 'bank_transfer',
    reference: '',
    notes: ''
});

// Status configuration
const statusConfig = {
    draft: { color: 'bg-gray-100 text-gray-800', label: 'Draft' },
    sent: { color: 'bg-blue-100 text-blue-800', label: 'Sent' },
    paid: { color: 'bg-green-100 text-green-800', label: 'Paid' },
    overdue: { color: 'bg-red-100 text-red-800', label: 'Overdue' },
    cancelled: { color: 'bg-gray-100 text-gray-600', label: 'Cancelled' }
};

const paymentMethods = {
    cash: 'Cash',
    check: 'Check',
    bank_transfer: 'Bank Transfer',
    credit_card: 'Credit Card',
    paypal: 'PayPal',
    other: 'Other'
};

// Computed properties
const remainingBalance = computed(()
