<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    quota: {
        type: Object,
        default: () => ({ quota_limit: 5, period: 'Semester Genap 2025/2026' }),
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    quota_limit: props.quota?.quota_limit || 5,
    period: props.quota?.period || 'Semester Genap 2025/2026',
});

watch(
    () => props.quota,
    (newVal) => {
        if (newVal) {
            form.quota_limit = newVal.quota_limit || 5;
            form.period = newVal.period || 'Semester Genap 2025/2026';
        }
    },
    { deep: true }
);

const submit = () => {
    form.put('/scholarship-quota', {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-xl border border-slate-200 overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                        <span class="material-symbols-outlined text-[19px]">verified</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 leading-tight">Pengaturan Kuota Beasiswa</h3>
                        <p class="text-[11px] text-slate-500">Tentukan batas penerima lolos seleksi TOPSIS</p>
                    </div>
                </div>
                <button
                    @click="emit('close')"
                    class="rounded-lg p-1 text-slate-400 hover:bg-slate-200/60 hover:text-slate-600 transition"
                >
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Batas Kuota Penerima (Orang)
                    </label>
                    <input
                        v-model.number="form.quota_limit"
                        type="number"
                        min="1"
                        max="1000"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-800 focus:border-blue-600 focus:outline-hidden"
                        placeholder="Contoh: 5, 10, 35"
                        required
                    />
                    <p v-if="form.errors.quota_limit" class="mt-1 text-[11px] text-rose-600">
                        {{ form.errors.quota_limit }}
                    </p>
                    <p class="mt-1 text-[10px] text-slate-400">
                        Peringkat 1 hingga batas ini akan otomatis berstatus <strong>Lolos</strong>.
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nama / Label Periode Seleksi
                    </label>
                    <input
                        v-model="form.period"
                        type="text"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-800 focus:border-blue-600 focus:outline-hidden"
                        placeholder="Contoh: Semester Genap 2025/2026"
                        required
                    />
                    <p v-if="form.errors.period" class="mt-1 text-[11px] text-rose-600">
                        {{ form.errors.period }}
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button
                        type="button"
                        @click="emit('close')"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-1.5 rounded-lg bg-[#0051d5] px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-[#00236f] transition disabled:opacity-70 cursor-pointer"
                    >
                        <span v-if="form.processing" class="material-symbols-outlined text-[16px] animate-spin">sync</span>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
