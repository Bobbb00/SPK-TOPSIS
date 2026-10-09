<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    applicant: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const isLoading = ref(false);
const resultData = ref(null);
const errorMessage = ref('');

const suitabilityScore = computed(() => {
    const raw = resultData.value?.applicant?.score ?? props.applicant?.score ?? props.applicant?.preference_score ?? 0;
    return (Number(raw) * 100).toFixed(1);
});

const formattedExplanation = computed(() => {
    if (!resultData.value?.explanation) return '';
    let text = resultData.value.explanation;
    // Format bold headers nicely
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-slate-900">$1</strong>');
    return text;
});

const fetchExplanation = async () => {
    const applicantId = props.applicant?.id || props.applicant?.applicant_id;
    if (!applicantId) return;

    isLoading.value = true;
    errorMessage.value = '';
    resultData.value = null;

    try {
        const response = await fetch(`/ranking/${applicantId}/explain`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (!response.ok) {
            const errData = await response.json().catch(() => ({}));
            throw new Error(errData.message || 'Gagal mengambil analisis AI');
        }
        resultData.value = await response.json();
    } catch (err) {
        errorMessage.value = err.message || 'Terjadi kesalahan sistem saat memproses analisis';
    } finally {
        isLoading.value = false;
    }
};

watch(
    [() => props.show, () => props.applicant],
    ([isOpen, app]) => {
        if (isOpen && app) {
            fetchExplanation();
        } else if (!isOpen) {
            resultData.value = null;
            errorMessage.value = '';
            isLoading.value = false;
        }
    },
    { immediate: true }
);
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header -->
            <div class="px-6 py-4 bg-linear-to-r from-blue-900 via-indigo-900 to-slate-900 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 backdrop-blur-md text-amber-300 shadow-inner">
                        <span class="material-symbols-outlined text-[24px]">auto_awesome</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold tracking-tight">Analisis Keputusan AI • TOPSIS</h3>
                            <span class="rounded bg-amber-400/20 text-amber-300 border border-amber-300/30 px-1.5 py-0.2 text-[9px] font-bold uppercase tracking-wider">
                                {{ resultData?.engine || 'Smart DSS Engine' }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-300">Penjelasan transparan kelayakan penerima beasiswa</p>
                    </div>
                </div>
                <button
                    @click="emit('close')"
                    class="rounded-lg p-1 text-slate-400 hover:bg-white/10 hover:text-white transition"
                >
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <!-- Content Area -->
            <div class="p-6 overflow-y-auto space-y-4 flex-1">
                <!-- Candidate Brief Bar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl bg-slate-50 p-4 border border-slate-200/80">
                    <div>
                        <div class="font-bold text-sm text-slate-900">{{ applicant?.name }}</div>
                        <div class="text-xs text-slate-500 font-mono">{{ applicant?.nim }} • {{ applicant?.study_program }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-lg bg-blue-100 text-blue-800 px-2.5 py-1 text-xs font-bold font-mono">
                            Rank #{{ resultData?.applicant?.rank ?? applicant?.rank }}
                        </span>
                        <span
                            class="rounded-lg bg-amber-50 border border-amber-200 text-amber-900 px-2.5 py-1 text-xs font-semibold"
                            :title="`Skor Preferensi TOPSIS: ${Number(resultData?.applicant?.score ?? applicant?.score ?? applicant?.preference_score ?? 0).toFixed(4)}`"
                        >
                            Kecocokan: {{ suitabilityScore }}%
                        </span>
                        <span
                            :class="(resultData?.applicant?.is_passed ?? applicant?.is_passed) ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-200 text-slate-700 border-slate-300'"
                            class="rounded-lg border px-2.5 py-1 text-xs font-bold"
                        >
                            {{ (resultData?.applicant?.is_passed ?? applicant?.is_passed) ? 'Lolos Kuota' : 'Cadangan' }}
                        </span>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="isLoading" class="py-12 flex flex-col items-center justify-center space-y-3">
                    <span class="material-symbols-outlined text-[36px] text-blue-600 animate-spin">sync</span>
                    <p class="text-xs font-semibold text-slate-700">Menganalisis profil & menyusun penjelasan bahasa awam...</p>
                    <p class="text-[11px] text-slate-400">Merangkum faktor penentu kelayakan dalam narasi yang ramah dan mudah dimengerti</p>
                </div>

                <!-- Error State -->
                <div v-else-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-700 flex items-center justify-between gap-3">
                    <span>{{ errorMessage }}</span>
                    <button
                        @click="fetchExplanation"
                        type="button"
                        class="rounded bg-rose-600 px-2.5 py-1 text-white font-medium hover:bg-rose-700 transition cursor-pointer shrink-0"
                    >
                        Coba Lagi
                    </button>
                </div>

                <!-- Success Data View -->
                <div v-else-if="resultData" class="space-y-4">
                    <!-- Key Highlights -->
                    <div v-if="resultData.key_factors?.length > 0" class="space-y-1.5">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Poin Kunci Kelayakan</div>
                        <div class="space-y-1.5">
                            <div
                                v-for="(factor, idx) in resultData.key_factors"
                                :key="idx"
                                class="flex items-start gap-2 rounded-lg bg-blue-50/60 border border-blue-100 p-2.5 text-xs text-slate-700 leading-snug"
                            >
                                <span class="material-symbols-outlined text-[16px] text-blue-600 shrink-0 mt-0.5">check_circle</span>
                                <span>{{ factor }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Narrative Explanation -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Penjelasan Keputusan (Bahasa Awam)</div>
                            <span class="text-[10px] text-slate-400 font-mono">
                                V<sub>i</sub>: {{ Number(resultData?.applicant?.score ?? applicant?.score ?? 0).toFixed(4) }}
                            </span>
                        </div>
                        <div
                            class="rounded-xl bg-slate-50/70 border border-slate-200 p-4 text-xs leading-relaxed text-slate-700 whitespace-pre-line space-y-2"
                            v-html="formattedExplanation"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-200/80 flex items-center justify-between shrink-0">
                <span class="text-[11px] text-slate-500 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[15px] text-slate-400">verified_user</span>
                    <span>Audit trail terjamin bebas bias subjektif</span>
                </span>
                <button
                    @click="emit('close')"
                    class="rounded-lg bg-slate-800 px-4 py-1.5 text-xs font-semibold text-white hover:bg-slate-900 transition"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>
