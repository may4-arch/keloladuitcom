<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});

// Default date to today (YYYY-MM-DD)
const today = new Date().toISOString().split('T')[0];

const form = useForm({
    category_id: props.categories.length > 0 ? props.categories[0].category_id : '',
    date: today,
    notes: '',
    details: [
        { item_name: '', quantity: 1, price: 0 },
    ],
});

// Add item row
const addItem = () => {
    form.details.push({
        item_name: '',
        quantity: 1,
        price: 0,
    });
};

// Remove item row
const removeItem = (index) => {
    if (form.details.length > 1) {
        form.details.splice(index, 1);
    } else {
        // Reset the single remaining row
        form.details[0] = { item_name: '', quantity: 1, price: 0 };
    }
};

// Auto calculate grand total
const grandTotal = computed(() => {
    return form.details.reduce((sum, item) => {
        const qty = Number(item.quantity) || 0;
        const prc = Number(item.price) || 0;
        return sum + (qty * prc);
    }, 0);
});

// Format currency
const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const submit = () => {
    form.post(route('transactions.store'));
};
</script>

<template>
    <Head title="Tambah Transaksi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-base font-semibold text-white">
                        Tambah Transaksi
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Catat transaksi baru beserta rincian itemnya.
                    </p>
                </div>

                <Link
                    :href="route('transactions.index')"
                    class="rounded-lg border border-slate-700 px-3 py-1.5 text-xs text-slate-300 hover:bg-slate-800 transition-colors"
                >
                    Kembali
                </Link>
            </div>
        </template>

        <div class="p-4 sm:p-6">
            <div class="mx-auto max-w-2xl rounded-xl border border-slate-800 bg-slate-900 p-6 shadow-sm">
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Section 1: Header Transaksi -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Kategori -->
                        <div>
                            <label for="category_id" class="mb-1 block text-xs font-medium text-slate-300">
                                Kategori
                            </label>
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                required
                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                :class="{ 'border-rose-500': form.errors.category_id }"
                            >
                                <option value="" disabled>Pilih Kategori</option>
                                <option
                                    v-for="cat in categories"
                                    :key="cat.category_id"
                                    :value="cat.category_id"
                                >
                                    {{ cat.name }} ({{ cat.type === 1 ? 'Pemasukan' : 'Pengeluaran' }})
                                </option>
                            </select>
                            <p v-if="form.errors.category_id" class="mt-1 text-xs text-rose-400">
                                {{ form.errors.category_id }}
                            </p>
                        </div>

                        <!-- Tanggal -->
                        <div>
                            <label for="date" class="mb-1 block text-xs font-medium text-slate-300">
                                Tanggal
                            </label>
                            <input
                                id="date"
                                type="date"
                                v-model="form.date"
                                required
                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                :class="{ 'border-rose-500': form.errors.date }"
                            />
                            <p v-if="form.errors.date" class="mt-1 text-xs text-rose-400">
                                {{ form.errors.date }}
                            </p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="mb-1 block text-xs font-medium text-slate-300">
                            Catatan (Notes)
                        </label>
                        <input
                            id="notes"
                            type="text"
                            v-model="form.notes"
                            placeholder="Contoh: Belanja mingguan di supermarket"
                            class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            :class="{ 'border-rose-500': form.errors.notes }"
                        />
                        <p v-if="form.errors.notes" class="mt-1 text-xs text-rose-400">
                            {{ form.errors.notes }}
                        </p>
                    </div>

                    <!-- Section 2: Rincian Detail Items -->
                    <div class="border-t border-slate-800 pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-300">
                                    Detail Item Transaksi
                                </h3>
                                <p class="text-[11px] text-slate-500">
                                    Rincian barang atau pos transaksi yang dibeli/diterima.
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="addItem"
                                class="inline-flex items-center gap-1 rounded-lg border border-slate-700 bg-slate-800 px-2.5 py-1 text-xs font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition-colors"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Tambah Item</span>
                            </button>
                        </div>

                        <!-- Header on Desktop -->
                        <div class="hidden sm:grid sm:grid-cols-12 gap-3 px-3 py-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400 border-b border-slate-800 bg-slate-950/60 rounded-t-lg">
                            <div class="col-span-5">Nama Item</div>
                            <div class="col-span-2 text-center">Qty</div>
                            <div class="col-span-3 text-right">Harga Satuan (Rp)</div>
                            <div class="col-span-2 text-right">Subtotal</div>
                        </div>

                        <!-- Items Table/List -->
                        <div class="space-y-2.5">
                            <div
                                v-for="(item, index) in form.details"
                                :key="index"
                                class="grid grid-cols-12 gap-3 items-center rounded-lg border border-slate-800/80 bg-slate-950/50 p-3 sm:py-2.5 transition-colors hover:border-slate-700"
                            >
                                <!-- Nama Item -->
                                <div class="col-span-12 sm:col-span-5">
                                    <label class="block sm:hidden text-[11px] font-medium text-slate-400 mb-1">Nama Item</label>
                                    <input
                                        type="text"
                                        v-model="item.item_name"
                                        placeholder="Contoh: Beras 5kg, Kopi, Bayar Wifi"
                                        class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                    />
                                </div>

                                <!-- Qty -->
                                <div class="col-span-4 sm:col-span-2">
                                    <label class="block sm:hidden text-[11px] font-medium text-slate-400 mb-1">Qty</label>
                                    <input
                                        type="number"
                                        min="1"
                                        v-model.number="item.quantity"
                                        placeholder="1"
                                        class="w-full text-center rounded-lg border border-slate-700 bg-slate-900 px-2 py-2 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                    />
                                </div>

                                <!-- Harga Satuan -->
                                <div class="col-span-8 sm:col-span-3">
                                    <label class="block sm:hidden text-[11px] font-medium text-slate-400 mb-1">Harga Satuan</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="any"
                                        v-model.number="item.price"
                                        placeholder="0"
                                        class="w-full text-right rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                    />
                                </div>

                                <!-- Subtotal & Delete -->
                                <div class="col-span-12 sm:col-span-2 flex items-center justify-between sm:justify-end gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-800/80">
                                    <span class="text-xs font-semibold text-emerald-400 truncate">
                                        {{ formatRupiah((item.quantity || 0) * (item.price || 0)) }}
                                    </span>
                                    <button
                                        type="button"
                                        @click="removeItem(index)"
                                        class="p-1.5 text-slate-500 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition-colors shrink-0"
                                        title="Hapus baris item"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Grand Total Bar -->
                        <div class="mt-3 flex items-center justify-between rounded-lg bg-slate-950 px-3.5 py-2.5 border border-slate-800">
                            <span class="text-xs font-medium text-slate-400">Total Keseluruhan</span>
                            <span class="text-sm font-bold text-emerald-400">
                                {{ formatRupiah(grandTotal) }}
                            </span>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                        <Link
                            :href="route('transactions.index')"
                            class="rounded-lg border border-slate-700 px-4 py-2 text-xs font-medium text-slate-300 hover:bg-slate-800 transition-colors"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-medium text-white hover:bg-emerald-500 disabled:opacity-50 transition-colors"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Transaksi' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

