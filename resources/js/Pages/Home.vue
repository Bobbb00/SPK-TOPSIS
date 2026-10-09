<script setup>
defineProps({
    php: {
        type: String,
        required: true,
    },
    laravel: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <div class="mx-auto max-w-3xl px-6 py-10">
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">SPK Seleksi Beasiswa</p>
        <h1 class="mt-1 text-xl font-semibold text-slate-900">TOPSIS</h1>
        <p class="mt-3 text-sm text-slate-600">
            Scaffold siap. Engine dan modul panitia belum dihubungkan.
        </p>
        <dl class="mt-6 grid grid-cols-2 gap-3 border border-slate-200 bg-white text-sm">
            <div class="border-r border-slate-200 px-3 py-2">
                <dt class="text-xs text-slate-500">PHP</dt>
                <dd class="font-medium">{{ php }}</dd>
            </div>
            <div class="px-3 py-2">
                <dt class="text-xs text-slate-500">Laravel</dt>
                <dd class="font-medium">{{ laravel }}</dd>
            </div>
        </dl>
    </div>
</template>
