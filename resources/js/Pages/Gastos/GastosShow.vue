<template>
    <Head title="Gastos de Reserva" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Gastos de Reserva #{{ reserva.id || 'N/A' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="reserva.estado === 'checkin' && reserva.id" class="mb-6">
                            <Link :href="route('gastos.create', reserva.id)"
                                class="inline-block bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors duration-200">
                            Cargar Gasto
                            </Link>
                        </div>
                        <div v-else-if="reserva.estado !== 'checkin'" class="mb-6">
                            <p class="text-gray-500 text-sm italic">
                                No se pueden cargar gastos hasta que la reserva esté en estado 'checkin'.
                            </p>
                        </div>

                        <!-- Tabla de gastos -->
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Lista de Gastos</h3>
                        <table class="min-w-full bg-white border border-gray-200 rounded-md">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 border-b text-left text-sm font-semibold text-gray-700">
                                        Descripción
                                    </th>
                                    <th class="px-4 py-2 border-b text-left text-sm font-semibold text-gray-700">Monto
                                    </th>
                                    <th class="px-4 py-2 border-b text-left text-sm font-semibold text-gray-700">Fecha
                                    </th>
                                    <th class="px-4 py-2 border-b text-left text-sm font-semibold text-gray-700">Tipo
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="gasto in gastos" :key="gasto.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b text-gray-900">{{ gasto.descripcion || 'Sin descripción' }}
                                    </td>
                                    <td class="px-4 py-2 border-b text-gray-900">${{ gasto.monto || '0.00' }}</td>
                                    <td class="px-4 py-2 border-b text-gray-900">{{ formatFecha(gasto.fecha) }}</td>
                                    <td class="px-4 py-2 border-b text-gray-900">{{ gasto.tipo || 'Sin tipo' }}</td>
                                </tr>
                                <tr v-if="!gastos || gastos.length === 0">
                                    <td colspan="4" class="px-4 py-2 border-b text-center text-gray-500 italic">
                                        No hay gastos registrados
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

<script>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

export default {
    components: {
        Head,
        Link,
        AuthenticatedLayout,
    },
    props: {
        reserva: Object,
        gastos: Array,
        tiposGasto: Array,
    },
    setup(props) {
        function formatFecha(fecha) {
            if (!fecha) return 'Sin fecha';
            const date = new Date(fecha);
            return date instanceof Date && !isNaN(date)
                ? date.toLocaleDateString('es-ES', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                })
                : 'Fecha inválida';
        }

        return { formatFecha };
    },
};
</script>