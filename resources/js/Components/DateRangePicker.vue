<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    startDate: {
        type: String,
        default: '',
    },
    endDate: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Pilih rentang tanggal...',
    },
});

const emit = defineEmits(['update:startDate', 'update:endDate', 'change']);

const isOpen = ref(false);
const containerRef = ref(null);

const monthNames = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

// Navigation months:
// Left month is baseMonth/baseYear, Right month is next month
const today = new Date();
const baseYear = ref(today.getFullYear());
const baseMonth = ref(today.getMonth()); // 0-indexed

// Temporary selection states while picking
const tempStart = ref(props.startDate || '');
const tempEnd = ref(props.endDate || '');
const hoverDate = ref('');

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

// Sync base month when props change
watch(
    () => [props.startDate, props.endDate],
    ([newStart, newEnd]) => {
        tempStart.value = newStart || '';
        tempEnd.value = newEnd || '';
        if (newStart) {
            const parsed = parseYMD(newStart);
            if (parsed && !isNaN(parsed.getTime())) {
                baseYear.value = parsed.getFullYear();
                baseMonth.value = parsed.getMonth();
            }
        }
    },
    { immediate: true }
);

// Right month is always (baseMonth + 1)
const rightMonthInfo = computed(() => {
    let m = baseMonth.value + 1;
    let y = baseYear.value;
    if (m > 11) {
        m = 0;
        y++;
    }
    return { month: m, year: y };
});

const prevMonth = () => {
    if (baseMonth.value === 0) {
        baseMonth.value = 11;
        baseYear.value--;
    } else {
        baseMonth.value--;
    }
};

const nextMonth = () => {
    if (baseMonth.value === 11) {
        baseMonth.value = 0;
        baseYear.value++;
    } else {
        baseMonth.value++;
    }
};

// Helper to format short date for display (e.g. "01 Nov 2026")
const formatDisplayDate = (ymdStr) => {
    if (!ymdStr) return '';
    const parsed = parseYMD(ymdStr);
    if (!parsed || isNaN(parsed.getTime())) return ymdStr;
    const d = String(parsed.getDate()).padStart(2, '0');
    const m = monthNames[parsed.getMonth()].slice(0, 3);
    const y = parsed.getFullYear();
    return `${d} ${m} ${y}`;
};

// Formatted range display text for input trigger
const rangeDisplayText = computed(() => {
    if (props.startDate && props.endDate) {
        if (props.startDate === props.endDate) {
            return formatDisplayDate(props.startDate);
        }
        return `${formatDisplayDate(props.startDate)} - ${formatDisplayDate(props.endDate)}`;
    } else if (props.startDate) {
        return `Dari: ${formatDisplayDate(props.startDate)}`;
    } else if (props.endDate) {
        return `Sampai: ${formatDisplayDate(props.endDate)}`;
    }
    return '';
});

// Build calendar grid for a given year and month
const buildMonthDays = (year, month) => {
    const firstDayIndex = new Date(year, month, 1).getDay(); // 0 = Sunday
    const totalDays = new Date(year, month + 1, 0).getDate();
    const days = [];

    // Empty offset slots
    for (let i = 0; i < firstDayIndex; i++) {
        days.push({ day: null, dateStr: null, dayOfWeek: i });
    }

    // Days in month
    for (let day = 1; day <= totalDays; day++) {
        const dateStr = formatYMD(year, month, day);
        const dayOfWeek = new Date(year, month, day).getDay();
        days.push({
            day,
            dateStr,
            dayOfWeek,
        });
    }

    return days;
};

const leftMonthDays = computed(() => buildMonthDays(baseYear.value, baseMonth.value));
const rightMonthDays = computed(() => buildMonthDays(rightMonthInfo.value.year, rightMonthInfo.value.month));

// Calculate active range (either confirmed range, or tempStart to hover preview)
const activeRange = computed(() => {
    const s = tempStart.value;
    const e = tempEnd.value || (tempStart.value && hoverDate.value && hoverDate.value > tempStart.value ? hoverDate.value : '');

    return {
        start: s,
        end: e,
    };
});

