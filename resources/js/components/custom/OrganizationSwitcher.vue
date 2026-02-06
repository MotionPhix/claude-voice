<template>
    <div class="relative">
        <button
            @click="isOpen = !isOpen"
            type="button"
            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-800"
        >
            <div class="flex flex-col items-start">
                <span class="text-gray-900 dark:text-gray-100">
                    {{ currentOrganization?.name || 'Select Organization' }}
                </span>
                <span v-if="currentMembership" class="text-xs text-gray-500 dark:text-gray-400">
                    {{ currentMembership.role.label }}
                </span>
            </div>
            <svg
                class="h-4 w-4 text-gray-500 transition-transform"
                :class="{ 'rotate-180': isOpen }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 z-50 mt-2 w-64 rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-gray-800"
                @click.stop
            >
                <!-- Organizations List -->
                <div class="p-2">
                    <div class="mb-2 px-3 py-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                        Your Organizations
                    </div>
                    
                    <button
                        v-for="org in userOrganizations"
                        :key="org.id"
                        @click="switchOrganization(org.id)"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
                        :class="{
                            'bg-blue-50 dark:bg-blue-900/20': org.id === currentOrganization?.id
                        }"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300">
                            {{ org.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex flex-1 flex-col items-start">
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                {{ org.name }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ getMembershipRole(org.id) }}
                            </span>
                        </div>
                        <svg
                            v-if="org.id === currentOrganization?.id"
                            class="h-5 w-5 text-blue-600 dark:text-blue-400"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                </div>

                <div class="border-t border-gray-200 p-2 dark:border-gray-700">
                    <Link
                        :href="route('organizations.create')"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20"
                        @click="isOpen = false"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Organization
                    </Link>
                    
                    <Link
                        v-if="canManageOrganization"
                        :href="route('organizations.settings', currentOrganization?.uuid)"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                        @click="isOpen = false"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        Organization Settings
                    </Link>
                </div>
            </div>
        </Transition>

        <!-- Click outside to close -->
        <div
            v-if="isOpen"
            @click="isOpen = false"
            class="fixed inset-0 z-40"
        ></div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';

const { currentOrganization, currentMembership, userOrganizations, canManageOrganization } = usePermissions();

const isOpen = ref(false);

const getMembershipRole = (orgId) => {
    const org = userOrganizations.value.find(o => o.id === orgId);
    return org?.pivot?.role_label || 'Member';
};

const switchOrganization = (organizationId) => {
    router.post(route('organizations.switch', organizationId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            isOpen.value = false;
        },
    });
};

const handleEscape = (e) => {
    if (e.key === 'Escape') {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleEscape);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape);
});
</script>
