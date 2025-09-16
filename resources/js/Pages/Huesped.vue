<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    products: Array,
    success: String,
});

const destroy = (id) => {
    if (confirm('¿Estás seguro de eliminar este producto?')) {
        router.delete(route('products.destroy', id));
    }
};
</script>

<template>

    <Head title="Huesped" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Huesped
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="container mx-auto p-4">
                            <h1 class="text-2xl font-bold mb-4">Huesped</h1>
                            <Link :href="route('products.create')"
                                class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">
                            Alta Huesped
                            </Link>
                            <div v-if="$page.props.success" class="bg-green-100 text-green-700 p-4 mb-4 rounded">
                                {{ $page.props.success }}
                            </div>
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-200">
                                        <th class="border p-2">Nombre</th>
                                        <th class="border p-2">Descripción</th>
                                        <th class="border p-2">Precio</th>
                                        <th class="border p-2">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product in products" :key="product.id">
                                        <td class="border p-2">{{ product.name }}</td>
                                        <td class="border p-2">{{ product.description }}</td>
                                        <td class="border p-2">{{ product.price }}</td>
                                        <td class="border p-2">
                                            <Link :href="route('products.edit', product.id)" class="text-blue-500 mr-2">
                                            Editar
                                            </Link>
                                            <button @click="destroy(product.id)" class="text-red-500">Eliminar</button>
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
