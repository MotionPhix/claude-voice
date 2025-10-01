<template>
    <Head title="Create Organization" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    Create Organization
                </h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Set up a new organization to manage invoices and team members.
                </p>
            </div>

            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <form @submit.prevent="submit">
                    <!-- Organization Name -->
                    <div class="mb-6">
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                            Organization Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            :class="{ 'border-red-500': form.errors.name }"
                            required
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div class="mb-6">
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Phone -->
                    <div class="mb-6">
                        <label for="phone" class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                            Phone
                        </label>
                        <input
                            id="phone"
                            v-model="form.phone"
                            type="text"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            :class="{ 'border-red-500': form.errors.phone }"
                        />
                        <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.phone }}
                        </p>
                    </div>

                    <!-- Address -->
                    <div class="mb-6">
                        <label for="address" class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                            Address
                        </label>
                        <textarea
                            id="address"
                            v-model="form.address"
                            rows="2"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            :class="{ 'border-red-500': form.errors.address }"
                        ></textarea>
                        <p v-if="form.errors.address" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.address }}
                        </p>
                    </div>

                    <!-- City, State, Postal Code -->
                    <div class="mb-6 grid gap-4 sm:grid-cols-3">
                        <div>
                            <label for="city" class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                City
                            </label>
                            <input
                                id="city"
                                v-model="form.city"
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
                                v-model="form.state"
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
                                v-model="form.postal_code"
                                type="text"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Country -->
                    <div class="mb-6">
                        <label for="country" class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                            Country
                        </label>
                        <input
                            id="country"
                            v-model="form.country"
                            type="text"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3">
                        <Link
                            :href="route('dashboard')"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            <span v-if="!form.processing">Create Organization</span>
                            <span v-else>Creating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: 'Malawi',
});

const submit = () => {
    form.post(route('organizations.store'));
};
</script>
