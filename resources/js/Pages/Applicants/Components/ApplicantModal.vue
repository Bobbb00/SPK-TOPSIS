<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    applicant: Object,
});

const emit = defineEmits(['close']);

const form = useForm({
    nim: '',
    name: '',
    study_program: '',
});

watch(
    () => props.applicant,
    (val) => {
        if (val) {
            form.nim = val.nim;
            form.name = val.name;
            form.study_program = val.study_program;
        } else {
            form.reset();
        }
    },
    { immediate: true }
);

const submit = () => {
    if (props.applicant) {
        form.put(`/applicants/${props.applicant.id}`, {
            onSuccess: () => emit('close'),
        });
    } else {
        form.post('/applicants', {
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
                    {{ applicant ? 'Ubah Data Pendaftar' : 'Tambah Pendaftar Beasiswa' }}
                </h3>
                <button @click="emit('close')" class="text-xs text-slate-400 hover:text-slate-700">&times;</button>
            </div>

            <form @submit.prevent="submit" class="mt-4 space-y-3">
                <div>
                    <label class="block text-xs font-medium text-slate-700">Nomor Induk Mahasiswa (NIM)</label>
                    <input
                        v-model="form.nim"
                        type="text"
                        required
                        placeholder="Contoh: 2024001"
                        class="mt-1 block w-full rounded border border-slate-300 px-3 py-1.5 font-mono text-xs focus:border-slate-800 focus:outline-hidden"
                        :class="{ 'border-rose-500': form.errors.nim }"
                    />
                    <p v-if="form.errors.nim" class="mt-1 text-[11px] text-rose-600">{{ form.errors.nim }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700">Nama Lengkap Mahasiswa</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Contoh: Ahmad Fauzi"
                        class="mt-1 block w-full rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-slate-800 focus:outline-hidden"
                        :class="{ 'border-rose-500': form.errors.name }"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-[11px] text-rose-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700">Program Studi</label>
                    <input
                        v-model="form.study_program"
                        type="text"
                        required
                        placeholder="Contoh: Teknik Informatika"
                        class="mt-1 block w-full rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-slate-800 focus:outline-hidden"
                        :class="{ 'border-rose-500': form.errors.study_program }"
                    />
                    <p v-if="form.errors.study_program" class="mt-1 text-[11px] text-rose-600">{{ form.errors.study_program }}</p>
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
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
