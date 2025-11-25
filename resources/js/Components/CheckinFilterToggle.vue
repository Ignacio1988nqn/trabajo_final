<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    // ref actual: 'plan' | 'hoy' | null
    mode: {
        type: String,
        default: null, // si viene null → se toma "hoy"
    },
    routeName: {
        type: String,
        default: "checkin.index",
    },
});

const options = [
    { key: "plan", label: "Planificadas" },
    { key: "hoy", label: "Solo hoy" },
];

const isActive = (key) => {
    // si no vino nada, el activo por defecto es "hoy"
    if (!props.mode) {
        return key === "hoy";
    }
    return props.mode === key;
};
</script>

<template>
    <div class="mb-4 inline-flex gap-1 rounded-2xl bg-slate-100/60 p-1">
        <Link
            v-for="opt in options"
            :key="opt.key"
            :href="route(routeName, { ref: opt.key })"
            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-xl border transition-all duration-150"
            :class="
                isActive(opt.key)
                    ? 'bg-[#0073b6] text-white border-[#0073b6] shadow-sm shadow-blue-500/30 hover:bg-[#1ed2d8]'
                    : 'bg-white text-[#0F3D3E] border-slate-200 hover:bg-slate-50'
            "
        >
            {{ opt.label }}
        </Link>
    </div>
</template>
