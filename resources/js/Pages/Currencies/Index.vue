<script setup>
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    currencies: {
        type: Array,
        default: () => [],
    },
});

// Format sample amount mirroring Currencies::format()
const formatSampleCurrency = (currency, amount = 1250000) => {
    const decimals = Math.max(0, Math.min(6, Number(currency.decimal_places) || 0));
    const thouSep = currency.thousand_separator || '.';
    const decSep = currency.decimal_separator || ',';
    const symbol = currency.symbol || '';
    const position = currency.symbol_position || 'prefix';

    const fixed = Number(amount || 0).toFixed(decimals);
    const [intPart, decPart] = fixed.split('.');
    const formattedInt = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, thouSep);
    const formattedNumber = decPart !== undefined ? `${formattedInt}${decSep}${decPart}` : formattedInt;

    return position === 'suffix'
        ? `${formattedNumber} ${symbol}`
        : `${symbol}${formattedNumber}`;
};

// Format exchange rate cleanly
const formatExchangeRate = (rate) => {
    const num = Number(rate);
    if (isNaN(num)) return '-';
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 6,
    }).format(num);
};

// Filter states
const activeStatusFilter = ref('all'); // 'all', 'active', 'inactive', 'base'
const selectedPosition = ref(''); // '', 'prefix', 'suffix'

// Search & Debounce state
const searchQuery = ref('');
const debouncedSearch = ref('');
const isSearching = ref(false);
let debounceTimeout = null;

watch(searchQuery, (val) => {
    isSearching.value = true;
    if (debounceTimeout) clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        debouncedSearch.value = val;
        currentPage.value = 1;
        isSearching.value = false;
    }, 350);
});

const clearSearch = () => {
    if (debounceTimeout) clearTimeout(debounceTimeout);
    searchQuery.value = '';
    debouncedSearch.value = '';
    currentPage.value = 1;
    isSearching.value = false;
};

const hasActiveFilters = computed(() => {
    return (
        activeStatusFilter.value !== 'all' ||
        Boolean(selectedPosition.value) ||
        Boolean(debouncedSearch.value)
    );
});

const resetAllFilters = () => {
    activeStatusFilter.value = 'all';
    selectedPosition.value = '';
    clearSearch();
    currentPage.value = 1;
};

watch([activeStatusFilter, selectedPosition], () => {
    currentPage.value = 1;
});

// Counts for status filter tabs
const activeCount = computed(() => props.currencies.filter((c) => c.is_active).length);
const inactiveCount = computed(() => props.currencies.filter((c) => !c.is_active).length);
const baseCount = computed(() => props.currencies.filter((c) => c.is_base).length);

// Filtered currencies
const filteredCurrencies = computed(() => {
    return props.currencies.filter((curr) => {
        if (activeStatusFilter.value === 'active' && !curr.is_active) {
            return false;
        }
        if (activeStatusFilter.value === 'inactive' && curr.is_active) {
            return false;
        }
        if (activeStatusFilter.value === 'base' && !curr.is_base) {
            return false;
        }

        if (selectedPosition.value && curr.symbol_position !== selectedPosition.value) {
            return false;
        }

        if (debouncedSearch.value.trim()) {
            const q = debouncedSearch.value.trim().toLowerCase();
            const code = curr.code ? String(curr.code).toLowerCase() : '';
            const name = curr.name ? String(curr.name).toLowerCase() : '';
            const symbol = curr.symbol ? String(curr.symbol).toLowerCase() : '';
            if (!code.includes(q) && !name.includes(q) && !symbol.includes(q)) {
                return false;
            }
        }

        return true;
    });
});

// Pagination state
const perPage = ref(10);
const perPageOptions = [10, 25, 50];
const currentPage = ref(1);

watch(perPage, () => {
    currentPage.value = 1;
});

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredCurrencies.value.length / perPage.value));
});

watch([totalPages, filteredCurrencies], () => {
    if (currentPage.value > totalPages.value) {
        currentPage.value = totalPages.value;
    }
});

const paginatedCurrencies = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredCurrencies.value.slice(start, start + perPage.value);
});

const startRecord = computed(() => {
    if (filteredCurrencies.value.length === 0) return 0;
    return (currentPage.value - 1) * perPage.value + 1;
});

const endRecord = computed(() => {
    return Math.min(currentPage.value * perPage.value, filteredCurrencies.value.length);
});

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const goToPage = (p) => {
    if (typeof p === 'number' && p >= 1 && p <= totalPages.value) {
        currentPage.value = p;
    }
};

