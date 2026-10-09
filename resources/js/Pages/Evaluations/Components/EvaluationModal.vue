<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    applicant: Object,
    criterias: Array,
});

const emit = defineEmits(['close']);

const form = useForm({
    scores: [],
});

watch(
    () => [props.applicant, props.criterias],
    () => {
        if (props.applicant && props.criterias) {
            form.scores = props.criterias.map((c) => ({
                criteria_id: c.id,
                code: c.code,
                name: c.name,
                type: c.type,
                weight: c.weight,
                score: props.applicant.scores?.[c.id] ?? '',
            }));
        }
    },
    { immediate: true }
);

const filledCount = computed(() => {
    return form.scores.filter((s) => s.score !== '' && s.score !== null && !isNaN(Number(s.score))).length;
});

const submit = () => {
    if (!props.applicant) return;

    form.put(`/evaluations/${props.applicant.id}`, {
        onSuccess: () => emit('close'),
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-lg rounded border border-slate-300 bg-white p-5 shadow-lg">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-900">
                        Input Penilaian: {{ applicant?.name }}
                    </h3>
                    <p class="text-[11px] text-slate-500 font-mono">
                        NIM: {{ applicant?.nim }} &bull; {{ applicant?.study_program }}
                    </p>
                </div>
                <button @click="emit('close')" class="text-xs text-slate-400 hover:text-slate-700">&times;</button>
            </div>

            <form @submit.prevent="submit" class="mt-4 space-y-3">
                <div class="max-h-80 overflow-y-auto space-y-2.5 pr-1">
                    <div
                        v-for="(item, idx) in form.scores"
                        :key="item.criteria_id"
                        class="rounded border border-slate-200 bg-slate-50/50 p-2.5 text-xs"
                    >
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center space-x-2">
                                <span class="font-mono font-bold text-slate-900">{{ item.code }}</span>
                                <span class="font-medium text-slate-700">{{ item.name }}</span>
                            </div>
                            <span
                                :class="item.type === 'benefit' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                                class="rounded border px-1.5 py-0.2 text-[9px] uppercase font-semibold tracking-wider"
                            >
                                {{ item.type }} &bull; {{ Math.round(item.weight * 100) }}%
                            </span>
                        </div>
                        <div>
                            <input
                                v-model.number="item.score"
                                type="number"
                                step="any"
                                min="0"
                                required
                                placeholder="Masukkan nilai evaluasi..."
                                class="block w-full rounded border border-slate-300 bg-white px-2.5 py-1 text-xs focus:border-slate-800 focus:outline-hidden"
                            />
                        </div>
                    </div>
                </div>

                <!-- Footer Summary & Status -->
                <div class="flex items-center justify-between rounded border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
                    <span class="text-slate-600">
                        Kelengkapan Kriteria: <strong>{{ filledCount }}</strong> dari {{ form.scores.length }} terisi
                    </span>
                    <span
                        v-if="filledCount === form.scores.length"
                        class="font-semibold text-emerald-700"
                    >
                        &check; Nilai Lengkap
                    </span>
                    <span v-else class="text-amber-700">
                        &excl; Belum lengkap
                    </span>
                </div>

                <div class="flex items-center justify-end space-x-2 border-t border-slate-200 pt-3">
                    <button
                        type="button"
                        @click="emit('close')"
                        class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded bg-slate-900 px-4 py-1.5 text-xs font-medium text-white hover:bg-slate-800 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Nilai' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
