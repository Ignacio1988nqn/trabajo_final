<script setup>
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'

// Props desde Inertia
const props = defineProps({
    porcentajeOcupacion: {
        type: [Object, null],
        default: null,
    },
    habitacionesDisponibles: {
        type: [Number, null],
        default: null,
    },
})

// Estado local
const form = ref({
    fechaInicio: '',
    fechaFin: '',
})

function consultarOcupacion() {
    router.get(route('estadisticas.index'), {
        fecha_inicio: form.value.fechaInicio,
        fecha_fin: form.value.fechaFin,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}
</script>
<!-- <script setup>
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'

// Props desde Inertia
const props = defineProps({
    porcentajeOcupacion: {
        type: [Number, Object, null],
        default: null,
    },
})

// Estado local
const form = ref({
    fechaInicio: '',
    fechaFin: '',
})

function consultarOcupacion() {
    router.get(route('estadisticas.index'), {
        fecha_inicio: form.value.fechaInicio,
        fecha_fin: form.value.fechaFin,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

</script> -->
<template>

    <Head title="Estadísticas de Ocupación" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Estadísticas de Ocupación
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Formulario para consultar ocupación -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700">Consultar Porcentaje de Ocupación</h3>
                            <section class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-2">
                                <KpiCard label="Ocupación" chip="Rango" :value="porcentajeOcupacion !== null && !porcentajeOcupacion.error ? `${(porcentajeOcupacion.ocupada /
                                    porcentajeOcupacion.total) * 100}%` : '— %'"
                                    subtitle="Porcentaje de habitaciones ocupadas." />
                                <KpiCard label="Disponibles" chip="Habitaciones"
                                    :value="habitacionesDisponibles !== null ? String(habitacionesDisponibles) : '—'"
                                    subtitle="Listas para asignación." />
                            </section>

                            <form @submit.prevent="consultarOcupacion" class="flex flex-wrap gap-4 mt-2">

                                <div class="self-end">
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Consultar
                                    </button>
                                </div>
                            </form>
                            <!-- Mostrar resultado o error -->
                            <div v-if="porcentajeOcupacion !== null" class="mt-4">
                                <p v-if="porcentajeOcupacion.error" class="text-red-600">
                                    Error: {{ porcentajeOcupacion.error }}
                                </p>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
export default {
    components: {
        KpiCard: {
            props: { label: String, chip: String, value: String, subtitle: String },
            template: `
        <article class="rounded-2xl border border-black/5 bg-white p-5 shadow-sm">
          <div class="text-xs uppercase tracking-widest text-neutral-400">{{ label }}</div>
          <div class="mt-2 flex items-end justify-between">
            <p class="text-3xl font-semibold text-[#0F3D3E]">{{ value }}</p>
            <span class="rounded-lg bg-[#C69C6D]/10 px-2 py-1 text-xs font-medium text-[#8A6A47]">{{ chip }}</span>
          </div>
          <p class="mt-2 text-sm text-neutral-500">{{ subtitle }}</p>
        </article>
      `
        }
    }
}
</script>