<script setup>
import { computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    currency: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    code: props.currency.code || '',
    name: props.currency.name || '',
    symbol: props.currency.symbol || '',
    symbol_position: props.currency.symbol_position || 'prefix',
    decimal_places: props.currency.decimal_places ?? 2,
    thousand_separator: props.currency.thousand_separator || '.',
    decimal_separator: props.currency.decimal_separator || ',',
    exchange_rate: Number(props.currency.exchange_rate ?? 1),
    is_base: Boolean(props.currency.is_base),
    is_active: props.currency.is_active !== undefined ? Boolean(props.currency.is_active) : true,
});

watch(
    () => form.is_base,
    (isBase) => {
        if (isBase) {
            form.exchange_rate = 1;
        }
    }
);

const previewFormatted = computed(() => {
    const decimals = Math.max(0, Math.min(6, Number(form.decimal_places) || 0));
    const thouSep = form.thousand_separator || '.';
    const decSep = form.decimal_separator || ',';
    const symbol = form.symbol || '';

    const fixed = Number(1250000).toFixed(decimals);
    const [intPart, decPart] = fixed.split('.');
    const formattedInt = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, thouSep);
    const formattedNumber = decPart !== undefined ? `${formattedInt}${decSep}${decPart}` : formattedInt;

    return form.symbol_position === 'suffix'
        ? `${formattedNumber} ${symbol}`.trim()
        : `${symbol}${formattedNumber}`;
});

const submit = () => {
    form.put(route('currencies.update', props.currency.id));
};
</script>

