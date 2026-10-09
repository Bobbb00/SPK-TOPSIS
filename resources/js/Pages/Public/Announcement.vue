<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ResultCard from './Components/ResultCard.vue';

const props = defineProps({
    quota: {
        type: Object,
        default: () => ({ quota_limit: 5, period: '2026/2027' }),
    },
    totalApplicants: {
        type: Number,
        default: 0,
    },
});

const nim = ref('');
const name = ref('');
const isLoading = ref(false);
const errorMessage = ref('');
const searchResult = ref(null);

const handleCheck = async () => {
    if (!nim.value.trim()) {
        errorMessage.value = 'Silakan masukkan NIM pendaftaran Anda.';
        return;
    }
    isLoading.value = true;
    errorMessage.value = '';
    searchResult.value = null;

    try {
        const response = await fetch('/pengumuman/check', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ nim: nim.value.trim(), name: name.value.trim() }),
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Gagal memverifikasi pendaftaran.');
        searchResult.value = data.data;
    } catch (err) {
        errorMessage.value = err.message || 'Terjadi gangguan saat memeriksa pengumuman.';
    } finally {
        isLoading.value = false;
    }
};

const handleSelectSample = (sampleNim) => {
    nim.value = sampleNim;
    handleCheck();
};

const resetSearch = () => {
    searchResult.value = null;
    nim.value = '';
    name.value = '';
    errorMessage.value = '';
};
</script>

<template>
    <Head title="Pengumuman Seleksi Beasiswa" />

    <div class="min-h-screen bg-[#faf8ff] text-slate-800 flex flex-col font-sans antialiased">
        <!-- Navigation Header -->
        <header class="border-b border-slate-200/80 bg-white/90 backdrop-blur-md px-6 py-4 flex items-center justify-between sticky top-0 z-30 print:hidden">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#00236f] text-white shadow-xs">
                    <span class="material-symbols-outlined text-[20px]">school</span>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-[#00236f] leading-none">Portal Seleksi Beasiswa</h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Institut Teknologi & Sains Nasional</p>
                </div>
            </div>
            <Link
                href="/login"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs"
            >
                <span class="material-symbols-outlined text-[17px] text-slate-500">lock</span>
                <span>Masuk Panitia</span>
            </Link>
        </header>

        <!-- Main Body Content -->
        <main class="flex-1 flex flex-col items-center justify-center p-4 sm:p-8">
            <div class="w-full max-w-2xl flex flex-col items-center space-y-6">
                <!-- Hero Header (Hidden when printing or result shown) -->
                <div v-if="!searchResult" class="text-center space-y-2 max-w-lg">
                    <div class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-semibold text-blue-700">
                        <span class="material-symbols-outlined text-[15px]">campaign</span>
                        <span>Pengumuman Kelulusan • Periode {{ quota.period }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                        Cek Status Kelayakan Beasiswa
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                        Lacak hasil seleksi Anda secara transparan dan mandiri tanpa perlu membuat akun. Dilengkapi penjelasan narasi cerdas berbasis kecerdasan buatan.
                    </p>
                </div>

                <!-- Search Input Form Card (Hidden when result shown) -->
                <div v-if="!searchResult" class="w-full bg-white rounded-2xl border border-slate-200 shadow-xl p-6 sm:p-8 space-y-5">
                    <form @submit.prevent="handleCheck" class="space-y-4">
                        <div>
                            <label for="nim-input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Nomor Induk Mahasiswa (NIM) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">badge</span>
                                <input
                                    id="nim-input"
                                    v-model="nim"
                                    type="text"
                                    required
                                    placeholder="Masukkan NIM Anda (contoh: 2024003)"
                                    class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="name-input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Verifikasi Nama Mahasiswa <span class="text-slate-400 font-normal lowercase">(opsional konfirmasi)</span>
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">person</span>
                                <input
                                    id="name-input"
                                    v-model="name"
                                    type="text"
                                    placeholder="Nama lengkap sesuai berkas pendaftaran"
                                    class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:outline-hidden focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition"
                                />
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="isLoading"
                            class="w-full flex items-center justify-center gap-2 rounded-xl bg-linear-to-r from-blue-700 to-indigo-800 py-3 text-xs sm:text-sm font-bold text-white shadow-md hover:from-blue-800 hover:to-indigo-900 transition disabled:opacity-70 cursor-pointer"
                        >
                            <span v-if="isLoading" class="material-symbols-outlined text-[20px] animate-spin">sync</span>
                            <span v-else class="material-symbols-outlined text-[20px]">search</span>
                            <span>{{ isLoading ? 'Menganalisis Hasil Seleksi...' : 'Periksa Status Kelulusan' }}</span>
                        </button>
                    </form>

                    <!-- Sample Quick Lookup -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 flex-wrap gap-2">
                        <span>Coba contoh data:</span>
                        <div class="flex items-center gap-1.5">
                            <button
                                @click="handleSelectSample('2024003')"
                                type="button"
                                class="rounded bg-slate-100 hover:bg-blue-50 hover:text-blue-700 px-2 py-1 font-mono text-[11px] font-semibold transition cursor-pointer"
                            >
                                2024003 (Citra Dewi)
                            </button>
                            <button
                                @click="handleSelectSample('2024001')"
                                type="button"
                                class="rounded bg-slate-100 hover:bg-blue-50 hover:text-blue-700 px-2 py-1 font-mono text-[11px] font-semibold transition cursor-pointer"
                            >
                                2024001 (Ahmad Fauzi)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Error Alert Box -->
                <div v-if="errorMessage" class="w-full max-w-2xl rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-700 flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-[18px] text-rose-500 shrink-0 mt-0.5">error</span>
                    <div class="flex-1">{{ errorMessage }}</div>
                </div>

                <!-- Result Card Component -->
                <ResultCard
                    v-if="searchResult"
                    :result="searchResult"
                    @reset="resetSearch"
                />
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200/80 bg-white py-4 text-center text-xs text-slate-400 print:hidden">
            Sistem Pendukung Keputusan Seleksi Beasiswa • Audit Trail Matematis TOPSIS & Smart Decision Engine
        </footer>
    </div>
</template>
