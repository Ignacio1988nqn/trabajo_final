<template>

    <Head title="Estado General de Habitaciones" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Estado General de Habitaciones
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex flex-wrap gap-6 mb-6">
                            <div>
                                <label for="estadoFiltro" class="block text-sm font-medium text-gray-700">
                                    Filtrar por estado:
                                </label>
                                <select id="estadoFiltro" :value="filtroEstado" @change="actualizarFiltro($event)"
                                    class="mt-1 block w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="todos">Todos</option>
                                    <option value="disponible">Disponible</option>
                                    <option value="ocupada">Ocupada</option>
                                    <option value="mantenimiento">Mantenimiento</option>
                                    <option value="limpieza">Limpieza</option>
                                </select>
                            </div>

                            <div>
                                <label for="tipoFiltro" class="block text-sm font-medium text-gray-700">
                                    Filtrar por tipo:
                                </label>
                                <select id="tipoFiltro" :value="filtroTipo" @change="actualizarFiltroTipo($event)"
                                    class="mt-1 block w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="todos">Todos</option>
                                    <option value="simple">Simple</option>
                                    <option value="doble">Doble</option>
                                    <option value="triple">Triple</option>
                                    <option value="cuadruple">Cuádruple</option>
                                </select>
                            </div>
                        </div>                  

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <RoomCard v-for="habitacion in habitaciones" :key="habitacion.id" :room="habitacion" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { Head, router } from '@inertiajs/vue3';
import RoomCard from '@/Components/RoomCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

export default {
    name: 'RoomStatus',
    components: {
        RoomCard,
        AuthenticatedLayout,
        Head,
    },
    props: {
        habitaciones: {
            type: Array,
            required: true,
        },
        filtroEstado: {
            type: String,
            default: 'todos',
        },
        filtroTipo: {
            type: String,
            default: 'todos',
        },
    },
    data() {
        return {
            estadoSeleccionado: this.filtroEstado,
            tipoSeleccionado: this.filtroTipo,
        };
    },
    methods: {
        actualizarFiltro(event) {
            this.estadoSeleccionado = event.target.value;
            this.$emit('update:filtroEstado', this.estadoSeleccionado);

            this.aplicarFiltros();
        },
        actualizarFiltroTipo(event) {
            this.tipoSeleccionado = event.target.value;
            this.$emit('update:filtroTipo', this.tipoSeleccionado);

            this.aplicarFiltros();
        },
        aplicarFiltros() {
            router.get(
                route('habitaciones.index'),
                {
                    estado: this.estadoSeleccionado,
                    tipo: this.tipoSeleccionado,
                },
                { preserveState: true, preserveScroll: true }
            );
        },
    },
};
</script>
