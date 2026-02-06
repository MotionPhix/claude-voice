<template>
    <Head title="Select Organization" />

    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 dark:bg-gray-900">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-gray-100">
                Select Organization
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                Choose which organization you'd like to work with
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 dark:bg-gray-800">
                <div class="space-y-4">
                    <div
                        v-for="organization in organizations"
                        :key="organization.id"
                        class="relative block w-full rounded-lg border-2 border-dashed border-gray-300 p-4 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer transition-colors dark:border-gray-600 dark:hover:border-gray-500"
                        @click="selectOrganization(organization)"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center dark:bg-indigo-900">
                                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-4 0H9m-4 0H5m14 0h-2M9 3h6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 text-left">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    {{ organization.name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatRole(organization.pivot.role) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <Link
                        :href="route('organizations.create')"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-indigo-600 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-indigo-400 dark:hover:bg-gray-600 dark:border-gray-600"
                    >
                        Create New Organization
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    organizations: Array,
});

const selectOrganization = (organization) => {
    router.post(route('organizations.switch', organization.uuid), {}, {
        onSuccess: () => {
            router.visit(route('dashboard'));
        }
    });
};

const formatRole = (role) => {
    const roles = {
        owner: 'Owner',
        admin: 'Admin',
        manager: 'Manager',
        accountant: 'Accountant',
        user: 'User'
    };
    return roles[role] || role;
};
</script>