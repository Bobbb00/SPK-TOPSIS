<script setup>
import { computed } from 'vue';

const props = defineProps({
    result: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['reset']);

const isPassed = computed(() => Boolean(props.result?.applicant?.is_passed));
const suitabilityScore = computed(() => {
    const raw = props.result?.applicant?.score ?? 0;
    return (Number(raw) * 100).toFixed(1);
});

const formattedExplanation = computed(() => {
    if (!props.result?.explanation) return '';
    let text = props.result.explanation;
    // Format bold headers nicely
    return text.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-slate-900 block mt-2 mb-0.5">$1</strong>');
});

const printResult = () => {
    window.print();
};
</script>

<template>
    <div class="w-full max-w-2xl bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden print:border-none print:shadow-none transition-all">
        <!-- Status Header Banner -->
        <div
            :class="isPassed ? 'bg-linear-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white' : 'bg-linear-to-r from-slate-700 via-slate-800 to-indigo-900 text-white'"
            class="px-6 py-5 flex items-center justify-between"
        >
            <div class="flex items-center gap-3.5">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15 backdrop-blur-md shadow-inner text-white">
                    <span class="material-symbols-outlined text-[28px]">{{ isPassed ? 'verified' : 'hourglass_top' }}</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-200">
                            {{ isPassed ? 'Keputusan Seleksi' : 'Hasil Evaluasi' }}
                        </span>
                        <span class="rounded bg-white/20 px-2 py-0.5 text-[10px] font-bold uppercase">
                            Periode {{ result.quota?.period }}
                        </span>
                    </div>
                    <h2 class="text-lg sm:text-xl font-bold tracking-tight">
                        {{ isPassed ? 'SELAMAT, ANDA LOLOS SELEKSI!' : 'STATUS: KANDIDAT CADANGAN' }}
                    </h2>
                </div>
            </div>
            <div class="hidden sm:flex flex-col items-end">
                <span class="text-xs text-white/80">Peringkat</span>
                <span class="text-2xl font-black font-mono">#{{ result.applicant?.rank }}</span>
            </div>
        </div>

        <!-- Body Details -->
        <div class="p-6 space-y-5">
            <!-- Student Identity & Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 rounded-xl bg-slate-50 p-4 border border-slate-200/80">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nama Mahasiswa</span>
                    <p class="text-sm font-bold text-slate-900 leading-tight mt-0.5">{{ result.applicant?.name }}</p>
                    <p class="text-xs text-slate-500 font-mono">{{ result.applicant?.nim }}</p>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Program Studi</span>
                    <p class="text-xs font-semibold text-slate-800 mt-0.5">{{ result.applicant?.study_program }}</p>
                    <p class="text-[11px] text-slate-500">Institut Teknologi & Sains</p>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Skor Kelayakan</span>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <span class="text-base font-black text-blue-700 font-mono">{{ suitabilityScore }}%</span>
                        <span class="text-[10px] text-slate-400 font-mono">(Vi: {{ Number(result.applicant?.score).toFixed(4) }})</span>
                    </div>
                    <p class="text-[10px] text-slate-500 font-medium">Batas Kuota: {{ result.quota?.quota_limit }} Orang</p>
                </div>
            </div>

            <!-- Key Factors -->
            <div v-if="result.key_factors?.length" class="space-y-1.5">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Poin Kunci Kelayakan</div>
                <div class="space-y-1.5">
                    <div
                        v-for="(factor, idx) in result.key_factors"
                        :key="idx"
                        class="flex items-start gap-2.5 rounded-lg bg-blue-50/50 border border-blue-100 p-2.5 text-xs text-slate-700 leading-snug"
                    >
                        <span class="material-symbols-outlined text-[17px] text-blue-600 shrink-0 mt-0.5">check_circle</span>
                        <span>{{ factor }}</span>
                    </div>
                </div>
            </div>

            <!-- AI Narrative Explanation -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[15px] text-amber-500">auto_awesome</span>
                        <span>Penjelasan Transparan (Analisis AI)</span>
                    </div>
                    <span class="rounded bg-slate-100 text-slate-500 border border-slate-200 px-2 py-0.5 text-[10px] font-mono font-medium">
                        {{ result.engine }}
                    </span>
                </div>
                <div
                    class="rounded-xl bg-slate-50/70 border border-slate-200 p-4 text-xs leading-relaxed text-slate-700 whitespace-pre-line space-y-2"
                    v-html="formattedExplanation"
                ></div>
            </div>

            <!-- Actions Bar -->
            <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-100 print:hidden">
                <button
                    @click="emit('reset')"
                    type="button"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    <span>Cek NIM Lain</span>
                </button>
                <button
                    @click="printResult"
                    type="button"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 rounded-lg bg-[#00236f] px-5 py-2 text-xs font-semibold text-white shadow-2xs hover:bg-blue-900 transition cursor-pointer"
                >
                    <span class="material-symbols-outlined text-[16px]">print</span>
                    <span>Cetak Bukti Pengumuman</span>
                </button>
            </div>
        </div>
    </div>
</template>
