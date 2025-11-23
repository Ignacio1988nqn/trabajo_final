<template>

    <Head title="Estado de Habitaciones" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0a2 2 0 002-2V9m-2 12a2 2 0 01-2 2H7a2 2 0 01-2-2" />
                </svg>
                Estado de Habitaciones
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="bg-white shadow-2xl rounded-2xl overflow-hidden">

                    <!-- Filtros -->
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-700 p-6 text-white">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-semibold mb-2">Buscar por número</label>
                                <input v-model="numeroSeleccionado" @input="aplicarFiltros" type="text"
                                    placeholder="Ej. 101, 205..."
                                    class="w-full px-5 py-3 rounded-xl bg-white/20 backdrop-blur placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-white/30 transition text-white text-lg font-medium" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2">Estado</label>
                                <select v-model="estadoSeleccionado" @change="aplicarFiltros" class="w-full px-5 py-3 rounded-xl bg-white/20 backdrop-blur focus:outline-none focus:ring-4 focus:ring-white/30 transition text-white text-lg font-medium 
         [appearance:none] 
         [&_*]::text-white 
         [&_*]::bg-indigo-700 
         [&_option]:bg-indigo-700 
         [&_option]:text-white 
         [&_option:hover]:bg-indigo-600">

                                    <option value="todos">Todos los estados</option>
                                    <option value="disponible">Disponible</option>
                                    <option value="ocupada">Ocupada</option>
                                    <option value="limpieza">En limpieza</option>
                                    <option value="mantenimiento">En mantenimiento</option>
                                </select>

                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2">Tipo de habitación</label>
                                <select v-model="tipoSeleccionado" @change="aplicarFiltros" class="w-full px-5 py-3 rounded-xl bg-white/20 backdrop-blur focus:outline-none focus:ring-4 focus:ring-white/30 transition text-white text-lg font-medium 
         [appearance:none] 
         [&_*]::text-white 
         [&_*]::bg-indigo-700 
         [&_option]:bg-indigo-700 
         [&_option]:text-white 
         [&_option:hover]:bg-indigo-600">

                                    <option value="todos">Todos los tipos</option>
                                    <option value="simple">Simple</option>
                                    <option value="doble">Doble</option>
                                    <option value="triple">Triple</option>
                                    <option value="cuadruple">Cuádruple</option>
                                </select>

                            </div>
                        </div>
                    </div>

                    <div class="px-8 py-4 bg-gray-50 border-b">
                        <p class="text-lg font-semibold text-gray-700">
                            Mostrando <span class="text-indigo-600">{{ habitaciones.length }}</span> habitación{{
                                habitaciones.length !== 1 ? 'es' : '' }}
                        </p>
                    </div>

                    <div class="p-8 lg:p-10">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                            <RoomCard v-for="habitacion in habitaciones" :key="habitacion.id" :room="habitacion"
                                :asignacion-vigente="habitacion.asignacion_vigente"
                                :habitaciones-disponibles="habitacionesDisponibles" />
                        </div>

                        <!-- Sin resultados -->
                        <div v-if="!habitaciones.length" class="text-center py-20">
                            <svg class="mx-auto h-24 w-24 text-gray-300 mb-6" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <p class="text-2xl font-bold text-gray-600">No hay habitaciones</p>
                            <p class="text-gray-500 mt-2">Intentá con otros filtros</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import RoomCard from '@/Components/RoomCard.vue';

defineProps({
    habitaciones: Array,
    habitacionesDisponibles: { type: Array, default: () => [] },
    filtroEstado: { type: String, default: 'todos' },
    filtroTipo: { type: String, default: 'todos' },
    filtroNumero: { type: [String, null], default: null },
});

const numeroSeleccionado = ref('');
const estadoSeleccionado = ref('todos');
const tipoSeleccionado = ref('todos');

const aplicarFiltros = () => {
    router.get(route('habitaciones.index'), {
        numero: numeroSeleccionado.value || null,
        estado: estadoSeleccionado.value,
        tipo: tipoSeleccionado.value,
    }, { preserveState: true, preserveScroll: true });
};

watch([numeroSeleccionado, estadoSeleccionado, tipoSeleccionado], aplicarFiltros, { immediate: true });
</script>