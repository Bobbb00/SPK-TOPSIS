<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    rankings: {
        type: Array,
        required: true,
    },
    totalApplicants: {
        type: Number,
        required: true,
    },
    isReady: {
        type: Boolean,
        required: true,
    },
});

defineEmits(['explain-ai']);
</script>

<template>
    <div class="flex flex-col rounded-xl border border-slate-200/80 bg-white shadow-2xs overflow-hidden">
        <!-- Header -->
        <div class="p-4 sm:p-5 flex items-center justify-between border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                    <span class="material-symbols-outlined text-[20px]">leaderboard</span>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 leading-tight">Top 5 Rekomendasi Tertinggi (V<sub>i</sub>)</h2>
                    <p class="text-[11px] text-slate-500">Kandidat dengan skor kedekatan relatif terbaik terhadap solusi ideal</p>
                </div>
            </div>
            <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-[10px] font-semibold text-slate-600">
                Peringkat 1 s/d 5
            </span>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table v-if="rankings.length > 0" class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="py-2.5 px-4 text-center">Rank</th>
                        <th class="py-2.5 px-4">Mahasiswa / NIM</th>
                        <th class="py-2.5 px-4">Program Studi</th>
                        <th class="py-2.5 px-4 text-right">Nilai Preferensi (V<sub>i</sub>)</th>
                        <th class="py-2.5 px-4 text-center">Status</th>
                        <th class="py-2.5 px-4 text-center">AI Insight</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="item in rankings" :key="item.nim" class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3 px-4 text-center">
                            <span
                                :class="item.rank === 1 ? 'bg-[#00236f] text-white font-bold' : 'bg-slate-100 text-slate-700 font-semibold'"
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs shadow-2xs"
                            >
                                {{ item.rank }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-800 leading-tight">{{ item.name }}</div>
                            <div class="font-mono text-[10px] text-slate-400">{{ item.nim }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-600 text-[11px]">
                            {{ item.study_program }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-blue-700">
                            {{ Number(item.preference_score ?? item.score ?? 0).toFixed(4) }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span
                                v-if="item.is_passed"
                                class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700"
                            >
                                <span class="material-symbols-outlined text-[12px]">done</span>
                                <span>Lolos</span>
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500"
                            >
                                Cadangan
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <button
                                @click="$emit('explain-ai', item)"
                                type="button"
                                title="Buka Analisis AI"
                                class="inline-flex items-center gap-1 rounded-md bg-amber-50 border border-amber-200/80 px-2 py-1 text-[10px] font-bold text-amber-800 hover:bg-amber-100 transition cursor-pointer"
                            >
                                <span class="material-symbols-outlined text-[13px] text-amber-600">auto_awesome</span>
                                <span>AI</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Empty / Unready State -->
            <div v-else class="p-8 text-center text-xs text-slate-500">
                <span class="material-symbols-outlined text-[32px] text-slate-400 mb-1">pending_actions</span>
                <p class="font-semibold text-slate-700">Kalkulasi Belum Tersedia</p>
                <p class="mt-1 text-[11px] text-slate-400">Lengkapi penilaian seluruh pendaftar dan pastikan total bobot tepat 1.0 untuk menampilkan perangkingan.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <span class="text-[11px]">Menampilkan {{ rankings.length }} dari {{ totalApplicants }} alternatif hasil komputasi matriks</span>
            <Link href="/ranking" class="inline-flex items-center gap-1 font-semibold text-blue-700 hover:text-blue-900 transition-colors">
                <span>Lihat Semua {{ totalApplicants }} Pendaftar</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </Link>
        </div>
    </div>
</template>
