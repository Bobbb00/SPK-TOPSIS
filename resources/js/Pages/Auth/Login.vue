<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const showPassword = ref(false);

const form = useForm({
    email: 'admin@spk.test',
    password: 'password',
    remember: true,
});

const fillDefaultAccount = () => {
    form.email = 'admin@spk.test';
    form.password = 'password';
    form.clearErrors();
};

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk Akun Panitia — SPK TOPSIS" />

    <div class="relative min-h-screen w-full flex items-center justify-center bg-[#f4f6fb] px-4 py-8 antialiased selection:bg-blue-600 selection:text-white">
        <!-- Ambient Background Glows -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -left-40 h-96 w-96 rounded-full bg-blue-300/30 blur-3xl"></div>
            <div class="absolute -bottom-40 -right-40 h-96 w-96 rounded-full bg-indigo-300/30 blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[500px] w-[600px] rounded-full bg-blue-100/40 blur-3xl"></div>
        </div>

        <!-- Main Card Container -->
        <div class="relative z-10 w-full max-w-4xl overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xl shadow-slate-200/60 lg:grid lg:grid-cols-12">
            
            <!-- Left Branding & Feature Column (Desktop) -->
            <div class="relative flex flex-col justify-between bg-linear-to-br from-[#00236f] via-[#09328e] to-[#1e3a8a] p-8 text-white lg:col-span-5 lg:p-10">
                <!-- Background decorative pattern -->
                <div class="pointer-events-none absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>

                <!-- Top Brand -->
                <div class="relative z-10">
                    <div class="inline-flex items-center gap-2.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold backdrop-blur-md text-blue-100 border border-white/10 mb-6">
                        <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Portal Resmi Seleksi 2026/2027
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-[#00236f] shadow-md shadow-blue-950/30">
                            <span class="material-symbols-outlined text-[26px]">school</span>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold tracking-tight text-white leading-tight">SPK-TOPSIS</h1>
                            <p class="text-xs font-medium text-blue-200">Kampus Merdeka / PTN</p>
                        </div>
                    </div>

                    <h2 class="mt-8 text-xl font-bold tracking-tight text-white leading-snug">
                        Sistem Pendukung Keputusan Seleksi Beasiswa Mahasiswa
                    </h2>
                    <p class="mt-2.5 text-xs leading-relaxed text-blue-100/90">
                        Penentuan penerima beasiswa prestasi & afirmasi berbasis multi-kriteria menggunakan metode matematis TOPSIS yang objektif, transparan, dan teruji.
                    </p>

                    <!-- Feature Badges -->
                    <div class="mt-8 space-y-3">
                        <div class="flex items-center gap-3 text-xs text-blue-100">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-white/15 text-white">
                                <span class="material-symbols-outlined text-[15px]">verified</span>
                            </span>
                            <span>Kalkulasi matriks keputusan presisi matematis</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-blue-100">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-white/15 text-white">
                                <span class="material-symbols-outlined text-[15px]">psychology</span>
                            </span>
                            <span>Penjelasan keputusan naratif berbasis AI</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-blue-100">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-white/15 text-white">
                                <span class="material-symbols-outlined text-[15px]">shield</span>
                            </span>
                            <span>Otentikasi aman khusus panitia seleksi</span>
                        </div>
                    </div>
                </div>

                <!-- Bottom Accreditation -->
                <div class="relative z-10 mt-10 pt-6 border-t border-white/15 text-[11px] text-blue-200/80">
                    Biro Administrasi Kemahasiswaan & Akademik
                </div>
            </div>

            <!-- Right Form Column -->
            <div class="flex flex-col justify-between p-8 sm:p-10 lg:col-span-7">
                <div>
                    <!-- Form Header -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold tracking-tight text-slate-900">
                                Masuk Akun Panitia
                            </h2>
                            <span class="rounded-md bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-[#00236f]">
                                Akses Internal
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            Masukkan email dan kata sandi yang terdaftar untuk mengelola seleksi.
                        </p>
                    </div>

                    <!-- Login Form -->
                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Email Input -->
                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Alamat Email Panitia
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <span class="material-symbols-outlined text-[18px]">mail</span>
                                </span>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autofocus
                                    placeholder="nama@spk.test"
                                    class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pr-3 pl-9.5 text-xs text-slate-900 placeholder:text-slate-400 transition-all focus:border-[#00236f] focus:outline-hidden focus:ring-2 focus:ring-[#00236f]/15"
                                    :class="{ 'border-rose-500 ring-2 ring-rose-500/15': form.errors.email }"
                                />
                            </div>
                            <p v-if="form.errors.email" class="mt-1.5 flex items-center gap-1 text-[11px] font-medium text-rose-600">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Password Input -->
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Kata Sandi
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <span class="material-symbols-outlined text-[18px]">lock</span>
                                </span>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    placeholder="••••••••"
                                    class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pr-10 pl-9.5 text-xs text-slate-900 placeholder:text-slate-400 transition-all focus:border-[#00236f] focus:outline-hidden focus:ring-2 focus:ring-[#00236f]/15"
                                    :class="{ 'border-rose-500 ring-2 ring-rose-500/15': form.errors.password }"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    tabindex="-1"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition"
                                >
                                    <span class="material-symbols-outlined text-[18px]">
                                        {{ showPassword ? 'visibility_off' : 'visibility' }}
                                    </span>
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="mt-1.5 flex items-center gap-1 text-[11px] font-medium text-rose-600">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-between pt-1">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input
                                    v-model="form.remember"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-slate-300 text-[#00236f] focus:ring-[#00236f]/20 cursor-pointer"
                                />
                                <span class="text-xs font-medium text-slate-600">Ingat sesi di perangkat ini</span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="relative flex w-full items-center justify-center gap-2 rounded-lg bg-[#00236f] py-2.5 px-4 text-xs font-semibold text-white shadow-md shadow-blue-900/20 transition-all hover:bg-[#1e3a8a] active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer"
                        >
                            <span v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            <span class="material-symbols-outlined text-[18px]" v-else>login</span>
                            <span>{{ form.processing ? 'Memverifikasi Kredensial...' : 'Masuk ke Dashboard Sistem' }}</span>
                        </button>
                    </form>

                    <!-- Quick Demo Credentials Box -->
                    <div class="mt-6 rounded-xl border border-blue-100 bg-blue-50/60 p-3.5 text-xs">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-1.5 font-semibold text-[#00236f]">
                                <span class="material-symbols-outlined text-[16px]">info</span>
                                <span>Akun Bawaan Panitia:</span>
                            </div>
                            <button
                                type="button"
                                @click="fillDefaultAccount"
                                class="text-[11px] font-semibold text-blue-700 hover:text-blue-900 underline underline-offset-2 cursor-pointer transition"
                            >
                                Pasang Otomatis
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-600 font-mono bg-white/80 rounded-lg p-2 border border-blue-100/70">
                            <div><span class="text-slate-400">Email:</span> admin@spk.test</div>
                            <div><span class="text-slate-400">Pass:</span> password</div>
                        </div>
                    </div>
                </div>

                <!-- Footer: Link to Public Announcement -->
                <div class="mt-8 pt-5 border-t border-slate-200/80">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
                        <div class="text-[11px] text-slate-500">
                            Mahasiswa / Pendaftar Beasiswa?
                        </div>
                        <Link
                            href="/pengumuman"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 hover:text-blue-700 transition shadow-2xs"
                        >
                            <span class="material-symbols-outlined text-[16px] text-blue-600">campaign</span>
                            <span>Cek Hasil Seleksi Publik &rarr;</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footnote Copyright -->
        <div class="absolute bottom-3 text-center text-[11px] text-slate-400">
            &copy; 2026 Biro Administrasi Kemahasiswaan & Akademik. SPK TOPSIS Engine v2.4
        </div>
    </div>
</template>
