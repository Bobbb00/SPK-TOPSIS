<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    results: {
        type: Array,
        required: true,
    },
    quota: {
        type: Object,
        required: true,
    },
    scope: {
        type: String,
        default: 'all',
    },
    totalApplicants: {
        type: Number,
        required: true,
    },
    generatedAt: {
        type: String,
        required: true,
    },
});

const printDocument = () => {
    window.print();
};
</script>

<template>
    <Head title="Cetak Laporan Keputusan TOPSIS" />

    <div class="min-h-screen bg-white p-8 font-sans text-slate-900 antialiased print:p-0">
        <!-- Floating Action Button (Hidden on Print) -->
        <div class="mb-6 flex items-center justify-between border-b border-slate-200 pb-4 print:hidden">
            <div class="text-xs text-slate-500">
                Pratinjau Cetak Laporan Resmi Hasil Seleksi Beasiswa (Gunakan pilihan "Save as PDF" di dialog cetak).
            </div>
            <div class="flex space-x-2">
                <a
                    href="/ranking"
                    class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                >
                    &larr; Kembali
                </a>
                <button
                    @click="printDocument"
                    class="rounded bg-slate-900 px-4 py-1.5 text-xs font-medium text-white hover:bg-slate-800 shadow-xs"
                >
                    Cetak / Simpan PDF
                </button>
            </div>
        </div>

        <!-- Official Letterhead / Kop Dokumen -->
        <div class="border-b-2 border-slate-900 pb-4 text-center">
            <h1 class="text-base font-bold uppercase tracking-wider text-slate-900">
                Laporan Hasil Keputusan Seleksi Penerimaan Beasiswa
            </h1>
            <h2 class="mt-0.5 text-xs font-medium text-slate-700 uppercase tracking-wide">
                Sistem Pendukung Keputusan Metode TOPSIS (Technique for Order of Preference by Similarity to Ideal Solution)
            </h2>
            <p class="mt-1 text-[11px] text-slate-500 font-mono">
                Tahun Akademik / Periode: {{ quota.period }} &bull; Batas Kuota: {{ quota.quota_limit }} Penerima
            </p>
        </div>

        <!-- Metadata Overview -->
        <div class="mt-4 flex justify-between text-xs text-slate-700">
            <div>
                <p>Cakupan Laporan: <strong class="uppercase">{{ scope === 'passed' ? 'Hanya Pendaftar Lolos Kuota' : 'Seluruh Pendaftar' }}</strong></p>
                <p>Total Pendaftar Dievaluasi: <strong>{{ totalApplicants }} Mahasiswa</strong></p>
            </div>
            <div class="text-right">
                <p>Tanggal Pengesahan: <strong>{{ generatedAt }}</strong></p>
                <p>Status Data: <strong class="text-emerald-700">Tervalidasi Sistem</strong></p>
            </div>
        </div>

        <!-- Official Results Table -->
        <div class="mt-4 overflow-hidden border border-slate-900">
            <table class="min-w-full divide-y divide-slate-300 text-left text-xs">
                <thead class="bg-slate-100 text-[10px] font-bold uppercase tracking-wider text-slate-900 border-b border-slate-900">
                    <tr>
                        <th class="w-12 px-3 py-2 text-center border-r border-slate-300">Rank</th>
                        <th class="w-28 px-3 py-2 border-r border-slate-300">NIM</th>
                        <th class="px-3 py-2 border-r border-slate-300">Nama Lengkap Mahasiswa</th>
                        <th class="px-3 py-2 border-r border-slate-300">Program Studi</th>
                        <th class="w-20 px-3 py-2 text-right border-r border-slate-300 font-mono">D⁺</th>
                        <th class="w-20 px-3 py-2 text-right border-r border-slate-300 font-mono">D⁻</th>
                        <th class="w-24 px-3 py-2 text-right border-r border-slate-300 font-mono">Nilai (V)</th>
                        <th class="w-28 px-3 py-2 text-center">Keputusan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-normal text-slate-900">
                    <tr
                        v-for="item in results"
                        :key="item.applicant_id"
                        class="text-xs"
                    >
                        <td class="px-3 py-1.5 text-center font-bold font-mono border-r border-slate-200">#{{ item.rank }}</td>
                        <td class="px-3 py-1.5 font-mono font-medium border-r border-slate-200">{{ item.nim }}</td>
                        <td class="px-3 py-1.5 font-medium border-r border-slate-200">{{ item.name }}</td>
                        <td class="px-3 py-1.5 border-r border-slate-200 text-slate-700">{{ item.study_program }}</td>
                        <td class="px-3 py-1.5 text-right font-mono border-r border-slate-200 text-slate-600">{{ item.d_plus.toFixed(4) }}</td>
                        <td class="px-3 py-1.5 text-right font-mono border-r border-slate-200 text-slate-600">{{ item.d_minus.toFixed(4) }}</td>
                        <td class="px-3 py-1.5 text-right font-mono font-bold border-r border-slate-200">{{ item.score.toFixed(4) }}</td>
                        <td class="px-3 py-1.5 text-center font-bold text-[10px] uppercase">
                            <span :class="item.is_passed ? 'text-emerald-800' : 'text-slate-500'">
                                {{ item.is_passed ? 'Lolos Kuota' : 'Tidak Lolos' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Official Signatures Block -->
        <div class="mt-12 flex justify-between text-xs text-slate-800 avoid-break">
            <div class="text-center w-56">
                <p>Mengetahui,</p>
                <p class="font-medium">Ketua Panitia Seleksi</p>
                <div class="h-20"></div>
                <p class="font-bold underline uppercase">( Dr. Ir. H. Sudirman, M.T. )</p>
                <p class="text-[10px] text-slate-500 font-mono">NIP. 19780514 200312 1 002</p>
            </div>
            <div class="text-center w-56">
                <p>{{ generatedAt }}</p>
                <p class="font-medium">Sekretaris Tim Penguji</p>
                <div class="h-20"></div>
                <p class="font-bold underline uppercase">( Panitia Seleksi SPK )</p>
                <p class="text-[10px] text-slate-500 font-mono">Administrator SPK TOPSIS</p>
            </div>
        </div>
    </div>
</template>
