export interface InvoiceTemplate {
  id: number
  uuid: string
  organization_id: number
  name: string
  slug: string
  description?: string
  design?: string | null
  dynamic_fields?: string[] | null
  view_path?: string
  preview_image?: string
  is_free?: boolean
  price?: number
  is_active?: boolean
  settings?: Record<string, any> | null
  created_at: string
  updated_at: string
}

export interface Invoice {
  id: number
  uuid: string
  organization_id: number
  invoice_number: string
  client_id: number
  currency: string
  exchange_rate: number
  issue_date: string
  due_date: string
  status: 'draft' | 'sent' | 'paid' | 'overdue'
  subtotal: number
  tax_rate: number
  tax_amount: number
  discount: number
  total: number
  amount_paid: number
  notes?: string
  terms?: string
  sent_at?: string | null
  paid_at?: string | null
  created_at: string
  updated_at: string
}

export interface Client {
  id: number
  uuid: string
  organization_id: number
  name: string
  email: string
  phone?: string
  address?: string
  city?: string
  state?: string
  country?: string
  postal_code?: string
  website?: string
  notes?: string
  created_at: string
  updated_at: string
}

export interface Organization {
  id: number
  uuid: string
  name: string
  slug: string
  email?: string
  phone?: string
  address?: string
  city?: string
  state?: string
  country?: string
  postal_code?: string
  tax_id?: string
  website?: string
  logo?: string
  invoice_template_id?: number
  billing_email?: string
  is_active: boolean
  settings?: Record<string, any> | null
  created_at: string
  updated_at: string
}
