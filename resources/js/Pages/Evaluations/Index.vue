<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
import EvaluationModal from './Components/EvaluationModal.vue';

const props = defineProps({
    criterias: {
        type: Array,
        required: true,
    },
    applicants: {
        type: Array,
        required: true,
    },
    totalWeight: {
        type: Number,
        required: true,
    },
    summary: {
        type: Object,
        required: true,
    },
});

const showModal = ref(false);
const selectedApplicant = ref(null);

const openEvaluationModal = (applicant) => {
    selectedApplicant.value = applicant;
    showModal.value = true;
};
</script>

<template>
    <Head title="Matriks Penilaian" />

    <AuthenticatedLayout title="Matriks Keputusan Penilaian (X)">
        <div class="space-y-4">
            <!-- Header Bar -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs text-slate-500">
                        Matriks nilai mentah ($X_{ij}$) pendaftar beasiswa pada masing-masing kriteria seleksi aktif.
                    </p>
                </div>
                <div v-if="summary.is_ready_for_calculation">
                    <Link
                        href="/ranking"
                        class="rounded bg-slate-900 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-slate-800 transition"
                    >
                        Jalankan Kalkulasi TOPSIS &rarr;
                    </Link>
                </div>
            </div>

            <!-- Readiness & Incomplete Alert Banner -->
            <div
                v-if="summary.is_ready_for_calculation"
                class="flex items-center justify-between rounded border border-emerald-300 bg-emerald-50 px-4 py-3 text-xs text-emerald-900"
            >
                <div class="flex items-center space-x-2">
                    <span class="inline-block h-2 w-2 rounded-full bg-emerald-600"></span>
                    <span>Seluruh {{ summary.total_applicants }} pendaftar telah memiliki nilai lengkap. Matriks $X_{ij}$ siap diproses.</span>
                </div>
                <span class="font-mono text-[11px] font-semibold text-emerald-800">Σw = {{ totalWeight }} (100%)</span>
            </div>
            <div
                v-else
                class="flex items-center justify-between rounded border border-amber-300 bg-amber-50 px-4 py-3 text-xs text-amber-900"
            >
                <div class="flex items-center space-x-2">
                    <span class="inline-block h-2 w-2 rounded-full bg-amber-600"></span>
                    <span>
                        Peringatan: Terdapat {{ summary.incomplete_count }} dari {{ summary.total_applicants }} pendaftar dengan nilai kriteria belum lengkap.
                    </span>
                </div>
                <span class="text-[11px] font-medium text-amber-800">Lengkapi sebelum kalkulasi</span>
            </div>

            <!-- Matrix Table -->
            <div class="overflow-x-auto rounded border border-slate-200 bg-white shadow-2xs">
                <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th class="w-10 px-3 py-2.5">No</th>
                            <th class="w-28 px-3 py-2.5">NIM</th>
                            <th class="px-3 py-2.5">Nama Mahasiswa</th>
                            <th
                                v-for="c in criterias"
                                :key="c.id"
                                class="px-3 py-2.5 text-right font-mono"
                            >
                                <div>{{ c.code }}</div>
                                <div class="text-[9px] font-normal normal-case text-slate-400">
                                    {{ c.type }} ({{ Math.round(c.weight * 100) }}%)
                                </div>
                            </th>
                            <th class="w-28 px-3 py-2.5 text-center">Status</th>
                            <th class="w-24 px-3 py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-normal text-slate-800">
                        <tr v-if="applicants.length === 0">
                            <td :colspan="criterias.length + 5" class="px-4 py-8 text-center text-slate-400">
                                Belum ada pendaftar beasiswa yang terdata.
                            </td>
                        </tr>
                        <tr
                            v-for="(a, idx) in applicants"
                            :key="a.id"
                            class="hover:bg-slate-50/80 transition-colors"
                        >
                            <td class="px-3 py-2 text-slate-400 font-mono">{{ idx + 1 }}</td>
                            <td class="px-3 py-2 font-mono font-bold text-slate-900">{{ a.nim }}</td>
                            <td class="px-3 py-2 font-medium text-slate-900 whitespace-nowrap">{{ a.name }}</td>
                            <td
                                v-for="c in criterias"
                                :key="c.id"
                                class="px-3 py-2 text-right font-mono"
                            >
                                <span v-if="a.scores[c.id] !== undefined" class="text-slate-800">
                                    {{ Number(a.scores[c.id]).toLocaleString('id-ID') }}
                                </span>
                                <span v-else class="text-rose-400 font-bold">-</span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <span
                                    v-if="a.is_complete"
                                    class="inline-flex items-center rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 border border-emerald-200"
                                >
                                    Lengkap
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center rounded bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 border border-amber-200"
                                >
                                    {{ a.evaluated_count }}/{{ criterias.length }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <button
                                    @click="openEvaluationModal(a)"
                                    class="rounded bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-700 hover:bg-slate-200"
                                >
                                    {{ a.is_complete ? 'Ubah' : 'Input' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <EvaluationModal
            :show="showModal"
            :applicant="selectedApplicant"
            :criterias="criterias"
            @close="showModal = false"
        />
    </AuthenticatedLayout>
</template>
