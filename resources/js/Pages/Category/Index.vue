<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});

// Filter
const activeFilter = ref('all'); // 'all', '1', '2'

const filteredCategories = computed(() => {
    if (activeFilter.value === '1') {
        return props.categories.filter((c) => c.type === 1);
    }
    if (activeFilter.value === '2') {
        return props.categories.filter((c) => c.type === 2);
    }
    return props.categories;
});

// Delete Modal State
const categoryToDelete = ref(null);
const showDeleteModal = ref(false);
const deleteForm = useForm({});

const confirmDelete = (category) => {
    categoryToDelete.value = category;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    categoryToDelete.value = null;
};

const deleteCategory = () => {
    if (!categoryToDelete.value) return;

    deleteForm.delete(route('categories.destroy', categoryToDelete.value.category_id), {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
    });
};
</script>

<template>
    <Head title="Kategori" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-base font-semibold text-white">
                        Kategori
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Kelola kategori pemasukan dan pengeluaran Anda.
                    </p>
                </div>

                <Link
                    :href="route('categories.create')"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-medium text-white hover:bg-emerald-500 transition-colors"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Kategori</span>
                </Link>
            </div>
        </template>

        <div class="p-4 sm:p-6 space-y-4">
            <!-- Filter Tabs -->
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="activeFilter = 'all'"
                    :class="activeFilter === 'all' ? 'bg-slate-800 text-white font-medium' : 'text-slate-400 hover:text-slate-200'"
                    class="rounded-lg px-3 py-1.5 text-xs transition-colors"
                >
                    Semua ({{ categories.length }})
                </button>
                <button
                    type="button"
                    @click="activeFilter = '1'"
                    :class="activeFilter === '1' ? 'bg-slate-800 text-emerald-400 font-medium' : 'text-slate-400 hover:text-slate-200'"
                    class="rounded-lg px-3 py-1.5 text-xs transition-colors"
                >
                    Pemasukan ({{ categories.filter((c) => c.type === 1).length }})
                </button>
                <button
                    type="button"
                    @click="activeFilter = '2'"
                    :class="activeFilter === '2' ? 'bg-slate-800 text-rose-400 font-medium' : 'text-slate-400 hover:text-slate-200'"
                    class="rounded-lg px-3 py-1.5 text-xs transition-colors"
                >
                    Pengeluaran ({{ categories.filter((c) => c.type === 2).length }})
                </button>
            </div>

            <!-- Categories Table / Card -->
            <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden shadow-sm">
                <div v-if="filteredCategories.length === 0" class="p-8 text-center text-xs text-slate-500">
                    Belum ada kategori pada filter ini. Klik <span class="text-emerald-400 font-medium">Tambah Kategori</span> untuk membuat baru.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-800 bg-slate-950/60 text-slate-400 uppercase text-[11px] font-semibold tracking-wider">
                            <tr>
                                <th class="px-5 py-3">Nama Kategori</th>
                                <th class="px-5 py-3">Tipe</th>
                                <th class="px-5 py-3 text-center">Prioritas</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/70">
                            <tr
                                v-for="category in filteredCategories"
                                :key="category.category_id"
                                class="hover:bg-slate-800/30 transition-colors"
                            >
                                <td class="px-5 py-3.5 font-medium text-slate-200">
                                    {{ category.name }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span
                                        v-if="category.type === 1"
                                        class="inline-flex items-center rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-medium text-emerald-400 border border-emerald-500/20"
                                    >
                                        Pemasukan
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center rounded-full bg-rose-500/10 px-2 py-0.5 text-[11px] font-medium text-rose-400 border border-rose-500/20"
                                    >
                                        Pengeluaran
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span
                                        v-if="category.priority === 1"
                                        class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"
                                        title="Prioritas"
                                    >
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                    <span v-else class="text-slate-600">-</span>
                                </td>
                                <td class="px-5 py-3.5 text-right space-x-2">
                                    <Link
                                        :href="route('categories.edit', category.category_id)"
                                        class="text-xs font-medium text-slate-400 hover:text-emerald-400 transition-colors"
                                    >
                                        Edit
                                    </Link>
                                    <span class="text-slate-700">|</span>
                                    <button
                                        type="button"
                                        @click="confirmDelete(category)"
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

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal" max-width="md">
            <div class="p-6">
                <h2 class="text-base font-semibold text-white">
                    Hapus Kategori
                </h2>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                    Apakah Anda yakin ingin menghapus kategori
                    <span class="font-semibold text-slate-200">"{{ categoryToDelete?.name }}"</span>?
                    Tindakan ini tidak dapat dibatalkan.
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
                        @click="deleteCategory"
                        :disabled="deleteForm.processing"
                        class="rounded-lg bg-rose-600 px-3.5 py-1.5 text-xs font-medium text-white hover:bg-rose-500 disabled:opacity-50 transition-colors"
                    >
                        {{ deleteForm.processing ? 'Menghapus...' : 'Hapus Kategori' }}
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
