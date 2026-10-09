<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    criteria: Object,
});

const emit = defineEmits(['close']);

const form = useForm({
    code: '',
    name: '',
    type: 'benefit',
    weight: 0.20,
});

watch(
    () => props.criteria,
    (val) => {
        if (val) {
            form.code = val.code;
            form.name = val.name;
            form.type = val.type;
            form.weight = val.weight;
        } else {
            form.reset();
            form.type = 'benefit';
            form.weight = 0.20;
        }
    },
    { immediate: true }
);

const submit = () => {
    if (props.criteria) {
        form.put(`/criteria/${props.criteria.id}`, {
            onSuccess: () => emit('close'),
        });
    } else {
        form.post('/criteria', {
            onSuccess: () => {
                form.reset();
                emit('close');
            },
        });
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-md rounded border border-slate-300 bg-white p-5 shadow-lg">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-900">
                    {{ criteria ? 'Ubah Data Kriteria' : 'Tambah Kriteria Seleksi' }}
                </h3>
                <button @click="emit('close')" class="text-xs text-slate-400 hover:text-slate-700">&times;</button>
            </div>

            <form @submit.prevent="submit" class="mt-4 space-y-3">
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Kode Kriteria</label>
                        <input
                            v-model="form.code"
                            type="text"
                            required
                            placeholder="C1"
                            class="mt-1 block w-full rounded border border-slate-300 px-3 py-1.5 font-mono text-xs focus:border-slate-800 focus:outline-hidden"
                            :class="{ 'border-rose-500': form.errors.code }"
                        />
                        <p v-if="form.errors.code" class="mt-1 text-[11px] text-rose-600">{{ form.errors.code }}</p>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-700">Sifat Kriteria</label>
                        <select
                            v-model="form.type"
                            class="mt-1 block w-full rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-slate-800 focus:outline-hidden"
                        >
                            <option value="benefit">Benefit (Nilai tinggi lebih baik)</option>
                            <option value="cost">Cost (Nilai rendah lebih baik)</option>
                        </select>
                        <p v-if="form.errors.type" class="mt-1 text-[11px] text-rose-600">{{ form.errors.type }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700">Nama Lengkap Kriteria</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Contoh: Indeks Prestasi Kumulatif (IPK)"
                        class="mt-1 block w-full rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-slate-800 focus:outline-hidden"
                        :class="{ 'border-rose-500': form.errors.name }"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-[11px] text-rose-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700">
                        Bobot Kepentingan (W: 0.01 - 1.00)
                    </label>
                    <div class="mt-1 flex items-center space-x-2">
                        <input
                            v-model.number="form.weight"
                            type="number"
                            step="0.01"
                            min="0.01"
                            max="1.00"
                            required
                            class="block w-full rounded border border-slate-300 px-3 py-1.5 font-mono text-xs focus:border-slate-800 focus:outline-hidden"
                            :class="{ 'border-rose-500': form.errors.weight }"
                        />
                        <span class="rounded bg-slate-100 px-2 py-1.5 font-mono text-xs font-medium text-slate-600">
                            {{ Math.round((form.weight || 0) * 100) }}%
                        </span>
                    </div>
                    <p v-if="form.errors.weight" class="mt-1 text-[11px] text-rose-600">{{ form.errors.weight }}</p>
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
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Kriteria' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
