<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    criterias: {
        type: Array,
        required: true,
    },
    totalWeight: {
        type: Number,
        required: true,
    },
    period: {
        type: String,
        default: 'Periode Genap 2025/2026',
    },
});
</script>

<template>
    <div class="flex flex-col rounded-xl border border-slate-200/80 bg-white shadow-2xs p-4 sm:p-5">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                    <span class="material-symbols-outlined text-[20px]">pie_chart</span>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 leading-tight">Distribusi Bobot Kriteria</h2>
                    <p class="text-[11px] text-slate-500">{{ criterias.length }} Kriteria • Karakteristik Benefit vs Cost</p>
                </div>
            </div>
            <span
                :class="totalWeight === 1 ? 'bg-[#00236f] text-white' : 'bg-amber-100 text-amber-800'"
                class="rounded px-2 py-0.5 font-mono text-[10px] font-bold"
            >
                Total {{ Number(totalWeight).toFixed(2) }}
            </span>
        </div>

        <!-- Criteria List -->
        <div class="mt-4 space-y-4 flex-1">
            <div v-for="item in criterias" :key="item.code" class="space-y-1.5">
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-bold text-slate-800">{{ item.code }}</span>
                        <span class="font-medium text-slate-700 truncate max-w-42.5 sm:max-w-52.5">{{ item.name }}</span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span
                            :class="item.type === 'benefit' ? 'bg-teal-50 text-teal-700 border-teal-200/60' : 'bg-rose-50 text-rose-700 border-rose-200/60'"
                            class="rounded border px-1.5 py-0.2 text-[9px] font-bold uppercase tracking-wider"
                        >
                            {{ item.type }}
                        </span>
                        <span class="font-mono text-[11px] font-semibold text-slate-800">
                            {{ item.percentage }}% ({{ Number(item.weight).toFixed(2) }})
                        </span>
                    </div>
                </div>
                <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                    <div
                        :class="item.type === 'benefit' ? 'bg-blue-600' : 'bg-rose-500'"
                        class="h-full rounded-full transition-all duration-500"
                        :style="{ width: `${item.percentage}%` }"
                    ></div>
                </div>
            </div>

            <div v-if="criterias.length === 0" class="py-6 text-center text-xs text-slate-400">
                Belum ada kriteria yang ditetapkan.
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
            <span class="flex items-center gap-1 truncate">
                <span class="material-symbols-outlined text-[15px] text-blue-600">info</span>
                <span>Alokasi {{ period }}</span>
            </span>
            <Link href="/criteria" class="font-semibold text-blue-700 hover:text-blue-900 transition-colors">
                Ubah Bobot
            </Link>
        </div>
    </div>
</template>
