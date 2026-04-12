<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-[#F5F6FA] flex flex-col items-center justify-center px-4 py-12">
        <div class="mb-8">
            <Link href="/" class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-[#4A6CF7] flex items-center justify-center">
                    <span class="text-white text-lg font-bold">K</span>
                </div>
                <span class="text-xl font-bold text-gray-900">Kontak</span>
            </Link>
        </div>
        <div class="w-full max-w-md panel p-8">
            <slot />
        </div>
    </div>
</template>
