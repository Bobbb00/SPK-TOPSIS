<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
import RankingTable from './Components/RankingTable.vue';
import IntermediateMatrices from './Components/IntermediateMatrices.vue';
import IdealDistanceTable from './Components/IdealDistanceTable.vue';
import ScoreDetailModal from './Components/ScoreDetailModal.vue';
import QuotaModal from '../../Components/Quota/QuotaModal.vue';
import AiExplanationModal from '../../Components/Ranking/AiExplanationModal.vue';

const props = defineProps({
    results: {
        type: Array,
        required: true,
    },
    allResults: {
        type: Array,
        required: true,
    },
    criterias: {
        type: Array,
        required: true,
    },
    intermediates: {
        type: Object,
        required: true,
    },
    quota: {
        type: Object,
        required: true,
    },
    studyPrograms: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '', study_program: '', status: 'all' }),
    },
});

const currentTab = ref('ranking'); // 'ranking', 'matrices', 'ideal'
const search = ref(props.filters.search || '');
const selectedProdi = ref(props.filters.study_program || '');
const selectedStatus = ref(props.filters.status || 'all');

const activeModalApplicant = ref(null);
const showDetailModal = ref(false);
const showQuotaModal = ref(false);
const showAiModal = ref(false);
const selectedAiApplicant = ref(null);

const applyFilters = () => {
    router.get('/ranking', {
        search: search.value,
        study_program: selectedProdi.value,
        status: selectedStatus.value,
    }, { preserveState: true, replace: true });
};

const handleSelectApplicant = (applicant) => {
    activeModalApplicant.value = applicant;
    showDetailModal.value = true;
};

const handleExplainAi = (applicant) => {
    selectedAiApplicant.value = applicant;
    showAiModal.value = true;
};
</script>

<template>
    <Head title="Hasil & Ranking TOPSIS" />

    <AuthenticatedLayout title="Hasil Perangkingan & Keputusan TOPSIS">
        <div class="space-y-4">
            <!-- Header Controls & Export -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs text-slate-500">
                        Urutan peringkat preferensi beasiswa (V<sub>i</sub>) berdasarkan kalkulasi kedekatan terhadap solusi ideal.
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <button
                        @click="showQuotaModal = true"
                        type="button"
                        class="flex items-center gap-1.5 rounded border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition cursor-pointer"
                    >
                        <span class="material-symbols-outlined text-[15px]">verified</span>
                        <span>Kuota: {{ quota.quota_limit }} Penerima</span>
                    </button>
                    <a
                        :href="`/ranking/export-csv?scope=${selectedStatus === 'passed' ? 'passed' : 'all'}`"
                        class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Unduh Excel/CSV
                    </a>
                    <a
                        :href="`/ranking/print?scope=${selectedStatus === 'passed' ? 'passed' : 'all'}`"
                        target="_blank"
                        class="rounded bg-[#00236f] px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-900 transition"
                    >
                        Cetak Laporan
                    </a>
                </div>
            </div>

            <!-- Filter Toolbar -->
            <div class="grid grid-cols-1 gap-2.5 rounded border border-slate-200 bg-white p-3 shadow-2xs sm:grid-cols-4">
                <div class="sm:col-span-2">
                    <input
                        v-model="search"
                        @keyup.enter="applyFilters"
                        type="text"
                        placeholder="Cari Nama / NIM..."
                        class="block w-full rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-slate-800 focus:outline-hidden"
                    />
                </div>
                <div>
                    <select
                        v-model="selectedProdi"
                        @change="applyFilters"
                        class="block w-full rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-slate-800 focus:outline-hidden"
                    >
                        <option value="">Semua Program Studi</option>
                        <option v-for="prodi in studyPrograms" :key="prodi" :value="prodi">
                            {{ prodi }}
                        </option>
                    </select>
                </div>
                <div class="flex items-center space-x-2">
                    <select
                        v-model="selectedStatus"
                        @change="applyFilters"
                        class="block w-full rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-slate-800 focus:outline-hidden"
                    >
                        <option value="all">Semua Status</option>
                        <option value="passed">Hanya Lolos Kuota</option>
                        <option value="failed">Hanya Tidak Lolos</option>
                    </select>
                    <button
                        @click="applyFilters"
                        class="rounded border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100 cursor-pointer"
                    >
                        Saring
                    </button>
                </div>
            </div>

            <!-- Tab Switcher Navigation -->
            <div class="flex border-b border-slate-200 space-x-4 text-xs font-medium">
                <button
                    @click="currentTab = 'ranking'"
                    :class="currentTab === 'ranking' ? 'border-b-2 border-[#00236f] font-bold text-[#00236f]' : 'text-slate-500 hover:text-slate-800'"
                    class="pb-2.5 transition cursor-pointer"
                >
                    Tabel Peringkat & Rekomendasi
                </button>
                <button
                    @click="currentTab = 'matrices'"
                    :class="currentTab === 'matrices' ? 'border-b-2 border-[#00236f] font-bold text-[#00236f]' : 'text-slate-500 hover:text-slate-800'"
                    class="pb-2.5 transition cursor-pointer"
                >
                    Transparansi Matriks (R & Y)
                </button>
                <button
                    @click="currentTab = 'ideal'"
                    :class="currentTab === 'ideal' ? 'border-b-2 border-[#00236f] font-bold text-[#00236f]' : 'text-slate-500 hover:text-slate-800'"
                    class="pb-2.5 transition cursor-pointer"
                >
                    Solusi Ideal & Jarak (A⁺, A⁻, D)
                </button>
            </div>

            <!-- Active Tab Views -->
            <div v-show="currentTab === 'ranking'">
                <RankingTable
                    :results="results"
                    :quota-limit="quota.quota_limit"
                    @select-applicant="handleSelectApplicant"
                    @explain-ai="handleExplainAi"
                />
            </div>

            <div v-show="currentTab === 'matrices'">
                <IntermediateMatrices
                    :criterias="criterias"
                    :applicants="allResults"
                    :intermediates="intermediates"
                />
            </div>

            <div v-show="currentTab === 'ideal'">
                <IdealDistanceTable
                    :criterias="criterias"
                    :results="allResults"
                    :intermediates="intermediates"
                />
            </div>
        </div>

        <!-- Modals -->
        <ScoreDetailModal
            :show="showDetailModal"
            :applicant="activeModalApplicant"
            :criterias="criterias"
            :intermediates="intermediates"
            @close="showDetailModal = false"
        />

        <QuotaModal
            :show="showQuotaModal"
            :quota="quota"
            @close="showQuotaModal = false"
        />

        <AiExplanationModal
            :show="showAiModal"
            :applicant="selectedAiApplicant"
            @close="showAiModal = false"
        />
    </AuthenticatedLayout>
</template>
