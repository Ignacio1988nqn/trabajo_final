<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
</script>
<template>

    <Head title="Gastos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Cargar Gastos
            </h2>
        </template>

        <div v-if="$page.props.errors.error" class="bg-red-100 text-red-700 p-4 mb-4 rounded">
            {{ $page.props.errors.error }}
        </div>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="reservas.length === 0" class="text-gray-500">
                            No hay reservas activas (pendiente o check-in) para cargar gastos.
                        </div>
                        <table v-else class="min-w-full bg-white border">
                            <thead>
                                <tr>
                                    <th class="py-2 px-4 border">ID</th>
                                    <th class="py-2 px-4 border">Huésped</th>
                                    <th class="py-2 px-4 border">Fecha Check-in</th>
                                    <th class="py-2 px-4 border">Fecha Check-out</th>
                                    <th class="py-2 px-4 border">Estado</th>
                                    <th class="py-2 px-4 border">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="reserva in reservas" :key="reserva.id">
                                    <td class="py-2 px-4 border">{{ reserva.id }}</td>
                                    <td class="py-2 px-4 border">{{ reserva.huesped.nombre }}</td>
                                    <td class="py-2 px-4 border">{{ reserva.fecha_checkin || 'N/A' }}</td>
                                    <td class="py-2 px-4 border">{{ reserva.fecha_checkout || 'N/A' }}</td>
                                    <td class="py-2 px-4 border">{{ reserva.estado }}</td>
                                    <td class="py-2 px-4 border">
                                        <Link :href="route('gastos.create', reserva.id)"
                                            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                        Cargar Gasto
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

<script>
import { Link } from '@inertiajs/vue3';
export default {
    components: {
        Link,
    },
    props: {
        reservas: Array,
    },
};
</script>