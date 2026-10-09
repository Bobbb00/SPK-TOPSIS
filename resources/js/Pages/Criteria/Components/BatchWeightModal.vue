<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    criterias: Array,
});

const emit = defineEmits(['close']);

const form = useForm({
    weights: [],
});

watch(
    () => props.criterias,
    (val) => {
        if (val) {
            form.weights = val.map((c) => ({
                id: c.id,
                code: c.code,
                name: c.name,
                weight: c.weight,
            }));
        }
    },
    { immediate: true }
);

const currentSum = computed(() => {
    const sum = form.weights.reduce((acc, curr) => acc + (Number(curr.weight) || 0), 0);
    return Math.round(sum * 1000) / 1000;
});

const isValidSum = computed(() => Math.abs(currentSum.value - 1.0) < 0.001);

const normalizeWeights = () => {
    const rawSum = form.weights.reduce((acc, curr) => acc + (Number(curr.weight) || 0), 0);
    if (rawSum <= 0) return;

    let distributed = 0;
    const count = form.weights.length;

    form.weights.forEach((item, idx) => {
        if (idx === count - 1) {
            item.weight = Math.round((1.0 - distributed) * 100) / 100;
        } else {
            const normalized = Math.round(((Number(item.weight) || 0) / rawSum) * 100) / 100;
            item.weight = normalized;
            distributed += normalized;
        }
    });
};

const submit = () => {
    if (!isValidSum.value) return;

    form.post('/criteria/update-weights', {
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
                        Atur Bobot Seluruh Kriteria
                    </h3>
                    <p class="text-[11px] text-slate-500">Total akumulasi bobot wajib tepat bernilai 1.00 (100%).</p>
                </div>
                <button @click="emit('close')" class="text-xs text-slate-400 hover:text-slate-700">&times;</button>
            </div>

            <form @submit.prevent="submit" class="mt-4 space-y-4">
                <div class="max-h-72 overflow-y-auto space-y-2.5 pr-1">
                    <div
                        v-for="(item, idx) in form.weights"
                        :key="item.id"
                        class="flex items-center justify-between rounded border border-slate-200 bg-slate-50/60 px-3 py-2 text-xs"
                    >
                        <div class="min-w-0 pr-3">
                            <span class="font-mono font-bold text-slate-900">{{ item.code }}</span>
                            <span class="ml-2 text-slate-600">{{ item.name }}</span>
                        </div>
                        <div class="flex items-center space-x-2 shrink-0">
                            <input
                                v-model.number="item.weight"
                                type="number"
                                step="0.01"
                                min="0.01"
                                max="1.00"
                                required
                                class="w-20 rounded border border-slate-300 px-2 py-1 font-mono text-xs text-right focus:border-slate-800 focus:outline-hidden"
                            />
                            <span class="w-10 text-right font-mono text-[11px] text-slate-500">
                                {{ Math.round((item.weight || 0) * 100) }}%
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Live Sum Status & Helper -->
                <div class="flex items-center justify-between rounded border p-3 text-xs"
                    :class="isValidSum ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-rose-200 bg-rose-50 text-rose-900'"
                >
                    <div>
                        <div class="font-semibold">
                            Total Bobot: <span class="font-mono">{{ currentSum }}</span> ({{ Math.round(currentSum * 100) }}%)
                        </div>
                        <div class="text-[11px]">
                            <span v-if="isValidSum">Sesuai standar TOPSIS (Total pas 1.00).</span>
                            <span v-else-if="currentSum < 1.0">Kurang {{ (1.0 - currentSum).toFixed(2) }} untuk mencapai 1.00.</span>
                            <span v-else>Kelebihan {{ (currentSum - 1.0).toFixed(2) }} dari batas 1.00.</span>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="normalizeWeights"
                        class="rounded border border-slate-300 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Normalisasi Otomatis
                    </button>
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
                        :disabled="!isValidSum || form.processing"
                        class="rounded bg-slate-900 px-4 py-1.5 text-xs font-medium text-white hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Terapkan Bobot (Wajib 1.00)' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
