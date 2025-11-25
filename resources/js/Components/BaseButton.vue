<script setup>
import { Link } from "@inertiajs/vue3";
const props = defineProps({
    to: { type: String, default: null },
    icon: { type: Boolean, default: true },
    type: { type: String, default: "button" },
});
</script>
<template>
    <component
        :is="to ? Link : 'button'"
        :href="to ? route(to) : null"
        :type="to ? null : type"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#0073b6] text-white font-medium rounded-xl shadow-md shadow-blue-500/20 hover:bg-[#1ed2d8] hover:shadow-lg hover:shadow-blue-500/30 active:scale-[0.97] transition-all focus:outline-none focus:ring-2 focus:ring-blue-400/40"
    >
        <!-- Ícono opcional -->
        <svg
            v-if="icon"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.8"
            stroke="currentColor"
            class="w-5 h-5"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 1115 0v.75H4.5v-.75z"
            />
        </svg>
        <slot />
    </component>
</template>
