<script setup>
import { toRef } from "vue";
import { useCountUp } from "@/composables/useCountUp";

const props = defineProps({
    label: String,
    chip: String,
    value: { type: [Number, String], default: 0 }, // valor real
    suffix: { type: String, default: "" }, // ej: "%"
    subtitle: String,
});

// tomamos la prop como ref para pasársela al composable
const valueRef = toRef(props, "value");

// hook que hace la magia del conteo
const { display } = useCountUp(valueRef, 600);
</script>

<template>
    <article class="rounded-2xl border border-black/5 bg-white p-5 shadow-sm">
        <div class="text-xs uppercase tracking-widest text-neutral-400">
            {{ label }}
        </div>

        <div class="mt-2 flex items-end justify-between">
            <p class="text-3xl font-semibold text-[#0F3D3E]">
                {{ display }}
                <span v-if="suffix">{{ suffix }}</span>
            </p>
            <span
                class="rounded-lg bg-[#C69C6D]/10 px-2 py-1 text-xs font-medium text-[#8A6A47]"
            >
                {{ chip }}
            </span>
        </div>

        <p class="mt-2 text-sm text-neutral-500">
            {{ subtitle }}
        </p>
    </article>
</template>
