<script setup>
import { ref, onMounted, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { getNotyf, notifySuccess, notifyError } from '@/notyf';

const showingMobileSidebar = ref(false);
const page = usePage();

const checkFlash = () => {
    const flash = page.props.flash;
    if (flash?.success) {
        notifySuccess(flash.success);
    } else if (flash?.status) {
        notifySuccess(flash.status);
    }
    if (flash?.error) {
        notifyError(flash.error);
    }
};

onMounted(() => {
    getNotyf();
    checkFlash();
});

watch(
    () => page.props.flash,
    () => {
        checkFlash();
    },
    { deep: true }
);

const getUserInitials = (name) => {
    if (!name) return 'U';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <!-- Desktop Sidebar -->
        <aside class="hidden md:fixed md:inset-y-0 md:left-0 md:z-30 md:flex md:w-60 md:flex-col border-r border-slate-800 bg-slate-900">
            <!-- Brand -->
            <div class="flex h-16 shrink-0 items-center px-5 border-b border-slate-800">
                <Link :href="route('wallets.index')" class="flex items-center gap-3">
                    <ApplicationLogo class="h-8 w-8 shrink-0" />
                    <span class="text-base font-bold tracking-tight text-white">
                        Kelola<span class="text-emerald-400">Duit</span>
                    </span>
                </Link>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 overflow-y-auto p-4 space-y-2">
                <!-- Wallet -->
                <Link
                    :href="route('wallets.index')"
                    :class="[
                        route().current('wallets.index')
                            ? 'bg-slate-800 text-white font-medium'
                            : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50',
                        'flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-xs transition-colors'
                    ]"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span>Wallet</span>
                </Link>

                <!-- Category -->
                <Link
                    :href="route('categories.index')"
                    :class="[
                        route().current('categories.index')
                            ? 'bg-slate-800 text-white font-medium'
                            : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50',
                        'flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-xs transition-colors'
                    ]"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span>Category</span>
                </Link>

                <!-- Transaction -->
                <Link
                    :href="route('transactions.index')"
                    :class="[
                        route().current('transactions.index')
                            ? 'bg-slate-800 text-white font-medium'
                            : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50',
                        'flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-xs transition-colors'
                    ]"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>Transaction</span>
                </Link>

                <div class="my-2 border-t border-slate-800/80"></div>

                <!-- Profile -->
                <Link
                    :href="route('profile.edit')"
                    :class="[
                        route().current('profile.edit')
                            ? 'bg-slate-800 text-white font-medium'
                            : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50',
                        'flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-xs transition-colors'
                    ]"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profil</span>
                </Link>
            </div>

            <!-- User Info & Logout -->
            <div class="border-t border-slate-800 p-3">
                <div class="flex items-center justify-between rounded-lg bg-slate-950 p-2 border border-slate-800">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded bg-slate-800 text-xs font-medium text-slate-300">
                            {{ getUserInitials($page.props.auth.user.name) }}
                        </div>
                        <div class="truncate">
                            <p class="truncate text-xs font-medium text-slate-200">
                                {{ $page.props.auth.user.name }}
                            </p>
                        </div>
                    </div>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="shrink-0 p-1 text-slate-400 hover:text-rose-400 transition-colors"
                        title="Keluar"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Mobile Header -->
        <div class="sticky top-0 z-40 flex h-16 items-center justify-between border-b border-slate-800 bg-slate-900 px-4 md:hidden">
            <Link :href="route('wallets.index')" class="flex items-center gap-2.5">
                <ApplicationLogo class="h-8 w-8 shrink-0" />
                <span class="text-base font-bold text-white">
                    Kelola<span class="text-emerald-400">Duit</span>
                </span>
            </Link>

            <button
                @click="showingMobileSidebar = !showingMobileSidebar"
                class="rounded p-1.5 text-slate-400 hover:text-white"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path v-if="!showingMobileSidebar" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Mobile Drawer Backdrop -->
        <div
            v-if="showingMobileSidebar"
            class="fixed inset-0 z-40 bg-slate-950/80 md:hidden"
            @click="showingMobileSidebar = false"
        ></div>

        <!-- Mobile Drawer -->
        <div
            :class="showingMobileSidebar ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-60 bg-slate-900 border-r border-slate-800 p-3 transition-transform duration-200 md:hidden flex flex-col justify-between"
        >
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <Link :href="route('wallets.index')" class="flex items-center gap-2.5">
                        <ApplicationLogo class="h-8 w-8 shrink-0" />
                        <span class="text-base font-bold text-white">
                            Kelola<span class="text-emerald-400">Duit</span>
                        </span>
                    </Link>
                    <button @click="showingMobileSidebar = false" class="text-slate-400 hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 space-y-2">
                    <Link
                        :href="route('wallets.index')"
                        @click="showingMobileSidebar = false"
                        :class="route().current('wallets.index') ? 'bg-slate-800 text-white font-medium' : 'text-slate-400 hover:text-white'"
                        class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-xs transition-colors"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Wallet
                    </Link>

                    <Link
                        :href="route('categories.index')"
                        @click="showingMobileSidebar = false"
                        :class="route().current('categories.index') ? 'bg-slate-800 text-white font-medium' : 'text-slate-400 hover:text-white'"
                        class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-xs transition-colors"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        Category
                    </Link>

                    <Link
                        :href="route('transactions.index')"
                        @click="showingMobileSidebar = false"
                        :class="route().current('transactions.index') ? 'bg-slate-800 text-white font-medium' : 'text-slate-400 hover:text-white'"
                        class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-xs transition-colors"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        Transaction
                    </Link>

                    <div class="my-2 border-t border-slate-800/80"></div>

                    <Link
                        :href="route('profile.edit')"
                        @click="showingMobileSidebar = false"
                        :class="route().current('profile.edit') ? 'bg-slate-800 text-white font-medium' : 'text-slate-400 hover:text-white'"
                        class="flex items-center gap-3 rounded-lg px-3.5 py-2.5 text-xs transition-colors"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Profil
                    </Link>
                </div>
            </div>

            <div class="border-t border-slate-800 pt-3">
                <div class="flex items-center justify-between p-2 rounded bg-slate-950 border border-slate-800">
                    <p class="truncate text-xs text-slate-200">
                        {{ $page.props.auth.user.name }}
                    </p>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-xs text-rose-400 hover:underline"
                    >
                        Keluar
                    </Link>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="md:pl-60 flex flex-col min-h-screen">
            <header v-if="$slots.header" class="flex h-16 shrink-0 items-center border-b border-slate-800 px-6">
                <div class="w-full">
                    <slot name="header" />
                </div>
            </header>

            <main class="flex-1">
                <slot />
            </main>
        </div>
    </div>
</template>
