<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Pilih tanggal...',
    },
    required: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        default: 'single-date-picker',
    },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const containerRef = ref(null);

const monthNames = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

// Active view month & year
const today = new Date();
const currentYear = ref(today.getFullYear());
const currentMonth = ref(today.getMonth()); // 0-indexed

// Helper parse YYYY-MM-DD
const parseYMD = (str) => {
    if (!str) return null;
    const parts = str.split('-');
    if (parts.length !== 3) return null;
    const y = parseInt(parts[0], 10);
    const m = parseInt(parts[1], 10) - 1;
    const d = parseInt(parts[2], 10);
    return new Date(y, m, d);
};

// Helper format YYYY-MM-DD
const formatYMD = (year, month, day) => {
    const y = year;
    const m = String(month + 1).padStart(2, '0');
    const d = String(day).padStart(2, '0');
    return `${y}-${m}-${d}`;
};

// Synchronize initial month & year to modelValue if present
watch(
    () => props.modelValue,
    (val) => {
        if (val) {
            const parsed = parseYMD(val);
            if (parsed && !isNaN(parsed.getTime())) {
                currentYear.value = parsed.getFullYear();
                currentMonth.value = parsed.getMonth();
            }
        }
    },
    { immediate: true }
);

// Formatted display text (e.g. "15 Juli 2026")
const formattedDisplay = computed(() => {
    if (!props.modelValue) return '';
    const parsed = parseYMD(props.modelValue);
    if (!parsed || isNaN(parsed.getTime())) return props.modelValue;
    const d = parsed.getDate();
    const m = monthNames[parsed.getMonth()];
    const y = parsed.getFullYear();
    return `${d} ${m} ${y}`;
});

// Generate calendar days for currentMonth & currentYear
const calendarDays = computed(() => {
    const year = currentYear.value;
    const month = currentMonth.value;

    const firstDayIndex = new Date(year, month, 1).getDay(); // 0 = Sunday
    const totalDays = new Date(year, month + 1, 0).getDate();

    const days = [];

    // Empty offset slots before the 1st
    for (let i = 0; i < firstDayIndex; i++) {
        days.push({ day: null, dateStr: null });
    }

    // Days in current month
    for (let day = 1; day <= totalDays; day++) {
        const dateStr = formatYMD(year, month, day);
        days.push({
            day,
            dateStr,
            isSelected: props.modelValue === dateStr,
            isToday: formatYMD(today.getFullYear(), today.getMonth(), today.getDate()) === dateStr,
        });
    }

    return days;
});

const prevMonth = () => {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
};

const nextMonth = () => {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
};

const selectDate = (dayItem) => {
    if (!dayItem || !dayItem.dateStr) return;
    emit('update:modelValue', dayItem.dateStr);
    isOpen.value = false;
};

const clearDate = (e) => {
    e.stopPropagation();
    emit('update:modelValue', '');
};

const selectToday = () => {
    const todayStr = formatYMD(today.getFullYear(), today.getMonth(), today.getDate());
    currentYear.value = today.getFullYear();
    currentMonth.value = today.getMonth();
    emit('update:modelValue', todayStr);
    isOpen.value = false;
};

const handleClickOutside = (e) => {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
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
    <div ref="containerRef" class="relative">
        <!-- Trigger Input Button -->
        <button
            type="button"
            :id="id"
            @click="isOpen = !isOpen"
            class="w-full flex items-center justify-between rounded-lg border bg-slate-950 px-3 py-2 text-sm text-left transition-colors focus:outline-none focus:ring-1 focus:ring-emerald-500"
            :class="[
                error
                    ? 'border-rose-500 text-rose-300'
                    : isOpen
                    ? 'border-emerald-500 ring-1 ring-emerald-500'
                    : 'border-slate-700 hover:border-slate-600 text-slate-100',
            ]"
        >
            <div class="flex items-center gap-2 truncate">
                <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span v-if="formattedDisplay" class="font-medium text-slate-200">
                    {{ formattedDisplay }}
                </span>
                <span v-else class="text-slate-500 text-xs">
                    {{ placeholder }}
                </span>
            </div>

            <div class="flex items-center gap-1.5 shrink-0 text-slate-400">
                <button
                    v-if="modelValue && !required"
                    type="button"
                    @click="clearDate"
                    class="p-0.5 hover:text-rose-400 transition-colors"
                    title="Hapus tanggal"
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

        <!-- Calendar Dropdown Popover -->
        <div
            v-if="isOpen"
            class="absolute left-0 z-50 mt-1.5 w-72 rounded-2xl border border-slate-800 bg-slate-900 p-4 shadow-2xl backdrop-blur-md animate-in fade-in zoom-in-95 duration-100 select-none"
        >
            <!-- Month Header: < Month Year > -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                <button
                    type="button"
                    @click="prevMonth"
                    class="rounded-lg p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                    title="Bulan sebelumnya"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="text-xs font-semibold text-white tracking-wide">
                    {{ monthNames[currentMonth] }} {{ currentYear }}
                </div>

                <button
                    type="button"
                    @click="nextMonth"
                    class="rounded-lg p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                    title="Bulan berikutnya"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Day Names Row (Su Mo Tu We Th Fr Sa) -->
            <div class="grid grid-cols-7 gap-1 text-center py-2 text-[11px] font-medium text-slate-400">
                <div v-for="d in dayNames" :key="d" class="py-1">
                    {{ d }}
                </div>
            </div>

            <!-- Days Grid -->
            <div class="grid grid-cols-7 gap-1 text-center text-xs">
                <div
                    v-for="(item, idx) in calendarDays"
                    :key="idx"
                    class="h-8 flex items-center justify-center relative"
                >
                    <button
                        v-if="item.day"
                        type="button"
                        @click="selectDate(item)"
                        class="h-8 w-8 rounded-full flex items-center justify-center transition-all duration-150 relative text-xs"
                        :class="[
                            item.isSelected
                                ? 'bg-emerald-500 text-white font-bold shadow-md shadow-emerald-500/30'
                                : item.isToday
                                ? 'text-emerald-400 font-semibold ring-1 ring-emerald-500/60 hover:bg-slate-800'
                                : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                        ]"
                    >
                        {{ item.day }}
                    </button>
                </div>
            </div>

            <!-- Footer: Today quick action -->
            <div class="pt-3 mt-2 border-t border-slate-800/80 flex items-center justify-between text-xs">
                <button
                    type="button"
                    @click="selectToday"
                    class="text-xs font-medium text-emerald-400 hover:text-emerald-300 transition-colors"
                >
                    Hari ini
                </button>
                <button
                    type="button"
                    @click="isOpen = false"
                    class="text-xs text-slate-400 hover:text-slate-200 transition-colors"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>