const displayedPages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;

    if (total <= 5) {
        return Array.from({ length: total }, (_, i) => i + 1);
    }

    if (current <= 3) {
        return [1, 2, 3, 4, '...', total];
    }

    if (current >= total - 2) {
        return [1, '...', total - 3, total - 2, total - 1, total];
    }

    return [1, '...', current - 1, current, current + 1, '...', total];
});

// Delete Modal State
const currencyToDelete = ref(null);
const showDeleteModal = ref(false);
const deleteForm = useForm({});

const confirmDelete = (currency) => {
    currencyToDelete.value = currency;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    currencyToDelete.value = null;
};

const deleteCurrency = () => {
    if (!currencyToDelete.value) return;

    deleteForm.delete(route('currencies.destroy', currencyToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
    });
};
</script>

<template>
    <Head title="Mata Uang (Currencies)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-base font-semibold text-white">
                        Mata Uang (Currencies)
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Kelola daftar mata uang, format penulisan angka, simbol, dan nilai tukar.
                    </p>
                </div>

                <Link
                    :href="route('currencies.create')"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-medium text-white hover:bg-emerald-500 transition-colors shadow-sm"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Mata Uang</span>
                </Link>
            </div>
        </template>

        <div class="p-4 sm:p-6 space-y-4">
            <!-- Filter Section Card -->
            <div class="rounded-xl border border-slate-800 bg-slate-900 p-4 space-y-3.5 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-2 border-b border-slate-800/60">
                    <!-- Status Filter Tabs -->
                    <div class="flex items-center flex-wrap gap-1.5">
                        <button
                            type="button"
                            @click="activeStatusFilter = 'all'"
                            :class="activeStatusFilter === 'all' ? 'bg-slate-800 text-white font-medium' : 'text-slate-400 hover:text-slate-200'"
                            class="rounded-lg px-3 py-1.5 text-xs transition-colors"
                        >
                            Semua ({{ currencies.length }})
                        </button>
                        <button
                            type="button"
                            @click="activeStatusFilter = 'active'"
                            :class="activeStatusFilter === 'active' ? 'bg-slate-800 text-emerald-400 font-medium' : 'text-slate-400 hover:text-slate-200'"
                            class="rounded-lg px-3 py-1.5 text-xs transition-colors"
                        >
                            Aktif ({{ activeCount }})
                        </button>
                        <button
                            type="button"
                            @click="activeStatusFilter = 'inactive'"
                            :class="activeStatusFilter === 'inactive' ? 'bg-slate-800 text-rose-400 font-medium' : 'text-slate-400 hover:text-slate-200'"
                            class="rounded-lg px-3 py-1.5 text-xs transition-colors"
                        >
                            Nonaktif ({{ inactiveCount }})
                        </button>
                        <button
                            type="button"
                            @click="activeStatusFilter = 'base'"
                            :class="activeStatusFilter === 'base' ? 'bg-slate-800 text-amber-400 font-medium' : 'text-slate-400 hover:text-slate-200'"
                            class="rounded-lg px-3 py-1.5 text-xs transition-colors"
                        >
                            Utama ({{ baseCount }})
                        </button>
                    </div>

                    <!-- Reset Filter Button -->
                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        @click="resetAllFilters"
                        class="inline-flex items-center gap-1 self-start sm:self-auto text-xs text-rose-400 hover:text-rose-300 font-medium transition-colors"
                        title="Reset seluruh filter"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Reset Filter</span>
                    </button>
                </div>

                <!-- Filters Row: Posisi Simbol, Search, Per Page -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1">
                        <!-- Posisi Simbol Filter -->
                        <div class="w-full sm:w-48">
                            <select
                                v-model="selectedPosition"
                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-1.5 text-xs text-slate-200 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition-colors cursor-pointer"
                            >
                                <option value="">Semua Posisi Simbol</option>
                                <option value="prefix">Prefix (Di Depan)</option>
                                <option value="suffix">Suffix (Di Belakang)</option>
                            </select>
                        </div>

                        <!-- Debounced Search Input -->
                        <div class="relative w-full sm:w-72">
                            <svg
                                class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                type="text"
                                v-model="searchQuery"
                                placeholder="Cari kode, nama, atau simbol..."
                                class="w-full rounded-lg border border-slate-700 bg-slate-950 pl-8 pr-8 py-1.5 text-xs text-slate-100 placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition-colors"
                            />
                            <button
                                v-if="searchQuery"
                                type="button"
                                @click="clearSearch"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200"
                                title="Hapus pencarian"
                            >
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <span
                                v-else-if="isSearching"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 flex h-2 w-2"
                            >
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Per Page Selector -->
                    <div class="flex items-center gap-2 text-xs text-slate-400 self-end sm:self-auto shrink-0">
                        <span class="whitespace-nowrap">Tampilkan:</span>
                        <select
                            v-model.number="perPage"
                            class="rounded-lg border border-slate-700 bg-slate-950 py-1 pl-2.5 pr-7 text-xs text-slate-200 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition-colors cursor-pointer"
                        >
                            <option v-for="opt in perPageOptions" :key="opt" :value="opt">
                                {{ opt }}
                            </option>
                        </select>
                        <span>data / hal</span>
                    </div>
                </div>
            </div>

            <!-- Currencies Table Card -->
            <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden shadow-sm">
                <!-- Empty State (No data at all) -->
                <div v-if="currencies.length === 0" class="p-12 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-800 text-slate-400 mb-3">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-300">Belum ada data mata uang</p>
                    <p class="text-xs text-slate-500 mt-1">
                        Tambahkan mata uang pertama untuk mulai mengatur preferensi kurs dan format nominal.
                    </p>
                    <div class="mt-4">
                        <Link
                            :href="route('currencies.create')"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-500 transition-colors"
                        >
                            + Tambah Mata Uang
                        </Link>
                    </div>
                </div>

                <!-- Filtered Empty State -->
                <div v-else-if="filteredCurrencies.length === 0" class="p-12 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-800 text-slate-400 mb-3">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-300">Mata uang tidak ditemukan</p>
                    <p class="text-xs text-slate-500 mt-1">
                        Tidak ada mata uang yang cocok dengan filter atau kata kunci pencarian Anda.
                    </p>
                    <button
                        type="button"
                        @click="resetAllFilters"
                        class="mt-3 inline-flex items-center gap-1.5 rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-emerald-400 hover:text-white hover:bg-slate-700 transition-colors font-medium"
                    >
                        Reset Semua Filter
                    </button>
                </div>

                <!-- Responsive Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[720px]">
                        <thead class="border-b border-slate-800 bg-slate-950/60 text-slate-400 uppercase text-[11px] font-semibold tracking-wider">
                            <tr>
                                <th class="px-5 py-3">Kode</th>
                                <th class="px-5 py-3">Nama Mata Uang</th>
                                <th class="px-5 py-3">Simbol &amp; Posisi</th>
                                <th class="px-5 py-3">Contoh Format</th>
                                <th class="px-5 py-3 text-right">Nilai Tukar (Kurs)</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/70">
                            <tr
                                v-for="curr in paginatedCurrencies"
                                :key="curr.id"
                                class="hover:bg-slate-800/30 transition-colors"
                            >
                                <!-- Kode & Base Badge -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-md bg-slate-800 border border-slate-700 px-2 py-0.5 font-mono text-xs font-bold text-white tracking-wider">
                                            {{ curr.code }}
                                        </span>
                                        <span
                                            v-if="curr.is_base"
                                            class="inline-flex items-center rounded-full bg-amber-500/15 border border-amber-500/30 px-2 py-0.5 text-[10px] font-semibold text-amber-400"
                                            title="Mata uang utama aplikasi"
                                        >
                                            Utama
                                        </span>
                                    </div>
                                </td>

                                <!-- Nama Mata Uang -->
                                <td class="px-5 py-3.5 font-medium text-slate-200">
                                    {{ curr.name }}
                                </td>

                                <!-- Simbol & Posisi -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center min-w-[28px] rounded bg-slate-950 border border-slate-800 px-2 py-0.5 font-semibold text-emerald-400">
                                            {{ curr.symbol }}
                                        </span>
                                        <span class="text-[11px] text-slate-400">
                                            {{ curr.symbol_position === 'prefix' ? 'Prefix (Depan)' : 'Suffix (Belakang)' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Contoh Format -->
                                <td class="px-5 py-3.5 font-mono text-slate-300 whitespace-nowrap">
                                    {{ formatSampleCurrency(curr) }}
                                </td>

                                <!-- Nilai Tukar -->
                                <td class="px-5 py-3.5 text-right font-mono font-semibold text-white whitespace-nowrap">
                                    <span>{{ formatExchangeRate(curr.exchange_rate) }}</span>
                                    <span v-if="curr.is_base" class="ml-1 text-[10px] font-normal text-amber-400">(Base)</span>
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    <span
                                        v-if="curr.is_active"
                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 text-[11px] font-medium text-emerald-400"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                        Aktif
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-full bg-rose-500/10 border border-rose-500/20 px-2.5 py-0.5 text-[11px] font-medium text-rose-400"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-400"></span>
                                        Nonaktif
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="px-5 py-3.5 text-right space-x-1 whitespace-nowrap">
                                    <Link
                                        :href="route('currencies.edit', curr.id)"
                                        class="inline-flex items-center rounded px-2 py-1 text-slate-400 hover:text-emerald-400 hover:bg-slate-800 transition-colors"
                                        title="Edit Mata Uang"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </Link>
                                    <button
                                        type="button"
                                        :disabled="curr.is_base"
                                        @click="confirmDelete(curr)"
                                        class="inline-flex items-center rounded px-2 py-1 text-slate-400 hover:text-rose-400 hover:bg-slate-800 disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:text-slate-400 disabled:hover:bg-transparent transition-colors"
                                        :title="curr.is_base ? 'Mata uang utama tidak dapat dihapus' : 'Hapus Mata Uang'"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div
                    v-if="filteredCurrencies.length > 0"
                    class="border-t border-slate-800 bg-slate-950/40 px-5 py-3.5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs"
                >
                    <div class="text-slate-400 text-center sm:text-left">
                        Menampilkan
                        <span class="font-medium text-slate-200">{{ startRecord }}</span>
                        -
                        <span class="font-medium text-slate-200">{{ endRecord }}</span>
                        dari
                        <span class="font-medium text-slate-200">{{ filteredCurrencies.length }}</span>
                        mata uang
                        <span v-if="hasActiveFilters" class="text-slate-500">
                            (difilter dari {{ currencies.length }} total)
                        </span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            @click="prevPage"
                            :disabled="currentPage === 1"
                            class="inline-flex items-center gap-1 rounded-lg border border-slate-800 bg-slate-900 px-3 py-1.5 text-xs font-medium text-slate-300 hover:bg-slate-800 hover:text-white disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span>Sebelumnya</span>
                        </button>

                        <div class="hidden sm:flex items-center gap-1">
                            <template v-for="(p, idx) in displayedPages" :key="idx">
                                <span
                                    v-if="p === '...'"
                                    class="px-2 py-1 text-xs text-slate-600 select-none"
                                >
                                    ...
                                </span>
                                <button
                                    v-else
                                    type="button"
                                    @click="goToPage(p)"
                                    :class="p === currentPage ? 'bg-emerald-600 text-white font-semibold' : 'border border-slate-800 bg-slate-900 text-slate-300 hover:bg-slate-800 hover:text-white'"
                                    class="min-w-[32px] h-8 rounded-lg px-2 text-xs transition-colors flex items-center justify-center"
                                >
                                    {{ p }}
                                </button>
                            </template>
                        </div>

                        <span class="sm:hidden px-2 text-xs text-slate-400">
                            {{ currentPage }} / {{ totalPages }}
                        </span>

                        <button
                            type="button"
                            @click="nextPage"
                            :disabled="currentPage === totalPages"
                            class="inline-flex items-center gap-1 rounded-lg border border-slate-800 bg-slate-900 px-3 py-1.5 text-xs font-medium text-slate-300 hover:bg-slate-800 hover:text-white disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            <span>Selanjutnya</span>
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal" max-width="md">
            <div class="p-6 bg-slate-900 text-slate-100 rounded-xl border border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-white">
                            Hapus Mata Uang
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">
                            Apakah Anda yakin ingin menghapus mata uang
                            <span class="font-medium text-slate-200">"{{ currencyToDelete?.code }} - {{ currencyToDelete?.name }}"</span>? Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2.5">
                    <button
                        type="button"
                        @click="closeDeleteModal"
                        class="rounded-lg border border-slate-700 px-3.5 py-2 text-xs font-medium text-slate-300 hover:bg-slate-800 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        :disabled="deleteForm.processing"
                        @click="deleteCurrency"
                        class="rounded-lg bg-rose-600 px-3.5 py-2 text-xs font-medium text-white hover:bg-rose-500 disabled:opacity-50 transition-colors"
                    >
                        {{ deleteForm.processing ? 'Menghapus...' : 'Hapus' }}
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
