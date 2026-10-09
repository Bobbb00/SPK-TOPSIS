<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        default: 'SPK TOPSIS',
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash || {});
const activeQuota = computed(() => page.props.active_quota);

const navItems = [
    { name: 'Dashboard & Ringkasan', icon: 'dashboard', href: '/dashboard', active: page.url === '/dashboard' || page.url === '/' },
    { name: 'Kriteria & Bobot', icon: 'tune', href: '/criteria', active: page.url.startsWith('/criteria') },
    { name: 'Data Pendaftar', icon: 'how_to_reg', href: '/applicants', active: page.url.startsWith('/applicants') },
    { name: 'Penilaian Pendaftar', icon: 'rate_review', href: '/evaluations', active: page.url.startsWith('/evaluations') },
    { name: 'Hasil & Rekomendasi', icon: 'emoji_events', href: '/ranking', active: page.url.startsWith('/ranking') },
    { name: 'Akun Panitia', icon: 'manage_accounts', href: '/users', active: page.url.startsWith('/users') },
];
</script>

<template>
    <div class="flex h-screen bg-[#faf8ff] font-sans text-slate-800 antialiased overflow-hidden">
        <!-- Sidebar -->
        <aside class="flex w-72 flex-col justify-between border-r border-slate-200/80 bg-[#f4f5fd] z-30">
            <div>
                <!-- Brand Header -->
                <div class="flex h-16 items-center gap-3 border-b border-slate-200/70 bg-white px-5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#00236f] text-white shadow-xs">
                        <span class="material-symbols-outlined text-[20px]">school</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold tracking-tight text-[#00236f] leading-none">SPK-TOPSIS</span>
                        <span class="text-[11px] font-medium text-slate-500 mt-1">Kampus Merdeka / PTN</span>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="px-4 py-4">
                    <div class="px-2 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        Menu Seleksi
                    </div>
                    <nav class="space-y-1">
                        <Link
                            v-for="item in navItems"
                            :key="item.name"
                            :href="item.href"
                            :class="[
                                item.active
                                    ? 'bg-[#1e3a8a] text-white shadow-xs font-semibold'
                                    : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900 font-medium',
                                'flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs transition-colors'
                            ]"
                        >
                            <span class="material-symbols-outlined text-[19px]">{{ item.icon }}</span>
                            <span>{{ item.name }}</span>
                        </Link>
                    </nav>
                </div>
            </div>

            <!-- Bottom Computing Card & User Footer -->
            <div class="p-4 space-y-3">
                <div class="rounded-xl border border-slate-200/80 bg-white p-3 shadow-2xs">
                    <div class="flex items-center justify-between text-[11px] font-medium text-slate-500">
                        <span>Sistem Komputasi</span>
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                    </div>
                    <div class="mt-1.5 flex items-center gap-1.5 font-mono text-xs font-semibold text-slate-700">
                        <span class="material-symbols-outlined text-[16px] text-blue-600">memory</span>
                        <span>TOPSIS Core v2.4</span>
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-xl bg-white p-3 border border-slate-200/80 shadow-2xs">
                    <div class="min-w-0 pr-2">
                        <p class="truncate text-xs font-semibold text-slate-800">{{ user?.name || 'Panitia' }}</p>
                        <p class="truncate text-[10px] text-slate-500">{{ user?.role === 'admin' ? 'Admin Sistem' : 'Panitia Verifikator' }}</p>
                    </div>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] font-medium text-slate-600 transition hover:bg-red-50 hover:text-red-600 hover:border-red-200"
                    >
                        Keluar
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Top App Bar -->
            <header class="flex h-16 items-center justify-between border-b border-slate-200/80 bg-white/90 backdrop-blur-md px-6 z-20">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 border border-blue-100 text-xs font-medium text-blue-900">
                        <span class="material-symbols-outlined text-[16px] text-blue-600">date_range</span>
                        <span>{{ activeQuota?.period ? `Periode ${activeQuota.period}` : 'Periode Genap 2025/2026' }}</span>
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Aktif</span>
                    </div>
                    <div class="hidden xl:flex items-center gap-1 text-xs text-slate-500">
                        <span class="material-symbols-outlined text-[16px]">domain</span>
                        <span>Institut Teknologi & Sains Nasional</span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <a
                        href="/pengumuman"
                        target="_blank"
                        class="hidden md:inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100 transition"
                        title="Buka portal pengumuman publik untuk mahasiswa di tab baru"
                    >
                        <span class="material-symbols-outlined text-[15px] text-blue-600">open_in_new</span>
                        <span>Portal Mahasiswa</span>
                    </a>
                    <div class="flex items-center gap-2.5">
                        <div class="flex flex-col items-end">
                            <span class="text-xs font-semibold text-slate-800 leading-tight">{{ user?.name || 'Panitia Beasiswa' }}</span>
                            <span class="rounded bg-teal-50 px-1.5 py-0.5 text-[10px] font-bold text-teal-700">Panitia Verifikator</span>
                        </div>
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#00236f] text-white">
                            <span class="material-symbols-outlined text-[18px]">person</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Page Content -->
            <main class="flex-1 overflow-y-auto p-6 md:p-8">
                <!-- Flash Notification Banner -->
                <div v-if="flash.success" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-xs text-emerald-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-emerald-600">check_circle</span>
                    <span>{{ flash.success }}</span>
                </div>
                <div v-if="flash.error" class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs text-rose-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-rose-600">error</span>
                    <span>{{ flash.error }}</span>
                </div>

                <slot />
            </main>

            <!-- Footer -->
            <footer class="flex h-10 items-center justify-between border-t border-slate-200/80 bg-white px-6 text-[11px] text-slate-500">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">terminal</span>Stack: Laravel 12 + Vue 3 Inertia</span>
                    <span>•</span>
                    <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px] text-emerald-600">bolt</span>Status Engine: Aktif</span>
                    <span>•</span>
                    <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">database</span>Database: MySQL 8.0</span>
                </div>
                <div>© 2025 Biro Administrasi Kemahasiswaan & Akademik</div>
            </footer>
        </div>
    </div>
</template>

