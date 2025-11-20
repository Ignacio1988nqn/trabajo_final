<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    title: { type: String, required: true },
    desc: { type: String, required: true },
    to: { type: String, required: true },
});
</script>

<template>
    <Link
        :href="to"
        class="group block rounded-2xl border border-black/5 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#C69C6D]/40"
    >
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-[#0F3D3E]">{{ title }}</h3>
            <span
                class="rounded-lg bg-[#0F3D3E] px-2 py-1 text-xs text-white transition group-hover:opacity-90"
            >
                Abrir
            </span>
        </div>
        <p class="mt-2 text-sm text-neutral-500">
            {{ desc }}
        </p>
    </Link>
</template>
