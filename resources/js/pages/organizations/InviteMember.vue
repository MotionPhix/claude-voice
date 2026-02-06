<template>
    <Head title="Invite Team Member" />

    <AppLayout>
        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6">
                <Link
                    :href="route('organizations.settings', organization.uuid)"
                    class="mb-4 inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Settings
                </Link>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    Invite Team Member
                </h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Send an invitation to join <strong>{{ organization.name }}</strong>
                </p>
            </div>

            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <form @submit.prevent="submit">
                    <!-- Email -->
                    <div class="mb-6">
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="colleague@example.com"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Role Selection -->
                    <div class="mb-6">
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-3">
                            <label
                                v-for="role in roles"
                                :key="role.value"
                                class="flex cursor-pointer items-start rounded-lg border p-4 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                                :class="{
                                    'border-blue-500 bg-blue-50 dark:border-blue-400 dark:bg-blue-900/20': form.role === role.value,
                                    'border-gray-300 dark:border-gray-600': form.role !== role.value,
                                }"
                            >
                                <input
                                    v-model="form.role"
                                    type="radio"
                                    :value="role.value"
                                    class="mt-1 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                                />
                                <div class="ml-3 flex-1">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ role.label }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ role.description }}
                                    </div>
                                </div>
                            </label>
                        </div>
                        <p v-if="form.errors.role" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.role }}
                        </p>
                    </div>

                    <!-- Personal Message -->
                    <div class="mb-6">
                        <label for="message" class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                            Personal Message (Optional)
                        </label>
                        <textarea
                            id="message"
                            v-model="form.message"
                            rows="3"
                            placeholder="Add a personal message to your invitation..."
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            This message will be included in the invitation email.
                        </p>
                    </div>

                    <!-- Permission Summary -->
                    <div v-if="form.role" class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                        <h3 class="mb-2 font-medium text-blue-900 dark:text-blue-100">
                            What can {{ roles.find(r => r.value === form.role)?.label }}s do?
                        </h3>
                        <ul class="space-y-1 text-sm text-blue-800 dark:text-blue-200">
                            <li v-if="form.role === 'admin'" class="flex items-center">
                                <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Manage team members and organization settings
                            </li>
                            <li v-if="['admin', 'manager'].includes(form.role)" class="flex items-center">
                                <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Create and send invoices
                            </li>
                            <li v-if="['admin', 'manager'].includes(form.role)" class="flex items-center">
                                <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Manage clients
                            </li>
                            <li v-if="['admin', 'manager', 'accountant'].includes(form.role)" class="flex items-center">
                                <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Record and manage payments
                            </li>
                            <li class="flex items-center">
                                <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                View invoices and reports
                            </li>
                        </ul>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <Link
                            :href="route('organizations.settings', organization.uuid)"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || !canInviteMembers"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            <span v-if="!form.processing">Send Invitation</span>
                            <span v-else>Sending...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { usePermissions } from '@/composables/usePermissions';

const props = defineProps({
    organization: Object,
    roles: Array,
});

// Permission check
const { canInviteMembers } = usePermissions();

const form = useForm({
    email: '',
    role: 'manager',
    message: '',
});

const submit = () => {
    form.post(route('organizations.members.send-invite', props.organization.uuid));
};
</script>