<template>
    <Head title="Edit Mata Uang" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-base font-semibold text-white">
                        Edit Mata Uang
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Perbarui informasi, format penulisan, dan nilai tukar mata uang Anda.
                    </p>
                </div>

                <Link
                    :href="route('currencies.index')"
                    class="rounded-lg border border-slate-700 px-3 py-1.5 text-xs text-slate-300 hover:bg-slate-800 transition-colors"
                >
                    Kembali
                </Link>
            </div>
        </template>

        <div class="p-4 sm:p-6">
            <div class="mx-auto max-w-xl rounded-xl border border-slate-800 bg-slate-900 p-6 shadow-sm">
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Kode & Simbol -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Kode Mata Uang -->
                        <div>
                            <label for="code" class="mb-1 block text-xs font-medium text-slate-300">
                                Kode Mata Uang (ISO 4217)
                            </label>
                            <input
                                id="code"
                                type="text"
                                maxlength="3"
                                v-model="form.code"
                                @input="form.code = form.code.toUpperCase()"
                                required
                                autofocus
                                placeholder="Contoh: IDR, USD, SGD"
                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm uppercase font-mono text-slate-100 placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                :class="{ 'border-rose-500': form.errors.code }"
                            />
                            <p v-if="form.errors.code" class="mt-1 text-xs text-rose-400">
                                {{ form.errors.code }}
                            </p>
                        </div>

                        <!-- Simbol -->
                        <div>
                            <label for="symbol" class="mb-1 block text-xs font-medium text-slate-300">
                                Simbol
                            </label>
                            <input
                                id="symbol"
                                type="text"
                                maxlength="10"
                                v-model="form.symbol"
                                required
                                placeholder="Contoh: Rp, $, S$, €"
                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                :class="{ 'border-rose-500': form.errors.symbol }"
                            />
                            <p v-if="form.errors.symbol" class="mt-1 text-xs text-rose-400">
                                {{ form.errors.symbol }}
                            </p>
                        </div>
                    </div>

                    <!-- Nama Mata Uang -->
                    <div>
                        <label for="name" class="mb-1 block text-xs font-medium text-slate-300">
                            Nama Mata Uang
                        </label>
                        <input
                            id="name"
                            type="text"
                            maxlength="50"
                            v-model="form.name"
                            required
                            placeholder="Contoh: Indonesian Rupiah, US Dollar"
                            class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            :class="{ 'border-rose-500': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-rose-400">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Posisi Simbol & Jumlah Desimal -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Posisi Simbol -->
                        <div>
                            <label for="symbol_position" class="mb-1 block text-xs font-medium text-slate-300">
                                Posisi Simbol
                            </label>
                            <select
                                id="symbol_position"
                                v-model="form.symbol_position"
                                required
                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer"
                                :class="{ 'border-rose-500': form.errors.symbol_position }"
                            >
                                <option value="prefix">Prefix (Di Depan Nominal)</option>
                                <option value="suffix">Suffix (Di Belakang Nominal)</option>
                            </select>
                            <p v-if="form.errors.symbol_position" class="mt-1 text-xs text-rose-400">
                                {{ form.errors.symbol_position }}
                            </p>
                        </div>

                        <!-- Jumlah Desimal -->
                        <div>
                            <label for="decimal_places" class="mb-1 block text-xs font-medium text-slate-300">
                                Jumlah Angka Desimal
                            </label>
                            <input
                                id="decimal_places"
                                type="number"
                                min="0"
                                max="6"
                                step="1"
                                v-model.number="form.decimal_places"
                                required
                                placeholder="0 atau 2"
                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                :class="{ 'border-rose-500': form.errors.decimal_places }"
                            />
                            <p v-if="form.errors.decimal_places" class="mt-1 text-xs text-rose-400">
                                {{ form.errors.decimal_places }}
                            </p>
                        </div>
                    </div>

                    <!-- Pemisah Ribuan & Pemisah Desimal -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Pemisah Ribuan -->
                        <div>
                            <label for="thousand_separator" class="mb-1 block text-xs font-medium text-slate-300">
                                Pemisah Ribuan
                            </label>
                            <select
                                id="thousand_separator"
                                v-model="form.thousand_separator"
                                required
                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer"
                                :class="{ 'border-rose-500': form.errors.thousand_separator }"
                            >
                                <option value=".">Titik (.)</option>
                                <option value=",">Koma (,)</option>
                                <option value="'">Apostrof (')</option>
                            </select>
                            <p v-if="form.errors.thousand_separator" class="mt-1 text-xs text-rose-400">
                                {{ form.errors.thousand_separator }}
                            </p>
                        </div>

                        <!-- Pemisah Desimal -->
                        <div>
                            <label for="decimal_separator" class="mb-1 block text-xs font-medium text-slate-300">
                                Pemisah Desimal
                            </label>
                            <select
                                id="decimal_separator"
                                v-model="form.decimal_separator"
                                required
                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer"
                                :class="{ 'border-rose-500': form.errors.decimal_separator }"
                            >
                                <option value=",">Koma (,)</option>
                                <option value=".">Titik (.)</option>
                            </select>
                            <p v-if="form.errors.decimal_separator" class="mt-1 text-xs text-rose-400">
                                {{ form.errors.decimal_separator }}
                            </p>
                        </div>
                    </div>

                    <!-- Nilai Tukar (Kurs) -->
                    <div>
                        <label for="exchange_rate" class="mb-1 block text-xs font-medium text-slate-300">
                            Nilai Tukar (Kurs terhadap Mata Uang Utama)
                        </label>
                        <input
                            id="exchange_rate"
                            type="number"
                            min="0.000001"
                            step="any"
                            v-model="form.exchange_rate"
                            :readonly="form.is_base"
                            required
                            placeholder="Contoh: 1 atau 15850"
                            class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm font-mono text-slate-100 placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 read-only:opacity-60 read-only:cursor-not-allowed"
                            :class="{ 'border-rose-500': form.errors.exchange_rate }"
                        />
                        <p v-if="form.is_base" class="mt-1 text-[11px] text-amber-400">
                            Nilai tukar otomatis ditetapkan ke 1 untuk Mata Uang Utama.
                        </p>
                        <p v-if="form.errors.exchange_rate" class="mt-1 text-xs text-rose-400">
                            {{ form.errors.exchange_rate }}
                        </p>
                    </div>

                    <!-- Live Format Preview -->
                    <div class="flex items-center justify-between rounded-lg bg-slate-950 px-3.5 py-2.5 border border-slate-800">
                        <span class="text-xs font-medium text-slate-400">Contoh Tampilan Nominal</span>
                        <span class="text-sm font-mono font-bold text-emerald-400">
                            {{ previewFormatted }}
                        </span>
                    </div>

                    <!-- Opsi Checkboxes (Base Currency & Status Aktif) -->
                    <div class="space-y-2.5 pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input
                                id="is_base"
                                type="checkbox"
                                v-model="form.is_base"
                                class="h-4 w-4 rounded border-slate-700 bg-slate-950 text-emerald-600 focus:ring-0 focus:ring-offset-0 transition-colors cursor-pointer"
                            />
                            <span class="text-xs font-medium text-slate-300">
                                Jadikan Mata Uang Utama (Base Currency)
                            </span>
                        </label>
                        <p v-if="form.errors.is_base" class="mt-1 text-xs text-rose-400">
                            {{ form.errors.is_base }}
                        </p>

                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input
                                id="is_active"
                                type="checkbox"
                                v-model="form.is_active"
                                class="h-4 w-4 rounded border-slate-700 bg-slate-950 text-emerald-600 focus:ring-0 focus:ring-offset-0 transition-colors cursor-pointer"
                            />
                            <span class="text-xs font-medium text-slate-300">
                                Aktifkan Mata Uang Ini
                            </span>
                        </label>
                        <p v-if="form.errors.is_active" class="mt-1 text-xs text-rose-400">
                            {{ form.errors.is_active }}
                        </p>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                        <Link
                            :href="route('currencies.index')"
                            class="rounded-lg border border-slate-700 px-4 py-2 text-xs font-medium text-slate-300 hover:bg-slate-800 transition-colors"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-medium text-white hover:bg-emerald-500 disabled:opacity-50 transition-colors"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Perbarui Mata Uang' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