const isDayStart = (dateStr) => {
    return dateStr && activeRange.value.start === dateStr;
};

const isDayEnd = (dateStr) => {
    return dateStr && activeRange.value.end === dateStr;
};

const isDayBetween = (dateStr) => {
    if (!dateStr || !activeRange.value.start || !activeRange.value.end) return false;
    return dateStr > activeRange.value.start && dateStr < activeRange.value.end;
};

// Date selection logic
const handleDateClick = (dateStr) => {
    if (!dateStr) return;

    if (!tempStart.value || (tempStart.value && tempEnd.value)) {
        // First click: reset and pick start
        tempStart.value = dateStr;
        tempEnd.value = '';
    } else if (tempStart.value && !tempEnd.value) {
        // Second click:
        if (dateStr < tempStart.value) {
            tempStart.value = dateStr;
        } else {
            tempEnd.value = dateStr;
            // Confirm selection
            emit('update:startDate', tempStart.value);
            emit('update:endDate', tempEnd.value);
            emit('change', { startDate: tempStart.value, endDate: tempEnd.value });
            isOpen.value = false;
        }
    }
};

const handleDateHover = (dateStr) => {
    if (tempStart.value && !tempEnd.value) {
        hoverDate.value = dateStr;
    }
};

// Presets
const applyPreset = (preset) => {
    const y = today.getFullYear();
    const m = String(today.getMonth() + 1).padStart(2, '0');
    const d = String(today.getDate()).padStart(2, '0');

    let s = '';
    let e = '';

    if (preset === 'today') {
        const todayStr = `${y}-${m}-${d}`;
        s = todayStr;
        e = todayStr;
    } else if (preset === 'this_month') {
        s = `${y}-${m}-01`;
        const lastDay = new Date(y, today.getMonth() + 1, 0).getDate();
        e = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;
    } else if (preset === 'last_month') {
        const lastMonthDate = new Date(y, today.getMonth() - 1, 1);
        const lmY = lastMonthDate.getFullYear();
        const lmM = String(lastMonthDate.getMonth() + 1).padStart(2, '0');
        const lmLDay = new Date(lmY, lastMonthDate.getMonth() + 1, 0).getDate();
        s = `${lmY}-${lmM}-01`;
        e = `${lmY}-${lmM}-${String(lmLDay).padStart(2, '0')}`;
    } else if (preset === 'all') {
        s = '';
        e = '';
    }

    tempStart.value = s;
    tempEnd.value = e;
    emit('update:startDate', s);
    emit('update:endDate', e);
    emit('change', { startDate: s, endDate: e });
    isOpen.value = false;
};

const clearRange = (event) => {
    event.stopPropagation();
    tempStart.value = '';
    tempEnd.value = '';
    emit('update:startDate', '');
    emit('update:endDate', '');
    emit('change', { startDate: '', endDate: '' });
};

