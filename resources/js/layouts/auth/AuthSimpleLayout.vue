<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-slate-50 px-4 py-10"
    >
        <div class="mb-8 text-center">
            <Link
                href="/dashboard"
                class="inline-block"
            >
                <h1 class="text-3xl font-bold text-slate-900">
                    APONWORKS
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Your Personal AI Secretary
                </p>
            </Link>
        </div>

        <div class="w-full max-w-md">
            <slot />
        </div>
    </div>
</template>