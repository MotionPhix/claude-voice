import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

type MembershipRole = 'owner' | 'admin' | 'manager' | 'accountant' | 'user'

interface User {
  id: number
  name: string
  email: string
}

interface Membership {
  role: MembershipRole
  role_label: string
}

interface Organization {
  id: number
  name: string
  slug: string
}

interface AuthData {
  user: User | null
  currentOrganization: Organization | null
  currentMembership: Membership | null
  userOrganizations: Organization[]
}

export function usePermissions() {
  const page = usePage()

  const auth = computed(() => page.props.auth as AuthData)
  const user = computed(() => auth.value?.user)
  const currentOrganization = computed(() => auth.value?.currentOrganization)
  const currentMembership = computed(() => auth.value?.currentMembership)
  const userRole = computed(() => currentMembership.value?.role)
  const userRoleLabel = computed(() => currentMembership.value?.role_label || 'User')

  // Permission hierarchy: owner > admin > manager > accountant > user
  const roleHierarchy: Record<MembershipRole, number> = {
    owner: 5,
    admin: 4,
    manager: 3,
    accountant: 2,
    user: 1
  }

  const hasRole = (role: MembershipRole): boolean => {
    if (!userRole.value || !currentMembership.value) return false
    return userRole.value === role
  }

  const hasRoleOrHigher = (minimumRole: MembershipRole): boolean => {
    if (!userRole.value || !currentMembership.value) return false
    return roleHierarchy[userRole.value] >= roleHierarchy[minimumRole]
  }

  // Invoice permissions
  const canCreateInvoices = computed(() => {
    return hasRoleOrHigher('accountant')
  })

  const canEditInvoices = computed(() => {
    return hasRoleOrHigher('accountant')
  })

  const canDeleteInvoices = computed(() => {
    return hasRoleOrHigher('manager')
  })

  const canSendInvoices = computed(() => {
    return hasRoleOrHigher('accountant')
  })

  const canMarkInvoicesPaid = computed(() => {
    return hasRoleOrHigher('accountant')
  })

  const canViewInvoices = computed(() => {
    return hasRoleOrHigher('user')
  })

  // Client permissions
  const canCreateClients = computed(() => {
    return hasRoleOrHigher('accountant')
  })

  const canEditClients = computed(() => {
    return hasRoleOrHigher('accountant')
  })

  const canDeleteClients = computed(() => {
    return hasRoleOrHigher('manager')
  })

  const canViewClients = computed(() => {
    return hasRoleOrHigher('user')
  })

  // Organization permissions
  const canManageOrganization = computed(() => {
    return hasRoleOrHigher('admin')
  })

  const canInviteMembers = computed(() => {
    return hasRoleOrHigher('admin')
  })

  const canManageMembers = computed(() => {
    return hasRoleOrHigher('admin')
  })

  const canDeleteOrganization = computed(() => {
    return hasRole('owner')
  })

  // Settings permissions
  const canManageSettings = computed(() => {
    return hasRoleOrHigher('admin')
  })

  const canManageCurrencies = computed(() => {
    return hasRoleOrHigher('admin')
  })

  // Reports permissions
  const canViewReports = computed(() => {
    return hasRoleOrHigher('accountant')
  })

  const canExportReports = computed(() => {
    return hasRoleOrHigher('manager')
  })

  // Payment permissions
  const canViewPayments = computed(() => {
    return hasRoleOrHigher('accountant')
  })

  const canManagePayments = computed(() => {
    return hasRoleOrHigher('accountant')
  })

  // Bulk operations permissions
  const canBulkDeleteInvoices = computed(() => {
    return hasRoleOrHigher('manager')
  })

  const canBulkSendInvoices = computed(() => {
    return hasRoleOrHigher('accountant')
  })

  const canBulkExport = computed(() => {
    return hasRoleOrHigher('accountant')
  })

  return {
    // Auth data
    user,
    currentOrganization,
    currentMembership,
    userRole,
    userRoleLabel,

    // Role helpers
    hasRole,
    hasRoleOrHigher,

    // Invoice permissions
    canCreateInvoices,
    canEditInvoices,
    canDeleteInvoices,
    canSendInvoices,
    canMarkInvoicesPaid,
    canViewInvoices,

    // Client permissions
    canCreateClients,
    canEditClients,
    canDeleteClients,
    canViewClients,

    // Organization permissions
    canManageOrganization,
    canInviteMembers,
    canManageMembers,
    canDeleteOrganization,

    // Settings permissions
    canManageSettings,
    canManageCurrencies,

    // Reports permissions
    canViewReports,
    canExportReports,

    // Payment permissions
    canViewPayments,
    canManagePayments,

    // Bulk operations permissions
    canBulkDeleteInvoices,
    canBulkSendInvoices,
    canBulkExport,
  }
}