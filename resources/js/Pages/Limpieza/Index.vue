<template>

    <Head title="Limpieza de Habitaciones" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Limpieza de Habitaciones
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <div class="flex flex-wrap gap-6 mb-6">
                            <div>
                                <label for="numeroFiltro" class="block text-sm font-medium text-gray-700">
                                    Buscar por número:
                                </label>
                                <input id="numeroFiltro" v-model="numeroSeleccionado" @input="aplicarFiltro" type="text"
                                    class="mt-1 block w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Ej. 101" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" style="padding-bottom: 80px;">
                            <RoomCardLimpieza v-for="habitacion in habitacionesFiltradas" :key="habitacion.id"
                                :habitacion="habitacion" />
                        </div>

                        <p v-if="habitacionesFiltradas.length === 0" class="text-gray-500">
                            No hay habitaciones en limpieza que coincidan con el filtro.
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import RoomCardLimpieza from '@/Components/RoomCardLimpieza.vue'
import { ref, computed } from 'vue'

// Props desde Inertia
const props = defineProps({
    habitaciones: {
        type: Array,
        required: true,
    },
})

// Estado local
const numeroSeleccionado = ref('')

// Filtro por número
const habitacionesFiltradas = computed(() => {
    if (!numeroSeleccionado.value) return props.habitaciones
    const filtro = numeroSeleccionado.value.toString().toLowerCase()
    return props.habitaciones.filter(h =>
        h.numero.toString().toLowerCase().includes(filtro)
    )
})

function aplicarFiltro() {
}
</script>
