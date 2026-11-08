<script setup>
import { reactive } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    categoryBudgets: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            start_date: '',
            end_date: '',
        }),
    },
    summary: {
        type: Object,
        default: () => ({
            total_budget: 0,
            total_spent: 0,
            over_budget_count: 0,
        }),
    },
});

const filterForm = reactive({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

// Format currency helper
const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

// Format date helper (e.g. "04 Nov 2026")
const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

// Helper for date column text
const getDisplayDate = (item) => {
    if (filterForm.start_date && filterForm.end_date) {
        if (filterForm.start_date === filterForm.end_date) {
            return formatDate(filterForm.start_date);
        }
        return `${formatDate(filterForm.start_date)} - ${formatDate(filterForm.end_date)}`;
    } else if (filterForm.start_date) {
        return `>= ${formatDate(filterForm.start_date)}`;
    } else if (filterForm.end_date) {
        return `<= ${formatDate(filterForm.end_date)}`;
    }

    // Default: use actual transaction dates in this category
    if (item.first_date && item.last_date) {
        if (item.first_date === item.last_date) {
            return formatDate(item.first_date);
        }
        return `${formatDate(item.first_date)} - ${formatDate(item.last_date)}`;
    } else if (item.first_date) {
        return formatDate(item.first_date);
    }

    return '-';
};

// Filter actions
const applyFilter = () => {
    router.get(
        route('dashboard'),
        {
            start_date: filterForm.start_date || undefined,
            end_date: filterForm.end_date || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const resetFilter = () => {
    filterForm.start_date = '';
    filterForm.end_date = '';
    applyFilter();
};

// Date Presets
const setPreset = (preset) => {
    const today = new Date();
    const y = today.getFullYear();
    const m = String(today.getMonth() + 1).padStart(2, '0');
    const d = String(today.getDate()).padStart(2, '0');

    if (preset === 'today') {
        const todayStr = `${y}-${m}-${d}`;
        filterForm.start_date = todayStr;
        filterForm.end_date = todayStr;
    } else if (preset === 'this_month') {
        const firstDay = `${y}-${m}-01`;
        const lastDayObj = new Date(y, today.getMonth() + 1, 0);
        const lastDay = `${y}-${m}-${String(lastDayObj.getDate()).padStart(2, '0')}`;
        filterForm.start_date = firstDay;
        filterForm.end_date = lastDay;
    } else if (preset === 'all') {
        filterForm.start_date = '';
        filterForm.end_date = '';
    }
    applyFilter();
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-base font-semibold text-white">
                        Dashboard
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Laporan realisasi dan perbandingan Category Budget dengan transaksi pengeluaran.
                    </p>
                </div>
            </div>
        </template>

        <div class="p-4 sm:p-6 space-y-6">
            <!-- Top KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Total Budget -->
                <div class="rounded-xl border border-slate-800 bg-slate-900/90 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-400">Total Alokasi Budget</span>
                        <div class="h-8 w-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-bold text-white">
                            {{ formatRupiah(summary.total_budget) }}
                        </p>
                        <span class="text-[11px] text-slate-400">Batas maksimal anggaran</span>
                    </div>
                </div>

                <!-- Total Spent -->
                <div class="rounded-xl border border-slate-800 bg-slate-900/90 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-400">Total Pengeluaran Aktual</span>
                        <div class="h-8 w-8 rounded-lg bg-rose-500/10 flex items-center justify-center text-rose-400 border border-rose-500/20">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-2xl font-bold text-rose-400">
                            {{ formatRupiah(summary.total_spent) }}
                        </p>
                        <span class="text-[11px] text-slate-400">Total belanja pada kategori budget</span>
                    </div>
                </div>

                <!-- Status Anggaran -->
                <div class="rounded-xl border border-slate-800 bg-slate-900/90 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-400">Status Anggaran</span>
                        <div
                            :class="[
                                summary.over_budget_count > 0
                                    ? 'bg-rose-500/10 text-rose-400 border-rose-500/20'
                                    : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                'h-8 w-8 rounded-lg flex items-center justify-center border'
                            ]"
                        >
                            <svg v-if="summary.over_budget_count > 0" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p
                            :class="summary.over_budget_count > 0 ? 'text-rose-400' : 'text-emerald-400'"
                            class="text-2xl font-bold"
                        >
                            {{ summary.over_budget_count > 0 ? `${summary.over_budget_count} Over Budget` : 'Semua Normal' }}
                        </p>
                        <span class="text-[11px] text-slate-400">
                            {{ summary.over_budget_count > 0 ? 'Ada pengeluaran melebihi budget' : 'Pengeluaran aman terkendali' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Report Card: Category Budget -->
            <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden shadow-sm">
                <!-- Report Header & Date Filters -->
                <div class="p-5 border-b border-slate-800 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                                <span>Report: Category Budget</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                    {{ categoryBudgets.length }} Kategori
                                </span>
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Perbandingan antara batas maksimal budget dengan pengeluaran aktual transaksi per kategori.
                            </p>
                        </div>

                        <!-- Date Preset Badges -->
                        <div class="flex items-center gap-1.5 self-start sm:self-auto">
                            <button
                                type="button"
                                @click="setPreset('today')"
                                class="rounded-md border border-slate-700 bg-slate-950 px-2.5 py-1 text-[11px] text-slate-300 hover:border-slate-500 hover:text-white transition-colors"
                            >
                                Hari Ini
                            </button>
                            <button
                                type="button"
                                @click="setPreset('this_month')"
                                class="rounded-md border border-slate-700 bg-slate-950 px-2.5 py-1 text-[11px] text-slate-300 hover:border-slate-500 hover:text-white transition-colors"
                            >
                                Bulan Ini
                            </button>
                            <button
                                type="button"
                                @click="setPreset('all')"
                                class="rounded-md border border-slate-700 bg-slate-950 px-2.5 py-1 text-[11px] text-slate-300 hover:border-slate-500 hover:text-white transition-colors"
                            >
                                Semua
                            </button>
                        </div>
                    </div>

                    <!-- Date Filter Inputs Form -->
                    <form @submit.prevent="applyFilter" class="pt-2 flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2">
                            <label class="text-xs text-slate-400">Dari:</label>
                            <input
                                type="date"
                                v-model="filterForm.start_date"
                                class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-1.5 text-xs text-slate-200 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            />
                        </div>

                        <div class="flex items-center gap-2">
                            <label class="text-xs text-slate-400">Sampai:</label>
                            <input
                                type="date"
                                v-model="filterForm.end_date"
                                class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-1.5 text-xs text-slate-200 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            />
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-1.5 text-xs font-medium text-white hover:bg-emerald-500 transition-colors shadow-sm"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                <span>Filter Tanggal</span>
                            </button>

                            <button
                                v-if="filterForm.start_date || filterForm.end_date"
                                type="button"
                                @click="resetFilter"
                                class="rounded-lg border border-slate-700 px-3 py-1.5 text-xs font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800 transition-colors"
                            >
                                Reset
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Report Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-800 bg-slate-950/60 text-slate-400 uppercase text-[11px] font-semibold tracking-wider">
                            <tr>
                                <th scope="col" class="py-3.5 px-5 font-semibold">Category</th>
                                <th scope="col" class="py-3.5 px-5 font-semibold">Date</th>
                                <th scope="col" class="py-3.5 px-5 font-semibold text-right">Budget Expense</th>
                                <th scope="col" class="py-3.5 px-5 font-semibold text-right">Total Expense</th>
                                <th scope="col" class="py-3.5 px-5 font-semibold text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/70 text-slate-300">
                            <tr
                                v-for="item in categoryBudgets"
                                :key="item.budget_id"
                                class="hover:bg-slate-800/30 transition-colors"
                            >
                                <!-- Category -->
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-white">
                                            {{ item.category_name }}
                                        </span>
                                        <span
                                            v-if="item.budget_name && item.budget_name !== item.category_name"
                                            class="text-[11px] text-slate-400 truncate max-w-xs"
                                        >
                                            ({{ item.budget_name }})
                                        </span>
                                    </div>
                                </td>

                                <!-- Date -->
                                <td class="py-3.5 px-5 text-slate-400 whitespace-nowrap">
                                    {{ getDisplayDate(item) }}
                                </td>

                                <!-- Budget Expense -->
                                <td class="py-3.5 px-5 text-right font-mono font-medium text-slate-300 whitespace-nowrap">
                                    {{ formatRupiah(item.budget_expense) }}
                                </td>

                                <!-- Total Expense -->
                                <td
                                    class="py-3.5 px-5 text-right font-mono font-bold whitespace-nowrap"
                                    :class="item.status === 'Over Budget' ? 'text-rose-400' : 'text-slate-100'"
                                >
                                    {{ formatRupiah(item.total_expense) }}
                                </td>

                                <!-- Status (Over Budget / Normal) -->
                                <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                    <span
                                        v-if="item.status === 'Over Budget'"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-rose-500/15 border border-rose-500/30 px-2.5 py-1 text-[11px] font-semibold text-rose-400"
                                    >
                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span>Over Budget</span>
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 px-2.5 py-1 text-[11px] font-semibold text-emerald-400"
                                    >
                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Normal</span>
                                    </span>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="categoryBudgets.length === 0">
                                <td colspan="5" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="h-12 w-12 rounded-full bg-slate-800/80 flex items-center justify-center text-slate-500 mb-3">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-slate-300">Belum ada Category Budget</p>
                                        <p class="text-xs text-slate-500 mt-1">Buat anggaran baru untuk membandingkan alokasi dengan pengeluaran aktual.</p>
                                        <Link
                                            :href="route('budgets.create')"
                                            class="mt-4 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-medium transition-colors"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Tambah Budget Baru
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
