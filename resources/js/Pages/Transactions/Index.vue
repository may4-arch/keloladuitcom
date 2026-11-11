<script setup>
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    transactions: {
        type: Array,
        default: () => [],
    },
});

// Format date helper (e.g. "30 Okt 2026")
const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

// Format currency helper
const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

// Type Badge helpers
const getTypeBadgeClass = (type) => {
    if (type === 'hutang') {
        return 'bg-amber-950/60 text-amber-400 border-amber-800/60';
    }
    if (type === 'piutang') {
        return 'bg-sky-950/60 text-sky-400 border-sky-800/60';
    }
    return 'bg-slate-800 text-slate-300 border-slate-700/60';
};

const getTypeLabel = (type) => {
    if (type === 'hutang') return 'Hutang';
    if (type === 'piutang') return 'Piutang';
    return 'Normal';
};

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

// Filtered Transactions by Category name, Notes, or Type
const filteredTransactions = computed(() => {
    const q = debouncedSearch.value.trim().toLowerCase();
    if (!q) return props.transactions;

    return props.transactions.filter((trx) => {
        const catName = trx.category?.name ? String(trx.category.name).toLowerCase() : '';
        const notes = trx.notes ? String(trx.notes).toLowerCase() : '';
        const type = trx.type ? String(trx.type).toLowerCase() : 'normal';
        return catName.includes(q) || notes.includes(q) || type.includes(q);
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
    return Math.max(1, Math.ceil(filteredTransactions.value.length / perPage.value));
});

watch([totalPages, filteredTransactions], () => {
    if (currentPage.value > totalPages.value) {
        currentPage.value = totalPages.value;
    }
});

const paginatedTransactions = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredTransactions.value.slice(start, start + perPage.value);
});

const startRecord = computed(() => {
    if (filteredTransactions.value.length === 0) return 0;
    return (currentPage.value - 1) * perPage.value + 1;
});

