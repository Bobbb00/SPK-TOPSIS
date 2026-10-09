<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
import ApplicantModal from './Components/ApplicantModal.vue';

const props = defineProps({
    applicants: {
        type: Array,
        required: true,
    },
    studyPrograms: {
        type: Array,
        required: true,
    },
    criteriasCount: {
        type: Number,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '', study_program: '' }),
    },
});

const search = ref(props.filters.search || '');
const selectedProdi = ref(props.filters.study_program || '');

const showModal = ref(false);
const activeApplicant = ref(null);

const applyFilters = () => {
    router.get(
        '/applicants',
        { search: search.value, study_program: selectedProdi.value },
        { preserveState: true, replace: true }
    );
};

const openCreate = () => {
    activeApplicant.value = null;
    showModal.value = true;
};

const openEdit = (applicant) => {
    activeApplicant.value = applicant;
    showModal.value = true;
};

const deleteApplicant = (applicant) => {
    if (confirm(`Hapus pendaftar "${applicant.name} (${applicant.nim})"? Seluruh nilai evaluasinya juga akan terhapus.`)) {
        router.delete(`/applicants/${applicant.id}`);
    }
};
</script>

<template>
    <Head title="Data Pendaftar" />

    <AuthenticatedLayout title="Data Pendaftar Beasiswa">
        <div class="space-y-4">
            <!-- Header & Action Controls -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs text-slate-500">
                        Daftar mahasiswa calon penerima beasiswa yang terdaftar dalam sistem.
                    </p>
                </div>
                <button
                    @click="openCreate"
                    class="rounded bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-800"
                >
                    + Tambah Pendaftar
                </button>
            </div>

            <!-- Filters Bar (Search & Program Studi) -->
            <div class="grid grid-cols-1 gap-2.5 rounded border border-slate-200 bg-white p-3 shadow-2xs sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <input
                        v-model="search"
                        @keyup.enter="applyFilters"
                        type="text"
                        placeholder="Cari berdasarkan Nama atau NIM lalu tekan Enter..."
                        class="block w-full rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-slate-800 focus:outline-hidden"
                    />
                </div>
                <div class="flex items-center space-x-2">
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
                    <button
                        @click="applyFilters"
                        class="rounded border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100"
                    >
                        Saring
                    </button>
                </div>
            </div>

            <!-- Data Table -->
            <div class="overflow-hidden rounded border border-slate-200 bg-white shadow-2xs">
                <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="w-12 px-4 py-2.5">No</th>
                            <th class="w-32 px-4 py-2.5">NIM</th>
                            <th class="px-4 py-2.5">Nama Mahasiswa</th>
                            <th class="px-4 py-2.5">Program Studi</th>
                            <th class="w-36 px-4 py-2.5">Status Nilai</th>
                            <th class="w-40 px-4 py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-normal text-slate-800">
                        <tr v-if="applicants.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                Tidak ada data pendaftar yang sesuai.
                            </td>
                        </tr>
                        <tr
                            v-for="(a, idx) in applicants"
                            :key="a.id"
                            class="hover:bg-slate-50/80 transition-colors"
                        >
                            <td class="px-4 py-2 text-slate-400 font-mono">{{ idx + 1 }}</td>
                            <td class="px-4 py-2 font-mono font-bold text-slate-900">{{ a.nim }}</td>
                            <td class="px-4 py-2 font-medium text-slate-900">{{ a.name }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ a.study_program }}</td>
                            <td class="px-4 py-2">
                                <span
                                    v-if="a.is_complete"
                                    class="inline-flex items-center rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 border border-emerald-200"
                                >
                                    Lengkap ({{ a.evaluations_count }}/{{ criteriasCount }})
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center rounded bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 border border-amber-200"
                                >
                                    Kurang ({{ a.evaluations_count }}/{{ criteriasCount }})
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <Link
                                    href="/evaluations"
                                    class="text-indigo-600 hover:underline text-[11px]"
                                >
                                    Nilai
                                </Link>
                                <button
                                    @click="openEdit(a)"
                                    class="text-slate-600 hover:underline text-[11px]"
                                >
                                    Ubah
                                </button>
                                <button
                                    @click="deleteApplicant(a)"
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

        <ApplicantModal
            :show="showModal"
            :applicant="activeApplicant"
            @close="showModal = false"
        />
    </AuthenticatedLayout>
</template>
