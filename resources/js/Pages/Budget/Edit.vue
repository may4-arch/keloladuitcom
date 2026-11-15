<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchableCategorySelect from '@/Components/SearchableCategorySelect.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    budget: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const parseInitialDate = (val) => {
    if (!val) return '';
    return String(val).split('T')[0];
};

const form = useForm({
    name: props.budget.name || '',
    category_id: props.budget.category_id || null,
    maximal: props.budget.maximal || '',
    minimal: props.budget.minimal || 0,
    priority: props.budget.priority ?? 0,
    start_date: parseInitialDate(props.budget.start_date),
    end_date: parseInitialDate(props.budget.end_date),
});

const submit = () => {
    form.put(route('budgets.update', props.budget.budget_id));
};
</script>

<template>
    <Head title="Edit Anggaran" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-base font-semibold text-white">
                        Edit Anggaran
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Perbarui rincian batas nominal dan kategori anggaran Anda.
                    </p>
                </div>

                <Link
                    :href="route('budgets.index')"
                    class="rounded-lg border border-slate-700 px-3 py-1.5 text-xs text-slate-300 hover:bg-slate-800 transition-colors"
                >
                    Kembali
                </Link>
            </div>
        </template>

        <div class="p-4 sm:p-6 pb-40">
            <div class="mx-auto max-w-xl rounded-xl border border-slate-800 bg-slate-900 p-6 shadow-sm">
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Nama Anggaran -->
                    <div>
                        <label for="name" class="mb-1 block text-xs font-medium text-slate-300">
                            Nama Anggaran
                        </label>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            autofocus
                            placeholder="Contoh: Budget Bulanan Makan, Belanja Mingguan"
                            class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            :class="{ 'border-rose-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-rose-400">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Kategori (Searchable Dropdown) -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-300">
                            Pilih Kategori
                        </label>
                        <SearchableCategorySelect
                            v-model="form.category_id"
                            :categories="categories"
                            placeholder="Cari & pilih kategori..."
                            :error="form.errors.category_id"
                        />
                        <p v-if="form.errors.category_id" class="mt-1 text-xs text-rose-400">
                            {{ form.errors.category_id }}
                        </p>
                    </div>

                    <!-- Batas Nominal (Minimal & Maksimal) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Batas Minimal -->
                        <div>
                            <label for="minimal" class="mb-1 block text-xs font-medium text-slate-300">
                                Batas Minimal (Rp)
                            </label>
                            <input
                                id="minimal"
                                type="number"
                                min="0"
                                step="any"
                                v-model="form.minimal"
                                placeholder="0"
                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                :class="{ 'border-rose-500': form.errors.minimal }"
                            />
                            <p v-if="form.errors.minimal" class="mt-1 text-xs text-rose-400">
                                {{ form.errors.minimal }}
                            </p>
                        </div>

                        <!-- Batas Maksimal -->
                        <div>
                            <label for="maximal" class="mb-1 block text-xs font-medium text-slate-300">
                                Batas Maksimal (Rp)
                            </label>
                            <input
                                id="maximal"
                                type="number"
                                min="0"
                                step="any"
                                required
                                v-model="form.maximal"
                                placeholder="Contoh: 1500000"
                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                :class="{ 'border-rose-500': form.errors.maximal }"
                            />
                            <p v-if="form.errors.maximal" class="mt-1 text-xs text-rose-400">
                                {{ form.errors.maximal }}
                            </p>
                        </div>
                    </div>

                    <!-- Rentang Tanggal (Tanggal Mulai - Tanggal Selesai) -->
                    <div>
                        <label for="date-range" class="mb-1 block text-xs font-medium text-slate-300">
                            Rentang Tanggal
                        </label>
                        <DateRangePicker
                            id="date-range"
                            v-model:startDate="form.start_date"
                            v-model:endDate="form.end_date"
                            input-class="w-full px-3 py-2 text-sm"
                            placeholder="Pilih rentang tanggal anggaran..."
                            :error="form.errors.start_date || form.errors.end_date"
                        />
                        <p v-if="form.errors.start_date" class="mt-1 text-xs text-rose-400">
                            {{ form.errors.start_date }}
                        </p>
                        <p v-if="form.errors.end_date" class="mt-1 text-xs text-rose-400">
                            {{ form.errors.end_date }}
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
                                Jadikan Anggaran Prioritas
                            </span>
                        </label>
                        <p v-if="form.errors.priority" class="mt-1 text-xs text-rose-400">
                            {{ form.errors.priority }}
                        </p>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                        <Link
                            :href="route('budgets.index')"
                            class="rounded-lg border border-slate-700 px-4 py-2 text-xs font-medium text-slate-300 hover:bg-slate-800 transition-colors"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-medium text-white hover:bg-emerald-500 disabled:opacity-50 transition-colors"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Perbarui Anggaran' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

