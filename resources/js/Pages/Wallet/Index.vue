<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

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
});

// Format currency helper
const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};
</script>

<template>
    <Head title="Wallet" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-base font-semibold text-white">Dompet & Saldo</h1>
                    <p class="text-xs text-slate-400">Ringkasan saldo terkini dan riwayat saldo dompet Anda</p>
                </div>
            </div>
        </template>

        <div class="p-6">
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
                                <span class="text-slate-400 font-medium">Previous Balance (Saldo Sebelumnya):</span>
                                <span class="font-mono font-semibold text-amber-300">
                                    {{ formatRupiah(moneyBefore) }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="flex items-end justify-between pt-3 border-t border-slate-800/80">
                            <div>
                                <span class="text-[10px] font-medium uppercase tracking-widest text-slate-400">Pengelola</span>
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
        </div>
    </AuthenticatedLayout>
</template>
