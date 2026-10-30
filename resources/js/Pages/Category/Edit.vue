<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.category.name,
    type: props.category.type,
    priority: props.category.priority ?? 0,
});

const submit = () => {
    form.put(route('categories.update', props.category.category_id));
};
</script>

<template>
    <Head title="Edit Kategori" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-base font-semibold text-white">
                        Edit Kategori
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Perbarui detail kategori <span class="text-slate-200 font-medium">"{{ category.name }}"</span>.
                    </p>
                </div>

                <Link
                    :href="route('categories.index')"
                    class="rounded-lg border border-slate-700 px-3 py-1.5 text-xs text-slate-300 hover:bg-slate-800 transition-colors"
                >
                    Kembali
                </Link>
            </div>
        </template>

        <div class="p-4 sm:p-6">
            <div class="mx-auto max-w-xl rounded-xl border border-slate-800 bg-slate-900 p-6 shadow-sm">
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Nama Kategori -->
                    <div>
                        <label for="name" class="mb-1 block text-xs font-medium text-slate-300">
                            Nama Kategori
                        </label>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            autofocus
                            placeholder="Nama kategori"
                            class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            :class="{ 'border-rose-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-rose-400">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Tipe Kategori -->
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-300">
                            Tipe Transaksi
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label
                                :class="form.type === 2 ? 'border-rose-500/50 bg-rose-950/20 text-rose-300' : 'border-slate-800 bg-slate-950 text-slate-400 hover:border-slate-700'"
                                class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border p-3 text-xs font-medium transition-colors"
                            >
                                <input
                                    type="radio"
                                    :value="2"
                                    v-model="form.type"
                                    class="sr-only"
                                />
                                <span class="h-2 w-2 rounded-full" :class="form.type === 2 ? 'bg-rose-400' : 'bg-slate-600'"></span>
                                <span>Pengeluaran (Expense)</span>
                            </label>

                            <label
                                :class="form.type === 1 ? 'border-emerald-500/50 bg-emerald-950/20 text-emerald-300' : 'border-slate-800 bg-slate-950 text-slate-400 hover:border-slate-700'"
                                class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border p-3 text-xs font-medium transition-colors"
                            >
                                <input
                                    type="radio"
                                    :value="1"
                                    v-model="form.type"
                                    class="sr-only"
                                />
                                <span class="h-2 w-2 rounded-full" :class="form.type === 1 ? 'bg-emerald-400' : 'bg-slate-600'"></span>
                                <span>Pemasukan (Income)</span>
                            </label>
                        </div>
                        <p v-if="form.errors.type" class="mt-1 text-xs text-rose-400">
                            {{ form.errors.type }}
                        </p>
                    </div>

                    <!-- Prioritas Checkbox -->
                    <div class="pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input
                                id="priority"
                                type="checkbox"
                                :true-value="1"
                                :false-value="0"
                                v-model="form.priority"
                                class="h-4 w-4 rounded border-slate-700 bg-slate-950 text-emerald-600 focus:ring-0 focus:ring-offset-0 transition-colors cursor-pointer"
                            />
                            <span class="text-xs font-medium text-slate-300">
                                Jadikan Kategori Prioritas
                            </span>
                        </label>
                        <p v-if="form.errors.priority" class="mt-1 text-xs text-rose-400">
                            {{ form.errors.priority }}
                        </p>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                        <Link
                            :href="route('categories.index')"
                            class="rounded-lg border border-slate-700 px-4 py-2 text-xs font-medium text-slate-300 hover:bg-slate-800 transition-colors"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-medium text-white hover:bg-emerald-500 disabled:opacity-50 transition-colors"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
