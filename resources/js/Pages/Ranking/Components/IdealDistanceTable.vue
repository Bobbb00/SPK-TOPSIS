<script setup>
defineProps({
    criterias: {
        type: Array,
        required: true,
    },
    results: {
        type: Array,
        required: true,
    },
    intermediates: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="space-y-6">
        <!-- Acuan Solusi Ideal Positif (A+) & Negatif (A-) -->
        <div class="rounded border border-slate-200 bg-white shadow-2xs">
            <div class="border-b border-slate-200 bg-slate-50/50 px-4 py-3">
                <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-900">
                    Solusi Ideal Positif (A⁺) dan Solusi Ideal Negatif (A⁻)
                </h4>
                <p class="text-[11px] text-slate-500">
                    Benefit: A⁺ = Nilai Maksimum, A⁻ = Nilai Minimum &bull; Cost: A⁺ = Nilai Minimum, A⁻ = Nilai Maksimum.
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2">Acuan Solusi Ideal</th>
                            <th
                                v-for="c in criterias"
                                :key="c.id"
                                class="px-4 py-2 text-right font-mono"
                            >
                                {{ c.code }} ({{ c.type }})
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-xs">
                        <tr class="bg-emerald-50/40">
                            <td class="px-4 py-2 font-sans font-semibold text-emerald-900">
                                Solusi Ideal Positif (A⁺)
                            </td>
                            <td
                                v-for="c in criterias"
                                :key="c.id"
                                class="px-4 py-2 text-right font-bold text-emerald-800"
                            >
                                {{ intermediates.ideal_positive[c.id]?.toFixed(4) }}
                            </td>
                        </tr>
                        <tr class="bg-amber-50/40">
                            <td class="px-4 py-2 font-sans font-semibold text-amber-900">
                                Solusi Ideal Negatif (A⁻)
                            </td>
                            <td
                                v-for="c in criterias"
                                :key="c.id"
                                class="px-4 py-2 text-right font-bold text-amber-800"
                            >
                                {{ intermediates.ideal_negative[c.id]?.toFixed(4) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel Jarak Solusi & Preferensi -->
        <div class="rounded border border-slate-200 bg-white shadow-2xs">
            <div class="border-b border-slate-200 bg-slate-50/50 px-4 py-3">
                <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-900">
                    Jarak Solusi Ideal (D⁺ & D⁻) dan Nilai Preferensi Akhir (Vᵢ)
                </h4>
                <p class="text-[11px] text-slate-500">
                    Formula skor: Vᵢ = D⁻ / (D⁺ + D⁻)
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th class="w-12 px-4 py-2.5">Rank</th>
                            <th class="w-28 px-4 py-2.5">NIM</th>
                            <th class="px-4 py-2.5">Nama Mahasiswa</th>
                            <th class="w-28 px-4 py-2.5 text-right font-mono">Jarak D⁺</th>
                            <th class="w-28 px-4 py-2.5 text-right font-mono">Jarak D⁻</th>
                            <th class="w-32 px-4 py-2.5 text-right font-mono">D⁺ + D⁻</th>
                            <th class="w-28 px-4 py-2.5 text-right font-mono">Skor Akhir (V)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-xs">
                        <tr
                            v-for="item in results"
                            :key="item.applicant_id"
                            class="hover:bg-slate-50/80 transition-colors"
                        >
                            <td class="px-4 py-2 text-slate-500 font-bold">#{{ item.rank }}</td>
                            <td class="px-4 py-2 text-slate-900 font-bold">{{ item.nim }}</td>
                            <td class="px-4 py-2 font-sans font-medium text-slate-900">{{ item.name }}</td>
                            <td class="px-4 py-2 text-right text-rose-700">{{ item.d_plus.toFixed(4) }}</td>
                            <td class="px-4 py-2 text-right text-emerald-700">{{ item.d_minus.toFixed(4) }}</td>
                            <td class="px-4 py-2 text-right text-slate-500">
                                {{ (item.d_plus + item.d_minus).toFixed(4) }}
                            </td>
                            <td class="px-4 py-2 text-right font-bold text-slate-900">
                                {{ item.score.toFixed(4) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
