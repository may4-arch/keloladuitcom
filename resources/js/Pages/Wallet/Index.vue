<script setup>
import { reactive } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    currentBalance: {
        type: Number,
        default: 0,
    },
    moneyBefore: {
        type: Number,
        default: 0,
    },
    totalIncome: {
        type: Number,
        default: 0,
    },
    totalExpense: {
        type: Number,
        default: 0,
    },
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
const applyFilter = (range) => {
    const s = range?.startDate !== undefined ? range.startDate : filterForm.start_date;
    const e = range?.endDate !== undefined ? range.endDate : filterForm.end_date;

    router.get(
        route('wallets.index'),
        {
            start_date: s || undefined,
            end_date: e || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const onRangeChange = (range) => {
    filterForm.start_date = range.startDate;
    filterForm.end_date = range.endDate;
    applyFilter(range);
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
    <Head title="Wallet" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-base font-semibold text-white">Dompet & Saldo</h1>
                    <p class="text-xs text-slate-400">Ringkasan saldo dompet dan laporan perbandingan Category Budget</p>
                </div>
            </div>
        </template>

        <div class="p-6 pb-52 space-y-8">
            <!-- Digital Wallet Card & Quick Stats -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Wallet Digital Card -->
                <div class="lg:col-span-2 relative overflow-hidden rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 p-6 md:p-8 shadow-2xl">
                    <!-- Ambient Glow Effect -->
                    <div class="absolute -top-12 -right-12 h-44 w-44 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-10 -left-10 h-36 w-36 rounded-full bg-teal-500/10 blur-2xl pointer-events-none"></div>

                    <div class="relative z-10 flex flex-col justify-between h-full min-h-[220px]">
                        <!-- Card Header -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <!-- EMV Chip Icon -->
                                <div class="w-10 h-7 rounded-md bg-gradient-to-tr from-amber-300 via-amber-200 to-amber-400 border border-amber-500/40 p-1 flex items-center justify-center shadow-inner">
                                    <div class="w-full h-full border border-amber-600/40 rounded-sm grid grid-cols-2 gap-0.5 opacity-70">
                                        <div class="border-r border-b border-amber-700/50"></div>
                                        <div class="border-b border-amber-700/50"></div>
                                        <div class="border-r border-amber-700/50"></div>
                                        <div></div>
                                    </div>
                                </div>
                                <!-- Contactless Wave -->
                                <svg class="w-5 h-5 text-slate-400 rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                </svg>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold tracking-wider uppercase text-emerald-400">KelolaDuit Card</span>
                                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            </div>
                        </div>

                        <!-- Card Center: Balance & Money Before -->
                        <div class="my-5">
                            <span class="text-xs font-medium uppercase tracking-wider text-slate-400">Saldo Saat Ini (Current Balance)</span>
                            <div class="mt-1">
                                <span class="text-3xl md:text-4xl font-extrabold tracking-tight text-white">
                                    {{ formatRupiah(currentBalance) }}
                                </span>
                            </div>

                            <!-- Money Before (Saldo Sebelumnya) -->
                            <div class="mt-3 inline-flex items-center gap-2 rounded-lg bg-slate-800/80 border border-slate-700/60 px-3 py-1.5 text-xs">
                                <span class="text-slate-400 font-medium">Saldo Sebelumnya:</span>
                                <span class="font-mono font-semibold text-amber-300">
                                    {{ formatRupiah(moneyBefore) }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="flex items-end justify-between pt-3 border-t border-slate-800/80">
                            <div>
                                <span class="text-[10px] font-medium uppercase tracking-widest text-slate-400">Pemilik Dompet</span>
                                <p class="text-sm font-semibold text-slate-200 uppercase tracking-wide">
                                    {{ $page.props.auth.user.name }}
                                </p>
                            </div>
                            <div class="text-right">
                                <Link
                                    :href="route('transactions.index')"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-slate-800/90 hover:bg-slate-700/90 border border-slate-700/60 px-3 py-1.5 text-xs font-medium text-emerald-400 hover:text-emerald-300 transition-colors shadow-sm"
                                >
                                    <span>Lihat Transaksi</span>
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Column -->
                <div class="flex flex-col gap-4 justify-between">
                    <!-- Total Pemasukan -->
                    <div class="flex-1 rounded-2xl border border-slate-800 bg-slate-900/90 p-5 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-slate-400">Total Pemasukan</span>
                            <div class="h-8 w-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p class="text-2xl font-bold text-emerald-400">
                                {{ formatRupiah(totalIncome) }}
                            </p>
                            <span class="text-[11px] text-slate-400">Semua akumulasi uang masuk</span>
                        </div>
                    </div>

                    <!-- Total Pengeluaran -->
                    <div class="flex-1 rounded-2xl border border-slate-800 bg-slate-900/90 p-5 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-slate-400">Total Pengeluaran</span>
                            <div class="h-8 w-8 rounded-lg bg-rose-500/10 flex items-center justify-center text-rose-400 border border-rose-500/20">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p class="text-2xl font-bold text-rose-400">
                                {{ formatRupiah(totalExpense) }}
                            </p>
                            <span class="text-[11px] text-slate-400">Semua akumulasi uang keluar</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Section: Category Budget -->
            <div class="rounded-xl border border-slate-800 bg-slate-900 shadow-sm relative">
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

                    <!-- Date Filter Bar with Custom Vanilla JS Date Range Picker -->
                    <div class="pt-2 flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-400 font-medium">Rentang Tanggal:</span>
                            <DateRangePicker
                                v-model:startDate="filterForm.start_date"
                                v-model:endDate="filterForm.end_date"
                                @change="onRangeChange"
                            />
                        </div>

                        <button
                            v-if="filterForm.start_date || filterForm.end_date"
                            type="button"
                            @click="resetFilter"
                            class="rounded-lg border border-slate-700 px-3 py-1.5 text-xs font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800 transition-colors"
                        >
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Report Table -->
                <div class="overflow-x-auto rounded-b-xl">
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

            <!-- Extra bottom spacer for smooth calendar popover reachability -->
            <div class="h-28"></div>
        </div>
    </AuthenticatedLayout>
</template>
