<template>
    <Head :title="`${organization.name} Settings`" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <Link
                    :href="route('dashboard')"
                    class="mb-4 inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Dashboard
                </Link>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    Organization Settings
                </h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Manage your organization details, members, and settings.
                </p>
            </div>

            <!-- Tabs -->
            <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex gap-6">
                    <button
                        @click="currentTab = 'general'"
                        :class="[
                            currentTab === 'general'
                                ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                            'border-b-2 px-1 py-4 text-sm font-medium'
                        ]"
                    >
                        General
                    </button>
                    <button
                        v-if="canManageMembers"
                        @click="currentTab = 'members'"
                        :class="[
                            currentTab === 'members'
                                ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                            'border-b-2 px-1 py-4 text-sm font-medium'
                        ]"
                    >
                        Members
                    </button>
                    <button
                        v-if="canManageBilling"
                        @click="currentTab = 'billing'"
                        :class="[
                            currentTab === 'billing'
                                ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                            'border-b-2 px-1 py-4 text-sm font-medium'
                        ]"
                    >
                        Billing
                    </button>
                    <button
                        v-if="isOwner"
                        @click="currentTab = 'danger'"
                        :class="[
                            currentTab === 'danger'
                                ? 'border-red-500 text-red-600 dark:border-red-400 dark:text-red-400'
                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                            'border-b-2 px-1 py-4 text-sm font-medium'
                        ]"
                    >
                        Danger Zone
                    </button>
                </nav>
            </div>

            <!-- General Tab -->
            <div v-if="currentTab === 'general'" class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h2 class="mb-6 text-xl font-semibold text-gray-900 dark:text-gray-100">
                    General Information
                </h2>

                <form @submit.prevent="updateOrganization">
                    <div class="grid gap-6">
                        <!-- Organization Name -->
                        <div>
                            <label for="name" class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                Organization Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="generalForm.name"
                                type="text"
                                required
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                Email
                            </label>
                            <input
                                id="email"
                                v-model="generalForm.email"
                                type="email"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                Phone
                            </label>
                            <input
                                id="phone"
                                v-model="generalForm.phone"
                                type="text"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                Address
                            </label>
                            <textarea
                                id="address"
                                v-model="generalForm.address"
                                rows="2"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            ></textarea>
                        </div>

                        <!-- City, State, Postal -->
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <label for="city" class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                    City
                                </label>
                                <input
                                    id="city"
                                    v-model="generalForm.city"
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                            <div>
                                <label for="state" class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                    State/Region
                                </label>
                                <input
                                    id="state"
                                    v-model="generalForm.state"
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                            <div>
                                <label for="postal_code" class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                    Postal Code
                                </label>
                                <input
                                    id="postal_code"
                                    v-model="generalForm.postal_code"
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                        </div>

                        <!-- Country -->
                        <div>
                            <label for="country" class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                Country
                            </label>
                            <input
                                id="country"
                                v-model="generalForm.country"
                                type="text"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            type="submit"
                            :disabled="generalForm.processing"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ generalForm.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Members Tab -->
            <div v-if="currentTab === 'members' && canManageMembers" class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            Team Members
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Manage who has access to this organization.
                        </p>
                    </div>
                    <Link
                        :href="route('organizations.members.invite', organization.uuid)"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    >
                        Invite Member
                    </Link>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="member in organization.members"
                        :key="member.id"
                        class="flex items-center justify-between py-4"
                    >
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300">
                                {{ member.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ member.name }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ member.email }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                {{ member.pivot.role_label || member.pivot.role }}
                            </span>
                            <button
                                v-if="member.id !== $page.props.auth.user.id && isOwner"
                                @click="removeMember(member.id)"
                                class="text-red-600 hover:text-red-700 dark:text-red-400"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing Tab (Placeholder) -->
            <div v-if="currentTab === 'billing' && canManageBilling" class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-gray-100">
                    Billing & Subscription
                </h2>
                <p class="text-gray-600 dark:text-gray-400">
                    Billing integration will be available in Phase 4 with PayChangu.
                </p>
            </div>

            <!-- Danger Zone -->
            <div v-if="currentTab === 'danger' && isOwner" class="rounded-lg border-2 border-red-200 bg-white p-6 dark:border-red-800 dark:bg-gray-800">
                <h2 class="mb-4 text-xl font-semibold text-red-600 dark:text-red-400">
                    Danger Zone
                </h2>
                <div class="rounded-lg border border-red-300 bg-red-50 p-4 dark:border-red-700 dark:bg-red-900/20">
                    <h3 class="mb-2 font-semibold text-red-900 dark:text-red-100">
                        Delete Organization
                    </h3>
                    <p class="mb-4 text-sm text-red-700 dark:text-red-300">
                        Once you delete an organization, there is no going back. All data including invoices, clients, and payments will be permanently deleted.
                    </p>
                    <button
                        @click="confirmDelete = true"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                    >
                        Delete Organization
                    </button>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div v-if="confirmDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Delete Organization
                    </h3>
                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        Are you absolutely sure? This action cannot be undone. Type <strong>{{ organization.name }}</strong> to confirm.
                    </p>
                    <input
                        v-model="deleteConfirmText"
                        type="text"
                        class="mb-4 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Type organization name"
                    />
                    <div class="flex justify-end gap-3">
                        <button
                            @click="confirmDelete = false"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </button>
                        <button
                            @click="deleteOrganization"
                            :disabled="deleteConfirmText !== organization.name"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                        >
                            Delete Permanently
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePermissions } from '@/composables/usePermissions';

const props = defineProps({
    organization: Object,
});

const { isOwner, canManageMembers, canManageBilling } = usePermissions();

const currentTab = ref('general');
const confirmDelete = ref(false);
const deleteConfirmText = ref('');

const generalForm = useForm({
    name: props.organization.name,
    email: props.organization.email,
    phone: props.organization.phone,
    address: props.organization.address,
    city: props.organization.city,
    state: props.organization.state,
    postal_code: props.organization.postal_code,
    country: props.organization.country,
});

const updateOrganization = () => {
    generalForm.put(route('organizations.update', props.organization.uuid), {
        preserveScroll: true,
    });
};

const removeMember = (memberId) => {
    if (confirm('Are you sure you want to remove this member?')) {
        router.delete(route('organizations.members.remove', [props.organization.uuid, memberId]), {
            preserveScroll: true,
        });
    }
};

const deleteOrganization = () => {
    router.delete(route('organizations.destroy', props.organization.uuid));
};
</script>