const endRecord = computed(() => {
    return Math.min(currentPage.value * perPage.value, filteredTransactions.value.length);
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

// Displayed page numbers for pagination bar
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
const transactionToDelete = ref(null);
const showDeleteModal = ref(false);
const deleteForm = useForm({});

const confirmDelete = (trx) => {
    transactionToDelete.value = trx;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    transactionToDelete.value = null;
};

const deleteTransaction = () => {
    if (!transactionToDelete.value) return;

    deleteForm.delete(route('transactions.destroy', transactionToDelete.value.transaction_id), {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
    });
};

// View Detail Modal State
const selectedTransaction = ref(null);
const showDetailModal = ref(false);

const openDetailModal = (trx) => {
    selectedTransaction.value = trx;
    showDetailModal.value = true;
};

const closeDetailModal = () => {
    showDetailModal.value = false;
    selectedTransaction.value = null;
};
</script>

<template>
    <Head title="Transaksi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-base font-semibold text-white">
                        Transaksi
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Kelola dan pantau catatan transaksi keuangan Anda.
                    </p>
                </div>

                <Link
                    :href="route('transactions.create')"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-medium text-white hover:bg-emerald-500 transition-colors shadow-sm"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Transaksi</span>
                </Link>
            </div>
        </template>

        <div class="p-4 sm:p-6 space-y-4">
            <!-- Toolbar: Search & Per Page Selector -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <!-- Debounced Search Input -->
                <div class="relative w-full sm:w-80">
                    <svg
                        class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Cari kategori atau catatan..."
                        class="w-full rounded-lg border border-slate-800 bg-slate-900 pl-9 pr-8 py-2 text-xs text-slate-100 placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition-colors"
                    />

                    <!-- Clear / Typing Indicator -->
                    <button
                        v-if="searchQuery"
                        type="button"
                        @click="clearSearch"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200"
                        title="Hapus pencarian"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <span
                        v-else-if="isSearching"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 flex h-2 w-2 relative"
                    >
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                </div>

                <!-- Per Page Selector -->
                <div class="flex items-center justify-between sm:justify-end gap-3 text-xs text-slate-400">
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400 whitespace-nowrap">Tampilkan:</span>
                        <select
                            v-model.number="perPage"
                            class="rounded-lg border border-slate-800 bg-slate-900 py-1.5 pl-3 pr-8 text-xs text-slate-200 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition-colors cursor-pointer"
                        >
                            <option v-for="opt in perPageOptions" :key="opt" :value="opt">
                                {{ opt }}
                            </option>
                        </select>
                        <span class="text-slate-400">data / hal</span>
                    </div>
                </div>
            </div>

            <!-- Transactions Table Card -->
            <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden shadow-sm">
                <!-- If No Data at All -->
                <div v-if="transactions.length === 0" class="p-12 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-800 text-slate-400 mb-3">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-300">Belum ada data transaksi</p>
                    <p class="text-xs text-slate-500 mt-1">
                        Mulai mencatat arus kas Anda dengan menambahkan transaksi pertama.
                    </p>
                    <div class="mt-4">
                        <Link
                            :href="route('transactions.create')"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-500 transition-colors"
                        >
                            + Tambah Transaksi
                        </Link>
                    </div>
                </div>

                <!-- If Search Results Empty -->
                <div v-else-if="filteredTransactions.length === 0" class="p-12 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-800 text-slate-400 mb-3">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-300">Tidak ada transaksi ditemukan</p>
                    <p class="text-xs text-slate-500 mt-1">
                        Tidak ada transaksi yang cocok dengan kata kunci "<span class="text-slate-300 font-medium">{{ debouncedSearch }}</span>".
                    </p>
                    <button
                        type="button"
                        @click="clearSearch"
                        class="mt-3 text-xs text-emerald-400 hover:text-emerald-300 underline font-medium"
                    >
                        Reset pencarian
                    </button>
                </div>

                <!-- Table Content -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[640px]">
                        <thead class="border-b border-slate-800 bg-slate-950/60 text-slate-400 uppercase text-[11px] font-semibold tracking-wider">
                            <tr>
                                <th class="px-5 py-3">Kategori</th>
                                <th class="px-5 py-3">Tipe</th>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Total</th>
                                <th class="px-5 py-3">Catatan</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/70">
                            <tr
                                v-for="trx in paginatedTransactions"
                                :key="trx.transaction_id"
                                class="hover:bg-slate-800/40 transition-colors"
                            >
                                <!-- Category -->
                                <td class="px-5 py-3.5 font-medium text-slate-200">
                                    <div class="flex items-center gap-2.5">
                                        <span
                                            v-if="trx.category?.type === 1"
                                            class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-emerald-950/60 text-emerald-400 border border-emerald-800/50 shrink-0"
                                            title="Pemasukan"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                            </svg>
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-rose-950/60 text-rose-400 border border-rose-800/50 shrink-0"
                                            title="Pengeluaran"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                            </svg>
                                        </span>
                                        <div>
                                            <div class="font-medium text-slate-100">
                                                {{ trx.category ? trx.category.name : '-' }}
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-normal">
                                                {{ trx.category?.type === 1 ? 'Pemasukan' : 'Pengeluaran' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tipe -->
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium border"
                                        :class="getTypeBadgeClass(trx.type)"
                                    >
                                        {{ getTypeLabel(trx.type) }}
                                    </span>
                                </td>

                                <!-- Date -->
                                <td class="px-5 py-3.5 text-slate-300 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span v-if="trx.todate">
                                            {{ formatDate(trx.date) }} <span class="text-slate-500">&rarr;</span> {{ formatDate(trx.todate) }}
                                        </span>
                                        <span v-else>{{ formatDate(trx.date) }}</span>
                                    </div>
                                </td>

                                <!-- Total -->
                                <td
                                    class="px-5 py-3.5 font-semibold whitespace-nowrap"
                                    :class="trx.category?.type === 1 ? 'text-emerald-400' : 'text-rose-400'"
                                >
                                    {{ trx.category?.type === 1 ? '+' : '-' }} {{ formatRupiah(trx.total) }}
                                </td>

                                <!-- Notes -->
                                <td class="px-5 py-3.5 text-slate-400 max-w-xs">
                                    <span class="truncate block" :title="trx.notes">
                                        {{ trx.notes || '-' }}
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        <button
                                            v-if="trx.details && trx.details.length > 0"
                                            type="button"
                                            @click="openDetailModal(trx)"
                                            class="rounded px-2 py-1 text-xs font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors"
                                            title="Lihat Detail Item"
                                        >
                                            Detail
                                        </button>
                                        <Link
                                            :href="route('transactions.edit', trx.transaction_id)"
                                            class="rounded px-2 py-1 text-xs font-medium text-slate-300 hover:bg-slate-800 hover:text-emerald-400 transition-colors"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            type="button"
                                            @click="confirmDelete(trx)"
                                            class="rounded px-2 py-1 text-xs font-medium text-rose-400 hover:bg-rose-950/40 hover:text-rose-300 transition-colors"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div
                    v-if="filteredTransactions.length > 0"
                    class="border-t border-slate-800 bg-slate-950/40 px-5 py-3.5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs"
                >
                    <!-- Info Counter -->
                    <div class="text-slate-400 text-center sm:text-left">
                        Menampilkan
                        <span class="font-medium text-slate-200">{{ startRecord }}</span>
                        -
                        <span class="font-medium text-slate-200">{{ endRecord }}</span>
                        dari
                        <span class="font-medium text-slate-200">{{ filteredTransactions.length }}</span>
                        transaksi
                        <span v-if="debouncedSearch" class="text-slate-500">
                            (difilter dari {{ transactions.length }} total)
                        </span>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex items-center gap-1.5">
                        <!-- Previous Button -->
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

                        <!-- Page Number Pills -->
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

                        <!-- Current Page Indicator on Mobile -->
                        <span class="sm:hidden px-2 text-xs text-slate-400">
                            {{ currentPage }} / {{ totalPages }}
                        </span>

                        <!-- Next Button -->
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

        <!-- Detail Modal -->
        <Modal :show="showDetailModal" @close="closeDetailModal" max-width="lg">
            <div class="p-6">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h2 class="text-base font-semibold text-white">
                        Detail Item Transaksi
                    </h2>
                    <button @click="closeDetailModal" class="text-slate-400 hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs text-slate-400">
                        <div>Kategori: <strong class="text-slate-200 block">{{ selectedTransaction?.category?.name || '-' }}</strong></div>
                        <div>Tipe: <strong class="text-slate-200 block">{{ getTypeLabel(selectedTransaction?.type) }}</strong></div>
                        <div>
                            Tanggal:
                            <strong class="text-slate-200 block">
                                {{ selectedTransaction?.todate ? `${formatDate(selectedTransaction?.date)} s/d ${formatDate(selectedTransaction?.todate)}` : formatDate(selectedTransaction?.date) }}
                            </strong>
                        </div>
                    </div>

                    <p v-if="selectedTransaction?.notes" class="text-xs text-slate-400">
                        Catatan: <span class="text-slate-200">{{ selectedTransaction.notes }}</span>
                    </p>

                    <div class="rounded-lg border border-slate-800 bg-slate-950 overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-slate-800 bg-slate-900/60 text-slate-400 text-[11px] uppercase font-semibold">
                                <tr>
                                    <th class="px-3.5 py-2">Item</th>
                                    <th class="px-3.5 py-2 text-center">Qty</th>
                                    <th class="px-3.5 py-2 text-right">Harga</th>
                                    <th class="px-3.5 py-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                <tr
                                    v-for="d in selectedTransaction?.details"
                                    :key="d.transaction_detail_id"
                                    class="text-slate-300"
                                >
                                    <td class="px-3.5 py-2.5 font-medium text-slate-200">{{ d.item_name }}</td>
                                    <td class="px-3.5 py-2.5 text-center">{{ d.quantity }}</td>
                                    <td class="px-3.5 py-2.5 text-right">{{ formatRupiah(d.price) }}</td>
                                    <td class="px-3.5 py-2.5 text-right font-medium text-slate-100">{{ formatRupiah(d.subtotal) }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="border-t border-slate-800 bg-slate-900/40 font-semibold text-slate-100">
                                <tr>
                                    <td colspan="3" class="px-3.5 py-2.5 text-right">Total Keseluruhan:</td>
                                    <td
                                        class="px-3.5 py-2.5 text-right font-bold"
                                        :class="selectedTransaction?.category?.type === 1 ? 'text-emerald-400' : 'text-rose-400'"
                                    >
                                        {{ selectedTransaction?.category?.type === 1 ? '+' : '-' }} {{ formatRupiah(selectedTransaction?.total) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button
                        type="button"
                        @click="closeDetailModal"
                        class="rounded-lg border border-slate-700 px-3.5 py-1.5 text-xs font-medium text-slate-300 hover:bg-slate-800 transition-colors"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal" max-width="md">
            <div class="p-6">
                <h2 class="text-base font-semibold text-white">
                    Hapus Transaksi
                </h2>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                    Apakah Anda yakin ingin menghapus transaksi tanggal
                    <span class="font-semibold text-slate-200">{{ formatDate(transactionToDelete?.date) }}</span>
                    pada kategori
                    <span class="font-semibold text-slate-200">"{{ transactionToDelete?.category?.name || '-' }}"</span>?
                    Tindakan ini juga akan menghapus seluruh detail item terkait secara permanen.
                </p>

                <div class="mt-6 flex justify-end gap-2.5">
                    <button
                        type="button"
                        @click="closeDeleteModal"
                        class="rounded-lg border border-slate-700 px-3.5 py-1.5 text-xs font-medium text-slate-300 hover:bg-slate-800 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="deleteTransaction"
                        :disabled="deleteForm.processing"
                        class="rounded-lg bg-rose-600 px-3.5 py-1.5 text-xs font-medium text-white hover:bg-rose-500 disabled:opacity-50 transition-colors"
                    >
                        {{ deleteForm.processing ? 'Menghapus...' : 'Hapus Transaksi' }}
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
