<template>
    <Head title="Consultar Gastos de Reservas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Consultar Gastos de Reservas
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <table class="min-w-full bg-white border">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 border">ID</th>
                                    <th class="px-4 py-2 border">Cliente</th>
                                    <th class="px-4 py-2 border">Fecha Inicio</th>
                                    <th class="px-4 py-2 border">Estado</th>
                                    <th class="px-4 py-2 border">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="reserva in reservas" :key="reserva.id">
                                    <td class="px-4 py-2 border">{{ reserva.id }}</td>
                                    <td class="px-4 py-2 border">{{ reserva.cliente || 'Huésped no encontrado' }}</td>
                                    <td class="px-4 py-2 border">{{ formatFecha(reserva.fecha_inicio) }}</td>
                                    <td class="px-4 py-2 border">{{ reserva.estado }}</td>
                                    <td class="px-4 py-2 border">
                                        <Link :href="route('gastos.show', reserva.id)"
                                            class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                            Consultar
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    reservas: {
        type: Array,
        default: () => [],
    },
});

const formatFecha = (fecha) => {
    if (!fecha) return 'Fecha no disponible';
    return new Date(fecha).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>