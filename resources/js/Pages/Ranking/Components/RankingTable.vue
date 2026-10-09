<script setup>
defineProps({
    results: {
        type: Array,
        required: true,
    },
    quotaLimit: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['select-applicant', 'explain-ai']);
</script>

<template>
    <div class="overflow-x-auto rounded border border-slate-200 bg-white shadow-2xs">
        <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
            <thead class="bg-slate-50 text-[11px] font-semibold text-slate-600 uppercase tracking-wider">
                <tr>
                    <th class="w-16 px-4 py-2.5 text-center">Rank</th>
                    <th class="w-28 px-4 py-2.5">NIM</th>
                    <th class="px-4 py-2.5">Nama Mahasiswa</th>
                    <th class="px-4 py-2.5">Program Studi</th>
                    <th class="w-24 px-4 py-2.5 text-right font-mono">D+</th>
                    <th class="w-24 px-4 py-2.5 text-right font-mono">D-</th>
                    <th class="w-32 px-4 py-2.5 text-right font-mono">Skor (V)</th>
                    <th class="w-36 px-4 py-2.5 text-center">Status</th>
                    <th class="w-32 px-4 py-2.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-normal text-slate-800">
                <tr v-if="results.length === 0">
                    <td colspan="9" class="px-4 py-8 text-center text-slate-400">
                        Tidak ada pendaftar yang memenuhi kriteria pencarian/penyaringan.
                    </td>
                </tr>
                <template v-for="item in results" :key="item.applicant_id">
                    <!-- Cut-off divider for quota boundary -->
                    <tr v-if="item.rank === quotaLimit + 1" class="bg-amber-50/50">
                        <td colspan="9" class="px-4 py-1.5 text-center text-[10px] font-semibold uppercase tracking-wider text-amber-800 border-y border-amber-200">
                            &bull; Batas Kuota Penerima Beasiswa (Maksimal {{ quotaLimit }} Mahasiswa) &bull;
                        </td>
                    </tr>
                    <tr
                        :class="item.is_passed ? 'bg-emerald-50/20 hover:bg-emerald-50/50' : 'hover:bg-slate-50/80'"
                        class="transition-colors"
                    >
                        <!-- Rank with badge for top 3 -->
                        <td class="px-4 py-2.5 text-center">
                            <span
                                v-if="item.rank === 1"
                                class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-[10px] font-bold text-white shadow-xs"
                            >
                                1
                            </span>
                            <span
                                v-else-if="item.rank === 2"
                                class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-400 text-[10px] font-bold text-white shadow-xs"
                            >
                                2
                            </span>
                            <span
                                v-else-if="item.rank === 3"
                                class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-700 text-[10px] font-bold text-white shadow-xs"
                            >
                                3
                            </span>
                            <span v-else class="font-mono text-slate-500 font-semibold">
                                #{{ item.rank }}
                            </span>
                        </td>

                        <td class="px-4 py-2.5 font-mono font-bold text-slate-900">{{ item.nim }}</td>
                        <td class="px-4 py-2.5 font-medium text-slate-900">{{ item.name }}</td>
                        <td class="px-4 py-2.5 text-slate-600">{{ item.study_program }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-slate-600">{{ item.d_plus.toFixed(4) }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-slate-600">{{ item.d_minus.toFixed(4) }}</td>
                        <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-900">
                            <div class="flex items-center justify-end space-x-1.5">
                                <span>{{ item.score.toFixed(4) }}</span>
                                <div class="w-12 h-1.5 bg-slate-200 rounded-full overflow-hidden hidden sm:block">
                                    <div
                                        class="h-full bg-slate-900 rounded-full"
                                        :style="{ width: `${Math.min(item.score * 100, 100)}%` }"
                                    ></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <span
                                v-if="item.is_passed"
                                class="inline-flex items-center rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 border border-emerald-300 tracking-wide uppercase"
                            >
                                Lolos Kuota
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center rounded bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500 border border-slate-200 tracking-wide uppercase"
                            >
                                Tidak Lolos
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            <button
                                @click="emit('explain-ai', item)"
                                class="inline-flex items-center gap-1 rounded border border-amber-300 bg-amber-50 px-2 py-1 text-[11px] font-bold text-amber-800 hover:bg-amber-100 transition mr-1.5 cursor-pointer"
                                title="Analisis Keputusan AI"
                            >
                                <span class="material-symbols-outlined text-[13px] text-amber-600">auto_awesome</span>
                                <span>AI</span>
                            </button>
                            <button
                                @click="emit('select-applicant', item)"
                                class="rounded border border-slate-300 bg-white px-2 py-1 text-[11px] font-medium text-slate-700 hover:bg-slate-50 cursor-pointer"
                            >
                                Detail
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>
