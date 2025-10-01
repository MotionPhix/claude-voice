import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const page = usePage();
    
    const auth = computed(() => page.props.auth);
    const currentOrganization = computed(() => auth.value?.currentOrganization);
    const currentMembership = computed(() => auth.value?.currentMembership);
    const userOrganizations = computed(() => auth.value?.userOrganizations || []);
    
    const can = (permission) => {
        if (!currentMembership.value?.role) return false;
        return currentMembership.value.role.permissions?.includes(permission) || false;
    };
    
    const hasRole = (role) => {
        if (!currentMembership.value?.role) return false;
        const currentRole = currentMembership.value.role.value.toLowerCase();
        return currentRole === role.toLowerCase();
    };
    
    const hasAnyRole = (roles) => {
        return roles.some(role => hasRole(role));
    };
    
    const isOwner = computed(() => hasRole('owner'));
    const isAdmin = computed(() => hasRole('admin'));
    const isManager = computed(() => hasRole('manager'));
    const isAccountant = computed(() => hasRole('accountant'));
    const isUser = computed(() => hasRole('user'));
    
    const canManageOrganization = computed(() => hasAnyRole(['owner', 'admin']));
    const canManageMembers = computed(() => hasAnyRole(['owner', 'admin']));
    const canManageBilling = computed(() => isOwner.value);
    const canCreateInvoices = computed(() => hasAnyRole(['owner', 'admin', 'manager']));
    const canCreateClients = computed(() => hasAnyRole(['owner', 'admin', 'manager']));
    const canManagePayments = computed(() => hasAnyRole(['owner', 'admin', 'manager', 'accountant']));
    
    return {
        auth,
        currentOrganization,
        currentMembership,
        userOrganizations,
        can,
        hasRole,
        hasAnyRole,
        isOwner,
        isAdmin,
        isManager,
        isAccountant,
        isUser,
        canManageOrganization,
        canManageMembers,
        canManageBilling,
        canCreateInvoices,
        canCreateClients,
        canManagePayments,
    };
}
