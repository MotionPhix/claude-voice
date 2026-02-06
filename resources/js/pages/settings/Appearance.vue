<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
</script>

<template>
    <Head title="Appearance Settings" />

    <SettingsLayout>
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Appearance</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Customize how your interface looks and feels
            </p>
        </div>

        <AppearanceTabs />
    </SettingsLayout>
</template>
