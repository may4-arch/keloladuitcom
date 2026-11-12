<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    modelValue: {
        type: [Number, String],
        default: null,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: 'Pilih kategori...',
    },
    error: {
        type: String,
        default: '',
    },
    inputClass: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const dropdownRef = ref(null);
const searchInputRef = ref(null);

const selectedCategory = computed(() => {
    if (!props.modelValue) return null;
    return props.categories.find(
        (c) => String(c.category_id) === String(props.modelValue)
    );
});

const filteredCategories = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.categories;
    }
    const q = searchQuery.value.toLowerCase().trim();
    return props.categories.filter((c) =>
        c.name.toLowerCase().includes(q)
    );
});

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        searchQuery.value = '';
        nextTick(() => {
            searchInputRef.value?.focus();
        });
    }
};

const selectCategory = (category) => {
    emit('update:modelValue', category.category_id);
    isOpen.value = false;
    searchQuery.value = '';
};

const clearSelection = (e) => {
    e.stopPropagation();
    emit('update:modelValue', null);
};

const handleClickOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <!-- Trigger Button -->
        <button
            type="button"
            @click="toggleDropdown"
            class="w-full flex items-center justify-between rounded-lg border bg-slate-950 text-left transition-colors focus:outline-none focus:ring-1 focus:ring-emerald-500"
            :class="[
                inputClass ? inputClass : 'px-3 py-2 text-sm',
                error
                    ? 'border-rose-500 text-rose-300'
                    : isOpen
                    ? 'border-emerald-500 ring-1 ring-emerald-500'
                    : 'border-slate-700 hover:border-slate-600 text-slate-100',
            ]"
        >
            <div class="flex items-center gap-2 truncate">
                <template v-if="selectedCategory">
                    <span
                        :class="[
                            selectedCategory.type === 1
                                ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25'
                                : 'bg-rose-500/15 text-rose-400 border border-rose-500/25',
                            'inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium shrink-0'
                        ]"
                    >
                        {{ selectedCategory.type === 1 ? 'Pemasukan' : 'Pengeluaran' }}
                    </span>
                    <span class="truncate font-medium text-slate-200">
                        {{ selectedCategory.name }}
                    </span>
                </template>
                <span v-else class="text-slate-500 text-xs">
                    {{ placeholder }}
                </span>
            </div>

            <div class="flex items-center gap-1.5 shrink-0 text-slate-400">
                <button
                    v-if="selectedCategory"
                    type="button"
                    @click="clearSelection"
                    class="p-0.5 hover:text-rose-400 transition-colors"
                    title="Hapus pilihan"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <svg
                    class="h-4 w-4 transition-transform duration-200"
                    :class="{ 'rotate-180': isOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </button>

        <!-- Dropdown Menu -->
        <div
            v-if="isOpen"
            class="absolute left-0 right-0 z-50 mt-1.5 rounded-xl border border-slate-800 bg-slate-900 p-2 shadow-2xl backdrop-blur-sm animate-in fade-in zoom-in-95 duration-100"
        >
            <!-- Search Input Inside Dropdown -->
            <div class="relative mb-2">
                <svg
                    class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    ref="searchInputRef"
                    type="text"
                    v-model="searchQuery"
                    placeholder="Cari kategori..."
                    class="w-full rounded-lg border border-slate-700 bg-slate-950 pl-8 pr-3 py-1.5 text-xs text-slate-100 placeholder-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                />
            </div>

            <!-- Categories Options List -->
            <div class="max-h-56 overflow-y-auto space-y-1 pr-1 custom-scrollbar">
                <!-- Clear Option when filtered -->
                <button
                    v-if="modelValue"
                    type="button"
                    @click="clearSelection"
                    class="w-full flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs text-left text-slate-400 hover:bg-slate-800 hover:text-white transition-colors border-b border-slate-800/80 mb-1"
                >
                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Semua Kategori (Hapus Filter)</span>
                </button>

                <button
                    v-for="category in filteredCategories"
                    :key="category.category_id"
                    type="button"
                    @click="selectCategory(category)"
                    class="w-full flex items-center justify-between rounded-lg px-2.5 py-2 text-xs text-left transition-colors"
                    :class="[
                        String(category.category_id) === String(modelValue)
                            ? 'bg-emerald-500/15 text-emerald-300 font-medium'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                    ]"
                >
                    <div class="flex items-center gap-2 truncate">
                        <span
                            :class="[
                                category.type === 1
                                    ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                                    : 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
                                'inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium shrink-0'
                            ]"
                        >
                            {{ category.type === 1 ? 'Pemasukan' : 'Pengeluaran' }}
                        </span>
                        <span class="truncate">{{ category.name }}</span>
                    </div>

                    <svg
                        v-if="String(category.category_id) === String(modelValue)"
                        class="h-4 w-4 text-emerald-400 shrink-0 ml-2"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </button>

                <!-- Empty State within Dropdown -->
                <div
                    v-if="filteredCategories.length === 0"
                    class="py-4 text-center text-xs text-slate-500"
                >
                    Kategori tidak ditemukan.
                </div>
            </div>
        </div>
    </div>
</template>

