<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';

defineProps({
    users: {
        type: Array,
        required: true,
    },
});

const isCreating = ref(false);
const editingUser = ref(null);

const createForm = useForm({
    name: '',
    email: '',
    password: '',
});

const passwordForm = useForm({
    password: '',
    password_confirmation: '',
});

const submitCreate = () => {
    createForm.post('/users', {
        onSuccess: () => {
            createForm.reset();
            isCreating.value = false;
        },
    });
};

const submitPasswordUpdate = () => {
    if (!editingUser.value) return;
    passwordForm.put(`/users/${editingUser.value.id}/password`, {
        onSuccess: () => {
            passwordForm.reset();
            editingUser.value = null;
        },
    });
};

const deleteUser = (user) => {
    if (confirm(`Yakin ingin menghapus panitia "${user.name}"?`)) {
        router.delete(`/users/${user.id}`);
    }
};
</script>

<template>
    <Head title="Manajemen Akun Panitia" />

    <AuthenticatedLayout title="Akun Pengelola & Panitia Seleksi">
        <div class="space-y-4">
            <!-- Header Action -->
            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-500">Kelola kredensial akun panitia yang memiliki hak akses sistem SPK.</p>
                <button
                    @click="isCreating = !isCreating"
                    class="rounded bg-slate-900 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-slate-800"
                >
                    {{ isCreating ? 'Batal Tambah' : '+ Tambah Panitia' }}
                </button>
            </div>

            <!-- Form Tambah Panitia Baru -->
            <div v-if="isCreating" class="rounded border border-slate-200 bg-white p-4 shadow-2xs">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-700">Pendaftaran Panitia Baru</h3>
                <form @submit.prevent="submitCreate" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Nama Lengkap</label>
                        <input
                            v-model="createForm.name"
                            type="text"
                            required
                            class="mt-1 block w-full rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-slate-800 focus:outline-hidden"
                        />
                        <p v-if="createForm.errors.name" class="mt-1 text-[11px] text-rose-600">{{ createForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Alamat Email</label>
                        <input
                            v-model="createForm.email"
                            type="email"
                            required
                            class="mt-1 block w-full rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-slate-800 focus:outline-hidden"
                        />
                        <p v-if="createForm.errors.email" class="mt-1 text-[11px] text-rose-600">{{ createForm.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Kata Sandi (Min 8 Karakter)</label>
                        <input
                            v-model="createForm.password"
                            type="password"
                            required
                            class="mt-1 block w-full rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-slate-800 focus:outline-hidden"
                        />
                        <p v-if="createForm.errors.password" class="mt-1 text-[11px] text-rose-600">{{ createForm.errors.password }}</p>
                    </div>
                    <div class="sm:col-span-3 flex justify-end">
                        <button
                            type="submit"
                            :disabled="createForm.processing"
                            class="rounded bg-slate-900 px-4 py-1.5 text-xs font-medium text-white hover:bg-slate-800 disabled:opacity-50"
                        >
                            {{ createForm.processing ? 'Menyimpan...' : 'Simpan Akun' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Modal Ganti Password -->
            <div v-if="editingUser" class="rounded border border-amber-200 bg-amber-50/60 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-amber-900">Ubah Password: {{ editingUser.name }}</span>
                    <button @click="editingUser = null" class="text-xs text-slate-500 hover:text-slate-800">&times; Batal</button>
                </div>
                <form @submit.prevent="submitPasswordUpdate" class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Kata Sandi Baru</label>
                        <input
                            v-model="passwordForm.password"
                            type="password"
                            required
                            class="mt-1 block w-full rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-slate-800 focus:outline-hidden"
                        />
                        <p v-if="passwordForm.errors.password" class="mt-1 text-[11px] text-rose-600">{{ passwordForm.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Konfirmasi Kata Sandi Baru</label>
                        <input
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            required
                            class="mt-1 block w-full rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-slate-800 focus:outline-hidden"
                        />
                    </div>
                    <div class="sm:col-span-2 flex justify-end">
                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="rounded bg-slate-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-800 disabled:opacity-50"
                        >
                            {{ passwordForm.processing ? 'Menyimpan...' : 'Perbarui Sandi' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabel Daftar Akun Panitia -->
            <div class="overflow-hidden rounded border border-slate-200 bg-white shadow-2xs">
                <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-600">
                        <tr>
                            <th class="px-4 py-2.5">ID</th>
                            <th class="px-4 py-2.5">Nama Panitia</th>
                            <th class="px-4 py-2.5">Email</th>
                            <th class="px-4 py-2.5">Terdaftar Sejak</th>
                            <th class="px-4 py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-normal text-slate-800">
                        <tr v-for="u in users" :key="u.id" class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-2 font-mono text-slate-500">#{{ u.id }}</td>
                            <td class="px-4 py-2 font-medium text-slate-900">{{ u.name }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ u.email }}</td>
                            <td class="px-4 py-2 text-slate-500">{{ new Date(u.created_at).toLocaleDateString('id-ID') }}</td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <button
                                    @click="editingUser = u"
                                    class="text-indigo-600 hover:underline text-[11px]"
                                >
                                    Ubah Sandi
                                </button>
                                <button
                                    @click="deleteUser(u)"
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
    </AuthenticatedLayout>
</template>
