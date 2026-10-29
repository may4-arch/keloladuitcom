<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-slate-950 px-4 py-8 text-slate-100">
        <!-- Logo & Brand -->
        <div class="mb-6">
            <Link :href="'/'" class="flex items-center gap-2.5">
                <ApplicationLogo class="h-8 w-8" />
                <span class="text-lg font-bold text-white">
                    Kelola<span class="text-emerald-400">Duit</span>
                </span>
            </Link>
        </div>

        <!-- Main Card Container -->
        <div class="w-full max-w-sm rounded-xl border border-slate-800 bg-slate-900 p-6 shadow-sm">
            <slot />
        </div>
    </div>
</template>
