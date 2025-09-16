<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    huespedes: Array,
    success: String,
});

const destroy = (id) => {
    if (confirm('¿Estás seguro de eliminar este huésped?')) {
        router.delete(route('huesped.destroy', id));
    }
};
</script>

<template>
    <Head title="Huéspedes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Huéspedes
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="container mx-auto p-4">
                            <h1 class="text-2xl font-bold mb-4">Huéspedes</h1>
                            <Link :href="route('huesped.create')"
                                class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">
                                Alta Huésped
                            </Link>
                            <div v-if="$page.props.success" class="bg-green-100 text-green-700 p-4 mb-4 rounded">
                                {{ $page.props.success }}
                            </div>
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-200">
                                        <th class="border p-2">Tipo</th>
                                        <th class="border p-2">Nombre/Razón Social</th>
                                        <th class="border p-2">Teléfono</th>
                                        <th class="border p-2">Email</th>
                                        <th class="border p-2">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="huesped in huespedes" :key="huesped.id">
                                        <td class="border p-2">{{ huesped.tipo_huesped }}</td>
                                        <td class="border p-2">
                                            {{ huesped.tipo_huesped === 'persona' ? huesped.personas?.nombre + ' ' + huesped.personas?.apellido : huesped.empresas?.razon_social }}
                                        </td>
                                        <td class="border p-2">{{ huesped.telefono }}</td>
                                        <td class="border p-2">{{ huesped.email }}</td>
                                        <td class="border p-2">
                                            <Link :href="route('huesped.edit', huesped.id)" class="text-blue-500 mr-2">
                                                Editar
                                            </Link>
                                            <button @click="destroy(huesped.id)" class="text-red-500">Eliminar</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>