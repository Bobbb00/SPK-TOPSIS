<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
import CriteriaModal from './Components/CriteriaModal.vue';
import BatchWeightModal from './Components/BatchWeightModal.vue';

const props = defineProps({
    criterias: {
        type: Array,
        required: true,
    },
    totalWeight: {
        type: Number,
        required: true,
    },
});

const showSingleModal = ref(false);
const showBatchModal = ref(false);
const activeCriteria = ref(null);

const openCreate = () => {
    activeCriteria.value = null;
    showSingleModal.value = true;
};

const openEdit = (criteria) => {
    activeCriteria.value = criteria;
    showSingleModal.value = true;
};

const deleteCriteria = (criteria) => {
    if (confirm(`Hapus kriteria "${criteria.code} - ${criteria.name}"? Penilaian terkait juga akan terhapus.`)) {
        router.delete(`/criteria/${criteria.id}`);
    }
};
</script>

<template>
    <Head title="Kriteria & Bobot" />

    <AuthenticatedLayout title="Kriteria & Bobot Penilaian">
        <div class="space-y-4">
            <!-- Header Bar & Summary Status -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs text-slate-500">
                        Kelola parameter seleksi beasiswa beserta sifat (Benefit/Cost) dan bobot preferensi ($W_j$).
                    </p>
                </div>

                <div class="flex items-center space-x-2">
                    <button
                        @click="showBatchModal = true"
                        class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Sesuaikan Bobot
                    </button>
                    <button
                        @click="openCreate"
                        class="rounded bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-800"
                    >
                        + Tambah Kriteria
                    </button>
                </div>
            </div>

            <!-- Total Weight Metric Indicator -->
            <div
                class="flex items-center justify-between rounded border px-4 py-3 text-xs"
                :class="Math.abs(totalWeight - 1.0) < 0.001 ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-amber-200 bg-amber-50 text-amber-900'"
            >
                <div class="flex items-center space-x-2">
                    <span
                        class="inline-block h-2 w-2 rounded-full"
                        :class="Math.abs(totalWeight - 1.0) < 0.001 ? 'bg-emerald-600' : 'bg-amber-600'"
                    ></span>
                    <span>
                        Total Akumulasi Bobot saat ini:
                        <strong class="font-mono text-sm">{{ totalWeight }}</strong> ({{ Math.round(totalWeight * 100) }}%)
                    </span>
                </div>
                <div class="text-[11px] font-medium">
                    <span v-if="Math.abs(totalWeight - 1.0) < 0.001" class="text-emerald-700">
                        &check; Valid (Sesuai kaidah TOPSIS $\sum W = 1.0$)
                    </span>
                    <span v-else class="text-amber-800">
                        &excl; Belum tepat 1.00 (Silakan sesuaikan kembali)
                    </span>
                </div>
            </div>

            <!-- Data Table -->
            <div class="overflow-hidden rounded border border-slate-200 bg-white shadow-2xs">
                <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="w-16 px-4 py-2.5">Kode</th>
                            <th class="px-4 py-2.5">Nama Kriteria</th>
                            <th class="w-28 px-4 py-2.5">Sifat</th>
                            <th class="w-28 px-4 py-2.5 text-right">Bobot (W)</th>
                            <th class="w-24 px-4 py-2.5 text-right">Porsi</th>
                            <th class="w-28 px-4 py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-normal text-slate-800">
                        <tr v-if="criterias.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                Belum ada kriteria seleksi yang didaftarkan.
                            </td>
                        </tr>
                        <tr
                            v-for="c in criterias"
                            :key="c.id"
                            class="hover:bg-slate-50/80 transition-colors"
                        >
                            <td class="px-4 py-2 font-mono font-bold text-slate-900">{{ c.code }}</td>
                            <td class="px-4 py-2 font-medium text-slate-900">{{ c.name }}</td>
                            <td class="px-4 py-2">
                                <span
                                    v-if="c.type === 'benefit'"
                                    class="inline-flex items-center rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-emerald-700 border border-emerald-200"
                                >
                                    Benefit
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center rounded bg-amber-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-amber-700 border border-amber-200"
                                >
                                    Cost
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right font-mono text-slate-800">{{ Number(c.weight).toFixed(2) }}</td>
                            <td class="px-4 py-2 text-right font-mono text-slate-500">{{ Math.round(c.weight * 100) }}%</td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <button
                                    @click="openEdit(c)"
                                    class="text-indigo-600 hover:underline text-[11px]"
                                >
                                    Ubah
                                </button>
                                <button
                                    @click="deleteCriteria(c)"
                                    class="text-rose-600 hover:underline text-[11px]"
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modals -->
        <CriteriaModal
            :show="showSingleModal"
            :criteria="activeCriteria"
            @close="showSingleModal = false"
        />

        <BatchWeightModal
            :show="showBatchModal"
            :criterias="criterias"
            @close="showBatchModal = false"
        />
    </AuthenticatedLayout>
</template>
