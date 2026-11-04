<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    budgets: {
        type: Array,
        default: () => [],
    },
});

// Format currency helper
const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

// Filter state
const activeFilter = ref('all'); // 'all', 'priority', 'non-priority'
const searchKeyword = ref('');

const filteredBudgets = computed(() => {
    let list = props.budgets;

    if (activeFilter.value === 'priority') {
        list = list.filter((b) => b.priority === 1);
    } else if (activeFilter.value === 'non-priority') {
        list = list.filter((b) => b.priority === 0);
    }

    if (searchKeyword.value.trim()) {
        const q = searchKeyword.value.toLowerCase().trim();
        list = list.filter(
            (b) =>
                b.name.toLowerCase().includes(q) ||
                (b.category?.name && b.category.name.toLowerCase().includes(q))
        );
    }

    return list;
});

// Delete Modal State
const budgetToDelete = ref(null);
const showDeleteModal = ref(false);
const deleteForm = useForm({});

const confirmDelete = (budget) => {
    budgetToDelete.value = budget;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    budgetToDelete.value = null;
};

const deleteBudget = () => {
    if (!budgetToDelete.value) return;

    deleteForm.delete(route('budgets.destroy', budgetToDelete.value.budget_id), {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
    });
};
</script>

<template>
    <Head title="Anggaran (Budget)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-base font-semibold text-white">
                        Anggaran (Budget)
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Kelola batas minimal dan maksimal alokasi pengeluaran per kategori.
                    </p>
                </div>

                <Link
                    :href="route('budgets.create')"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-medium text-white hover:bg-emerald-500 transition-colors shadow-sm"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Anggaran</span>
                </Link>
            </div>
        </template>

        <div class="p-4 sm:p-6 space-y-4">
            <!-- Filter & Search Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <!-- Tabs -->
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="activeFilter = 'all'"
                        :class="activeFilter === 'all' ? 'bg-slate-800 text-white font-medium' : 'text-slate-400 hover:text-slate-200'"
                        class="rounded-lg px-3 py-1.5 text-xs transition-colors"
                    >
                        Semua ({{ budgets.length }})
                    </button>
                    <button
                        type="button"
                        @click="activeFilter = 'priority'"
                        :class="activeFilter === 'priority' ? 'bg-slate-800 text-emerald-400 font-medium' : 'text-slate-400 hover:text-slate-200'"
                        class="rounded-lg px-3 py-1.5 text-xs transition-colors"
                    >
                        Prioritas ({{ budgets.filter((b) => b.priority === 1).length }})
                    </button>
                    <button
                        type="button"
                        @click="activeFilter = 'non-priority'"
                        :class="activeFilter === 'non-priority' ? 'bg-slate-800 text-slate-300 font-medium' : 'text-slate-400 hover:text-slate-200'"
                        class="rounded-lg px-3 py-1.5 text-xs transition-colors"
                    >
                        Biasa ({{ budgets.filter((b) => b.priority === 0).length }})
                    </button>
                </div>

                <!-- Search Input -->
                <div class="relative w-full sm:w-64">
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
                        v-model="searchKeyword"
                        placeholder="Cari anggaran..."
                        class="w-full rounded-lg border border-slate-800 bg-slate-900 pl-8 pr-3 py-1.5 text-xs text-slate-100 placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    />
                </div>
            </div>

            <!-- Budgets Table -->
            <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden shadow-sm">
                <div v-if="filteredBudgets.length === 0" class="p-8 text-center text-xs text-slate-500">
                    Belum ada anggaran pada filter ini. Klik <Link :href="route('budgets.create')" class="text-emerald-400 font-medium hover:underline">Tambah Anggaran</Link> untuk membuat alokasi baru.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-800 bg-slate-950/60 text-slate-400 uppercase text-[11px] font-semibold tracking-wider">
                            <tr>
                                <th class="px-5 py-3">Nama Anggaran</th>
                                <th class="px-5 py-3">Kategori</th>
                                <th class="px-5 py-3 text-right">Batas Minimal</th>
                                <th class="px-5 py-3 text-right text-emerald-400/90">Batas Maksimal</th>
                                <th class="px-5 py-3 text-center">Prioritas</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/70">
                            <tr
                                v-for="budget in filteredBudgets"
                                :key="budget.budget_id"
                                class="hover:bg-slate-800/30 transition-colors"
                            >
                                <!-- Nama Anggaran -->
                                <td class="px-5 py-3.5 font-medium text-slate-200">
                                    {{ budget.name }}
                                </td>

                                <!-- Kategori -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <span
                                            v-if="budget.category"
                                            :class="[
                                                budget.category.type === 1
                                                    ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                                                    : 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
                                                'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium'
                                            ]"
                                        >
                                            {{ budget.category.type === 1 ? 'Pemasukan' : 'Pengeluaran' }}
                                        </span>
                                        <span class="text-slate-300 font-medium">
                                            {{ budget.category?.name || '-' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Batas Minimal -->
                                <td class="px-5 py-3.5 text-right font-mono text-slate-400 whitespace-nowrap">
                                    {{ formatRupiah(budget.minimal) }}
                                </td>

                                <!-- Batas Maksimal -->
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-white whitespace-nowrap">
                                    {{ formatRupiah(budget.maximal) }}
                                </td>

                                <!-- Prioritas (Checklist) -->
                                <td class="px-5 py-3.5 text-center">
                                    <span
                                        v-if="budget.priority === 1"
                                        class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"
                                        title="Prioritas"
                                    >
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                    <span v-else class="text-slate-600">-</span>
                                </td>

                                <!-- Aksi -->
                                <td class="px-5 py-3.5 text-right space-x-2 whitespace-nowrap">
                                    <Link
                                        :href="route('budgets.edit', budget.budget_id)"
                                        class="inline-flex items-center rounded px-2 py-1 text-slate-400 hover:text-emerald-400 hover:bg-slate-800 transition-colors"
                                        title="Edit Anggaran"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </Link>
                                    <button
                                        type="button"
                                        @click="confirmDelete(budget)"
                                        class="inline-flex items-center rounded px-2 py-1 text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors"
                                        title="Hapus Anggaran"
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
                            Hapus Anggaran
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">
                            Apakah Anda yakin ingin menghapus anggaran
                            <span class="font-medium text-slate-200">"{{ budgetToDelete?.name }}"</span>? Tindakan ini tidak dapat dibatalkan.
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
                        @click="deleteBudget"
                        class="rounded-lg bg-rose-600 px-3.5 py-2 text-xs font-medium text-white hover:bg-rose-500 disabled:opacity-50 transition-colors"
                    >
                        {{ deleteForm.processing ? 'Menghapus...' : 'Hapus' }}
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

