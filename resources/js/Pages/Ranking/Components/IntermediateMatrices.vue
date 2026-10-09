<script setup>
import { ref } from 'vue';

defineProps({
    criterias: {
        type: Array,
        required: true,
    },
    applicants: {
        type: Array,
        required: true,
    },
    intermediates: {
        type: Object,
        required: true,
    },
});

const activeStep = ref('normalized'); // 'normalized' atau 'weighted'
</script>

<template>
    <div class="space-y-4">
        <!-- Sub-switch for matrix type -->
        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
            <div class="flex space-x-2">
                <button
                    @click="activeStep = 'normalized'"
                    :class="activeStep === 'normalized' ? 'bg-slate-900 text-white font-medium' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="rounded px-3 py-1.5 text-xs transition"
                >
                    1. Matriks Ternormalisasi (R)
                </button>
                <button
                    @click="activeStep = 'weighted'"
                    :class="activeStep === 'weighted' ? 'bg-slate-900 text-white font-medium' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="rounded px-3 py-1.5 text-xs transition"
                >
                    2. Matriks Terbobot (Y)
                </button>
            </div>
            <span class="text-[11px] text-slate-500 font-mono">
                {{ activeStep === 'normalized' ? 'Rumus: r_ij = x_ij / √(Σ x_kj²)' : 'Rumus: y_ij = w_j × r_ij' }}
            </span>
        </div>

        <!-- Matriks Normalisasi R -->
        <div v-if="activeStep === 'normalized'" class="overflow-x-auto rounded border border-slate-200 bg-white shadow-2xs">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-[11px] font-semibold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="w-12 px-3 py-2.5">No</th>
                        <th class="w-28 px-3 py-2.5">NIM</th>
                        <th class="px-3 py-2.5">Nama Alternatif</th>
                        <th
                            v-for="c in criterias"
                            :key="c.id"
                            class="px-3 py-2.5 text-right font-mono"
                        >
                            {{ c.code }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-normal text-slate-800">
                    <tr
                        v-for="(a, idx) in applicants"
                        :key="a.applicant_id || a.id"
                        class="hover:bg-slate-50/80 transition-colors"
                    >
                        <td class="px-3 py-2 text-slate-400 font-mono">{{ idx + 1 }}</td>
                        <td class="px-3 py-2 font-mono font-bold text-slate-900">{{ a.nim }}</td>
                        <td class="px-3 py-2 font-medium text-slate-900">{{ a.name }}</td>
                        <td
                            v-for="c in criterias"
                            :key="c.id"
                            class="px-3 py-2 text-right font-mono text-slate-700"
                        >
                            {{ intermediates.normal_matrix[a.applicant_id || a.id]?.[c.id]?.toFixed(4) ?? '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Matriks Terbobot Y -->
        <div v-if="activeStep === 'weighted'" class="overflow-x-auto rounded border border-slate-200 bg-white shadow-2xs">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-[11px] font-semibold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="w-12 px-3 py-2.5">No</th>
                        <th class="w-28 px-3 py-2.5">NIM</th>
                        <th class="px-3 py-2.5">Nama Alternatif</th>
                        <th
                            v-for="c in criterias"
                            :key="c.id"
                            class="px-3 py-2.5 text-right font-mono"
                        >
                            <div>{{ c.code }}</div>
                            <div class="text-[9px] font-normal text-slate-400">w={{ c.weight }}</div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-normal text-slate-800">
                    <tr
                        v-for="(a, idx) in applicants"
                        :key="a.applicant_id || a.id"
                        class="hover:bg-slate-50/80 transition-colors"
                    >
                        <td class="px-3 py-2 text-slate-400 font-mono">{{ idx + 1 }}</td>
                        <td class="px-3 py-2 font-mono font-bold text-slate-900">{{ a.nim }}</td>
                        <td class="px-3 py-2 font-medium text-slate-900">{{ a.name }}</td>
                        <td
                            v-for="c in criterias"
                            :key="c.id"
                            class="px-3 py-2 text-right font-mono text-slate-800 font-medium"
                        >
                            {{ intermediates.weighted_matrix[a.applicant_id || a.id]?.[c.id]?.toFixed(4) ?? '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
