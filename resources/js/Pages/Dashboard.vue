<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-base font-medium text-white">
                Dashboard
            </h1>
        </template>

        <div class="p-6">
            <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
                <p class="text-sm text-slate-300">
                    Selamat datang, {{ $page.props.auth.user.name }}.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
