<script setup>
defineProps({
    stats: {
        type: Object,
        required: true,
    },
    passedCount: {
        type: Number,
        default: 0,
    },
});

defineEmits(['edit-quota']);
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Card 1: Total Pendaftar -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-2xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Pendaftar</span>
                    <div class="mt-1 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold text-slate-900">{{ stats.applicant_count }}</span>
                        <span class="text-xs text-slate-500">Mahasiswa</span>
                    </div>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                    <span class="material-symbols-outlined text-[22px]">group</span>
                </div>
            </div>
            <div class="mt-4 pt-2 border-t border-slate-100 flex items-center gap-1.5 text-xs">
                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700">
                    +{{ stats.complete_evaluated_count }} Dinilai
                </span>
                <span class="text-[11px] text-slate-500">berkas terverifikasi</span>
            </div>
        </div>

        <!-- Card 2: Kriteria Aktif -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-2xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kriteria Aktif</span>
                    <div class="mt-1 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold text-slate-900">{{ stats.criteria_count }}</span>
                        <span class="text-xs text-slate-500">Variabel Bobot</span>
                    </div>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                    <span class="material-symbols-outlined text-[22px]">tune</span>
                </div>
            </div>
            <div class="mt-4 pt-2 border-t border-slate-100 flex items-center gap-1.5 flex-wrap text-[10px] font-bold">
                <span :class="stats.total_weight === 1 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'" class="rounded px-1.5 py-0.5">
                    {{ stats.total_weight === 1 ? 'Total 100% Valid' : `Σw = ${stats.total_weight}` }}
                </span>
                <span class="rounded bg-teal-50 px-1.5 py-0.5 text-teal-700">{{ stats.benefit_count || 0 }} Benefit</span>
                <span class="rounded bg-rose-50 px-1.5 py-0.5 text-rose-700">{{ stats.cost_count || 0 }} Cost</span>
            </div>
        </div>

        <!-- Card 3: Kuota Penerima -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-2xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kuota Penerima</span>
                        <button
                            @click="$emit('edit-quota')"
                            type="button"
                            class="text-[10px] font-bold text-blue-600 hover:text-blue-800 transition flex items-center gap-0.5 underline cursor-pointer"
                        >
                            <span>Ubah</span>
                        </button>
                    </div>
                    <div class="mt-1 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold text-slate-900">{{ stats.quota?.quota_limit || '-' }}</span>
                        <span class="text-xs text-slate-500">Alokasi SK</span>
                    </div>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                    <span class="material-symbols-outlined text-[22px]">verified</span>
                </div>
            </div>
            <div class="mt-4 pt-2 border-t border-slate-100 space-y-1">
                <div class="flex justify-between text-[11px] font-medium text-slate-500">
                    <span>Status Rekomendasi</span>
                    <span class="font-bold text-slate-800">{{ passedCount }} / {{ stats.quota?.quota_limit || 0 }}</span>
                </div>
                <div class="h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                    <div
                        class="h-full bg-blue-600 rounded-full transition-all duration-500"
                        :style="{ width: `${stats.quota?.quota_limit ? Math.min(100, Math.round((passedCount / stats.quota.quota_limit) * 100)) : 0}%` }"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Card 4: Status Engine TOPSIS -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-2xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Status Komputasi TOPSIS</span>
                    <div class="mt-1.5">
                        <span
                            v-if="stats.is_ready_for_calculation"
                            class="inline-flex items-center gap-1 rounded-full bg-teal-50 px-2 py-0.5 text-xs font-bold text-teal-700"
                        >
                            <span class="material-symbols-outlined text-[14px]">check_circle</span>
                            <span>Tersinkronisasi</span>
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700"
                        >
                            <span class="material-symbols-outlined text-[14px]">pending</span>
                            <span>Menunggu Data</span>
                        </span>
                    </div>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-50 text-teal-700">
                    <span class="material-symbols-outlined text-[22px]">calculate</span>
                </div>
            </div>
            <div class="mt-4 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] font-mono text-slate-500">
                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px] text-slate-400">schedule</span>
                    <span>Baru saja</span>
                </span>
                <span class="text-blue-700 font-semibold">D+ • D- Konvergen</span>
            </div>
        </div>
    </div>
</template>
