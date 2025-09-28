<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// Estado para el tipo de huésped
const tipoHuesped = ref('persona');

// Configuración del formulario con Inertia
const form = useForm({
    tipo_huesped: 'persona',
    telefono: '',
    email: '',
    nombre: '',
    apellido: '',
    documento: '',
    razon_social: '',
    cuit: '',
});

// Método para enviar el formulario
const submit = () => {
    form.post(route('huesped.store'), {
        onSuccess: () => {
            form.reset();
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Huesped cargado correctamente.',
                confirmButtonText: 'Aceptar'
            });
        },
        onError: (errors) => {
            console.log('Errores de validación:', errors);
            Swal.fire({
                icon: 'error',
                title: '¡Error!',
                text: 'Hubo errores en el formulario. Por favor, revisa los datos.',
                confirmButtonText: 'Aceptar'
            });
        },
    });
};

// Actualiza el tipo de huésped
const updateTipoHuesped = (value) => {
    tipoHuesped.value = value;
    form.tipo_huesped = value;
};
</script>

<template>

    <Head title="Crear Huésped" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Crear Huésped
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-4">Alta Huésped</h1>

                        <form @submit.prevent="submit">
                            <!-- Mensaje de error general -->
                            <div v-if="$page.props.errors.error" class="bg-red-100 text-red-700 p-4 mb-4 rounded">
                                {{ $page.props.errors.error }}
                            </div>

                            <!-- Tipo de huésped -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Tipo de Huésped</label>
                                <select v-model="tipoHuesped" @change="updateTipoHuesped($event.target.value)"
                                    class="mt-1 block w-full border-gray-300 rounded-md">
                                    <option value="persona">Persona</option>
                                    <option value="empresa">Empresa</option>
                                </select>
                                <span v-if="form.errors.tipo_huesped" class="text-red-500 text-sm">{{
                                    form.errors.tipo_huesped
                                    }}</span>
                            </div>

                            <!-- Campos comunes -->
                            <div class="mb-4">
                                <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                                <input v-model="form.telefono" type="text" id="telefono"
                                    class="mt-1 block w-full border-gray-300 rounded-md" />
                                <span v-if="form.errors.telefono" class="text-red-500 text-sm">{{ form.errors.telefono
                                    }}</span>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input v-model="form.email" type="email" id="email"
                                    class="mt-1 block w-full border-gray-300 rounded-md" />
                                <span v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email
                                    }}</span>
                            </div>

                            <!-- Campos para Persona -->
                            <div v-if="tipoHuesped === 'persona'">
                                <div class="mb-4">
                                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                                    <input v-model="form.nombre" type="text" id="nombre"
                                        class="mt-1 block w-full border-gray-300 rounded-md" />
                                    <span v-if="form.errors.nombre" class="text-red-500 text-sm">{{ form.errors.nombre
                                        }}</span>
                                </div>

                                <div class="mb-4">
                                    <label for="apellido"
                                        class="block text-sm font-medium text-gray-700">Apellido</label>
                                    <input v-model="form.apellido" type="text" id="apellido"
                                        class="mt-1 block w-full border-gray-300 rounded-md" />
                                    <span v-if="form.errors.apellido" class="text-red-500 text-sm">{{
                                        form.errors.apellido
                                        }}</span>
                                </div>

                                <div class="mb-4">
                                    <label for="documento"
                                        class="block text-sm font-medium text-gray-700">Documento</label>
                                    <input v-model="form.documento" type="number" id="documento"
                                        class="mt-1 block w-full border-gray-300 rounded-md" />
                                    <span v-if="form.errors.documento" class="text-red-500 text-sm">{{
                                        form.errors.documento
                                        }}</span>
                                </div>
                            </div>

                            <!-- Campos para Empresa -->
                            <div v-if="tipoHuesped === 'empresa'">
                                <div class="mb-4">
                                    <label for="razon_social" class="block text-sm font-medium text-gray-700">Razón
                                        Social</label>
                                    <input v-model="form.razon_social" type="text" id="razon_social"
                                        class="mt-1 block w-full border-gray-300 rounded-md" />
                                    <span v-if="form.errors.razon_social" class="text-red-500 text-sm">{{
                                        form.errors.razon_social }}</span>
                                </div>

                                <div class="mb-4">
                                    <label for="cuit" class="block text-sm font-medium text-gray-700">CUIT</label>
                                    <input v-model="form.cuit" type="text" id="cuit"
                                        class="mt-1 block w-full border-gray-300 rounded-md" />
                                    <span v-if="form.errors.cuit" class="text-red-500 text-sm">{{ form.errors.cuit
                                        }}</span>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="flex space-x-4">
                                <button type="submit" :disabled="form.processing"
                                    class="bg-blue-500 text-white px-4 py-2 rounded">
                                    Guardar
                                </button>
                                <Link :href="route('huesped.index')" class="bg-gray-500 text-white px-4 py-2 rounded">
                                Cancelar
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>