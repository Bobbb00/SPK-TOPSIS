<script setup>
defineProps({
    show: Boolean,
    applicant: Object,
    criterias: Array,
    intermediates: Object,
});

const emit = defineEmits(['close']);
</script>

<template>
    <div v-if="show && applicant" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-2xl rounded border border-slate-300 bg-white p-5 shadow-lg">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-900">
                        Rincian Matematis: {{ applicant.name }}
                    </h3>
                    <p class="text-[11px] text-slate-500 font-mono">
                        NIM: {{ applicant.nim }} &bull; {{ applicant.study_program }} &bull; Rank #{{ applicant.rank }}
                    </p>
                </div>
                <button @click="emit('close')" class="text-xs text-slate-400 hover:text-slate-700">&times;</button>
            </div>

            <div class="mt-4 space-y-4">
                <!-- Score Cards -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded border border-slate-200 bg-slate-50 p-2.5 text-center">
                        <span class="text-[10px] text-slate-500 uppercase font-medium">Jarak Positif (D⁺)</span>
                        <div class="text-base font-mono font-bold text-rose-700">{{ applicant.d_plus.toFixed(4) }}</div>
                    </div>
                    <div class="rounded border border-slate-200 bg-slate-50 p-2.5 text-center">
                        <span class="text-[10px] text-slate-500 uppercase font-medium">Jarak Negatif (D⁻)</span>
                        <div class="text-base font-mono font-bold text-emerald-700">{{ applicant.d_minus.toFixed(4) }}</div>
                    </div>
                    <div class="rounded border border-slate-200 bg-slate-50 p-2.5 text-center">
                        <span class="text-[10px] text-slate-500 uppercase font-medium">Nilai Akhir (Vᵢ)</span>
                        <div class="text-base font-mono font-bold text-slate-900">{{ applicant.score.toFixed(4) }}</div>
                    </div>
                </div>

                <!-- Per-Criteria Table -->
                <div class="overflow-x-auto rounded border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                        <thead class="bg-slate-50 text-[10px] font-semibold uppercase text-slate-600">
                            <tr>
                                <th class="px-3 py-2">Kriteria</th>
                                <th class="px-3 py-2 text-right font-mono">Mentah (x)</th>
                                <th class="px-3 py-2 text-right font-mono">Normal (r)</th>
                                <th class="px-3 py-2 text-right font-mono">Terbobot (y)</th>
                                <th class="px-3 py-2 text-right font-mono">A⁺</th>
                                <th class="px-3 py-2 text-right font-mono">A⁻</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                            <tr v-for="c in criterias" :key="c.id" class="hover:bg-slate-50/50">
                                <td class="px-3 py-1.5 font-sans font-medium text-slate-800">
                                    {{ c.code }} - {{ c.name }}
                                </td>
                                <td class="px-3 py-1.5 text-right text-slate-600">
                                    {{ intermediates.raw_matrix[applicant.applicant_id]?.[c.id] ?? '-' }}
                                </td>
                                <td class="px-3 py-1.5 text-right text-slate-600">
                                    {{ intermediates.normal_matrix[applicant.applicant_id]?.[c.id]?.toFixed(4) ?? '-' }}
                                </td>
                                <td class="px-3 py-1.5 text-right font-semibold text-slate-900">
                                    {{ intermediates.weighted_matrix[applicant.applicant_id]?.[c.id]?.toFixed(4) ?? '-' }}
                                </td>
                                <td class="px-3 py-1.5 text-right text-emerald-700">
                                    {{ intermediates.ideal_positive[c.id]?.toFixed(4) ?? '-' }}
                                </td>
                                <td class="px-3 py-1.5 text-right text-amber-700">
                                    {{ intermediates.ideal_negative[c.id]?.toFixed(4) ?? '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Formula explanation box -->
                <div class="rounded border border-slate-200 bg-slate-50/50 p-3 text-[11px] text-slate-600 font-mono">
                    <div>Perhitungan Preferensi:</div>
                    <div class="mt-0.5 text-slate-800 font-semibold">
                        Vᵢ = {{ applicant.d_minus.toFixed(4) }} / ({{ applicant.d_plus.toFixed(4) }} + {{ applicant.d_minus.toFixed(4) }}) = {{ applicant.score.toFixed(4) }}
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-end border-t border-slate-200 pt-3">
                <button
                    @click="emit('close')"
                    class="rounded bg-slate-900 px-4 py-1.5 text-xs font-medium text-white hover:bg-slate-800"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>