const handleClickOutside = (e) => {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        isOpen.value = false;
        // Revert unconfirmed temp values
        tempStart.value = props.startDate || '';
        tempEnd.value = props.endDate || '';
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
        <!-- Trigger Button -->
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="flex items-center justify-between rounded-lg border bg-slate-950 px-3 py-1.5 text-xs text-left transition-colors focus:outline-none focus:ring-1 focus:ring-emerald-500 min-w-[220px]"
            :class="[
                isOpen
                    ? 'border-emerald-500 ring-1 ring-emerald-500'
                    : 'border-slate-700 hover:border-slate-600 text-slate-100',
            ]"
        >
            <div class="flex items-center gap-2 truncate">
                <svg class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span v-if="rangeDisplayText" class="font-medium text-slate-200">
                    {{ rangeDisplayText }}
                </span>
                <span v-else class="text-slate-500">
                    {{ placeholder }}
                </span>
            </div>

            <div class="flex items-center gap-1 shrink-0 text-slate-400 ml-2">
                <button
                    v-if="startDate || endDate"
                    type="button"
                    @click="clearRange"
                    class="p-0.5 hover:text-rose-400 transition-colors"
                    title="Hapus rentang tanggal"
                >
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <svg
                    class="h-3.5 w-3.5 transition-transform duration-200"
                    :class="{ 'rotate-180': isOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </button>

        <!-- Dual Month Date Range Popover -->
        <div
            v-if="isOpen"
            class="absolute left-0 z-50 mt-1.5 rounded-2xl border border-slate-800 bg-slate-900 p-4 sm:p-5 shadow-2xl backdrop-blur-md animate-in fade-in zoom-in-95 duration-100 select-none max-w-[95vw] sm:max-w-none"
        >
            <!-- Preset Quick Buttons -->
            <div class="flex items-center gap-1.5 pb-4 mb-3 border-b border-slate-800/80 overflow-x-auto text-xs">
                <span class="text-[11px] font-medium text-slate-400 mr-1 shrink-0">Preset:</span>
                <button
                    type="button"
                    @click="applyPreset('today')"
                    class="rounded-md border border-slate-700 bg-slate-950 px-2.5 py-1 text-[11px] text-slate-300 hover:border-emerald-500 hover:text-white transition-colors shrink-0"
                >
                    Hari Ini
                </button>
                <button
                    type="button"
                    @click="applyPreset('this_month')"
                    class="rounded-md border border-slate-700 bg-slate-950 px-2.5 py-1 text-[11px] text-slate-300 hover:border-emerald-500 hover:text-white transition-colors shrink-0"
                >
                    Bulan Ini
                </button>
                <button
                    type="button"
                    @click="applyPreset('last_month')"
                    class="rounded-md border border-slate-700 bg-slate-950 px-2.5 py-1 text-[11px] text-slate-300 hover:border-emerald-500 hover:text-white transition-colors shrink-0"
                >
                    Bulan Lalu
                </button>
                <button
                    type="button"
                    @click="applyPreset('all')"
                    class="rounded-md border border-slate-700 bg-slate-950 px-2.5 py-1 text-[11px] text-slate-300 hover:border-emerald-500 hover:text-white transition-colors shrink-0"
                >
                    Semua
                </button>
            </div>

            <!-- Calendars Container: Month 1 & Month 2 -->
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Left Month -->
                <div class="w-64">
                    <!-- Header with < button and Month Year -->
                    <div class="flex items-center justify-between pb-3">
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
                            {{ monthNames[baseMonth] }} {{ baseYear }}
                        </div>

                        <!-- Invisible placeholder on left to balance spacing -->
                        <div class="w-7"></div>
                    </div>

                    <!-- Weekdays -->
                    <div class="grid grid-cols-7 text-center text-[11px] font-medium text-slate-400 py-1 mb-1">
                        <div v-for="d in dayNames" :key="d" class="py-1">
                            {{ d }}
                        </div>
                    </div>

                    <!-- Days Grid -->
                    <div class="grid grid-cols-7 gap-y-1 text-center text-xs">
                        <div
                            v-for="(item, idx) in leftMonthDays"
                            :key="idx"
                            class="h-8 relative flex items-center justify-center"
                            @mouseenter="handleDateHover(item.dateStr)"
                        >
                            <!-- Continuous connecting range ribbon background -->
                            <div
                                v-if="isDayBetween(item.dateStr)"
                                class="absolute inset-0 bg-emerald-500/20"
                                :class="{
                                    'rounded-l-full': item.dayOfWeek === 0,
                                    'rounded-r-full': item.dayOfWeek === 6,
                                }"
                            ></div>
                            <div
                                v-else-if="isDayStart(item.dateStr) && activeRange.end && activeRange.start !== activeRange.end"
                                class="absolute right-0 top-0 bottom-0 w-1/2 bg-emerald-500/20"
                            ></div>
                            <div
                                v-else-if="isDayEnd(item.dateStr) && activeRange.start && activeRange.start !== activeRange.end"
                                class="absolute left-0 top-0 bottom-0 w-1/2 bg-emerald-500/20"
                            ></div>

                            <!-- Day Circle Button -->
                            <button
                                v-if="item.day"
                                type="button"
                                @click="handleDateClick(item.dateStr)"
                                class="h-8 w-8 rounded-full flex items-center justify-center transition-all duration-150 relative z-10 text-xs font-medium"
                                :class="[
                                    isDayStart(item.dateStr) || isDayEnd(item.dateStr)
                                        ? 'bg-emerald-500 text-white font-bold shadow-md shadow-emerald-500/30'
                                        : isDayBetween(item.dateStr)
                                        ? 'text-emerald-200 font-semibold hover:text-white'
                                        : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                                ]"
                            >
                                {{ item.day }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Month -->
                <div class="w-64 border-t md:border-t-0 md:border-l border-slate-800/80 pt-4 md:pt-0 md:pl-6">
                    <!-- Header with Month Year and > button -->
                    <div class="flex items-center justify-between pb-3">
                        <!-- Invisible placeholder on right to balance spacing -->
                        <div class="w-7"></div>

                        <div class="text-xs font-semibold text-white tracking-wide">
                            {{ monthNames[rightMonthInfo.month] }} {{ rightMonthInfo.year }}
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

                    <!-- Weekdays -->
                    <div class="grid grid-cols-7 text-center text-[11px] font-medium text-slate-400 py-1 mb-1">
                        <div v-for="d in dayNames" :key="d" class="py-1">
                            {{ d }}
                        </div>
                    </div>

                    <!-- Days Grid -->
                    <div class="grid grid-cols-7 gap-y-1 text-center text-xs">
                        <div
                            v-for="(item, idx) in rightMonthDays"
                            :key="idx"
                            class="h-8 relative flex items-center justify-center"
                            @mouseenter="handleDateHover(item.dateStr)"
                        >
                            <!-- Continuous connecting range ribbon background -->
                            <div
                                v-if="isDayBetween(item.dateStr)"
                                class="absolute inset-0 bg-emerald-500/20"
                                :class="{
                                    'rounded-l-full': item.dayOfWeek === 0,
                                    'rounded-r-full': item.dayOfWeek === 6,
                                }"
                            ></div>
                            <div
                                v-else-if="isDayStart(item.dateStr) && activeRange.end && activeRange.start !== activeRange.end"
                                class="absolute right-0 top-0 bottom-0 w-1/2 bg-emerald-500/20"
                            ></div>
                            <div
                                v-else-if="isDayEnd(item.dateStr) && activeRange.start && activeRange.start !== activeRange.end"
                                class="absolute left-0 top-0 bottom-0 w-1/2 bg-emerald-500/20"
                            ></div>

                            <!-- Day Circle Button -->
                            <button
                                v-if="item.day"
                                type="button"
                                @click="handleDateClick(item.dateStr)"
                                class="h-8 w-8 rounded-full flex items-center justify-center transition-all duration-150 relative z-10 text-xs font-medium"
                                :class="[
                                    isDayStart(item.dateStr) || isDayEnd(item.dateStr)
                                        ? 'bg-emerald-500 text-white font-bold shadow-md shadow-emerald-500/30'
                                        : isDayBetween(item.dateStr)
                                        ? 'text-emerald-200 font-semibold hover:text-white'
                                        : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                                ]"
                            >
                                {{ item.day }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer: helper instruction and close -->
            <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs">
                <span class="text-[11px] text-slate-400">
                    <template v-if="!tempStart">
                        Klik tanggal pertama untuk mulai.
                    </template>
                    <template v-else-if="tempStart && !tempEnd">
                        Pilih tanggal akhir rentang.
                    </template>
                    <template v-else>
                        Rentang terpilih: {{ formatDisplayDate(tempStart) }} - {{ formatDisplayDate(tempEnd) }}
                    </template>
                </span>

                <button
                    type="button"
                    @click="isOpen = false"
                    class="rounded-lg bg-slate-800 px-3 py-1 text-xs text-slate-300 hover:text-white hover:bg-slate-700 transition-colors"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>
