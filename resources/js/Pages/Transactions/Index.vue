<script setup>
import { ref } from 'vue';
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
            <div class="flex items-center justify-between">
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
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-medium text-white hover:bg-emerald-500 transition-colors"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Transaksi</span>
                </Link>
            </div>
        </template>

        <div class="p-4 sm:p-6 space-y-4">
            <!-- Transactions Table Card -->
            <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden shadow-sm">
                <div v-if="transactions.length === 0" class="p-8 text-center text-xs text-slate-500">
                    Belum ada data transaksi. Klik <span class="text-emerald-400 font-medium">Tambah Transaksi</span> untuk membuat baru.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-800 bg-slate-950/60 text-slate-400 uppercase text-[11px] font-semibold tracking-wider">
                            <tr>
                                <th class="px-5 py-3">Category</th>
                                <th class="px-5 py-3">Date</th>
                                <th class="px-5 py-3">Total</th>
                                <th class="px-5 py-3">Notes</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/70">
                            <tr
                                v-for="trx in transactions"
                                :key="trx.transaction_id"
                                class="hover:bg-slate-800/30 transition-colors"
                            >
                                <!-- Category -->
                                <td class="px-5 py-3.5 font-medium text-slate-200">
                                    <div class="flex items-center gap-2">
                                        <span
                                            v-if="trx.category?.type === 1"
                                            class="h-2 w-2 rounded-full bg-emerald-400 shrink-0"
                                            title="Pemasukan"
                                        ></span>
                                        <span
                                            v-else
                                            class="h-2 w-2 rounded-full bg-rose-400 shrink-0"
                                            title="Pengeluaran"
                                        ></span>
                                        <span>{{ trx.category ? trx.category.name : '-' }}</span>
                                    </div>
                                </td>

                                <!-- Date -->
                                <td class="px-5 py-3.5 text-slate-300 whitespace-nowrap">
                                    {{ formatDate(trx.date) }}
                                </td>

                                <!-- Total -->
                                <td
                                    class="px-5 py-3.5 font-semibold whitespace-nowrap"
                                    :class="trx.category?.type === 1 ? 'text-emerald-400' : 'text-rose-400'"
                                >
                                    {{ trx.category?.type === 1 ? '+' : '-' }} {{ formatRupiah(trx.total) }}
                                </td>

                                <!-- Notes -->
                                <td class="px-5 py-3.5 text-slate-400 max-w-xs truncate">
                                    {{ trx.notes || '-' }}
                                </td>

                                <!-- Aksi -->
                                <td class="px-5 py-3.5 text-right space-x-2 whitespace-nowrap">
                                    <button
                                        v-if="trx.details && trx.details.length > 0"
                                        type="button"
                                        @click="openDetailModal(trx)"
                                        class="text-xs font-medium text-slate-400 hover:text-slate-200 transition-colors"
                                        title="Lihat Detail Item"
                                    >
                                        Detail
                                    </button>
                                    <span v-if="trx.details && trx.details.length > 0" class="text-slate-700">|</span>
                                    <Link
                                        :href="route('transactions.edit', trx.transaction_id)"
                                        class="text-xs font-medium text-slate-400 hover:text-emerald-400 transition-colors"
                                    >
                                        Edit
                                    </Link>
                                    <span class="text-slate-700">|</span>
                                    <button
                                        type="button"
                                        @click="confirmDelete(trx)"
                                        class="text-xs font-medium text-slate-400 hover:text-rose-400 transition-colors"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span>Kategori: <strong class="text-slate-200">{{ selectedTransaction?.category?.name || '-' }}</strong></span>
                        <span>Tanggal: <strong class="text-slate-200">{{ formatDate(selectedTransaction?.date) }}</strong></span>
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
