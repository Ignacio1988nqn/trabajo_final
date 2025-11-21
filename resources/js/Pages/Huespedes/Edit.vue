<template>

    <Head title="Editar Huésped" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar Huésped
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-xl rounded-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6 text-white">
                        <h1 class="text-2xl font-bold flex items-center gap-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Editar Huésped
                        </h1>
                        <p class="mt-1 opacity-90">
                            {{ huesped.tipo_huesped === 'persona'
                                ? (huesped.personas?.nombre + ' ' + huesped.personas?.apellido)
                                : huesped.empresas?.razon_social || 'Huésped sin nombre'
                            }}
                        </p>
                    </div>

                    <div class="p-8">
                        <form @submit.prevent="submit" class="space-y-8">

                            <!-- Tabs: Persona / Empresa -->
                            <div class="flex border-b border-gray-200">
                                <button type="button" @click="tipoHuesped = 'persona'; form.tipo_huesped = 'persona'"
                                    :class="tipoHuesped === 'persona' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500'"
                                    class="flex items-center gap-2 px-6 py-4 font-semibold text-sm transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Persona Física
                                </button>
                                <button type="button" @click="tipoHuesped = 'empresa'; form.tipo_huesped = 'empresa'"
                                    :class="tipoHuesped === 'empresa' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-gray-500'"
                                    class="flex items-center gap-2 px-6 py-4 font-semibold text-sm transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4z" />
                                        <path fill-rule="evenodd"
                                            d="M4 12a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Empresa
                                </button>
                            </div>

                            <!-- Campos comunes -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="telefono" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Teléfono
                                    </label>
                                    <input v-model="form.telefono" type="text" id="telefono"
                                        placeholder="+54 11 1234-5678"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                                        :class="{ 'border-red-500 focus:border-red-500': form.errors.telefono }" />
                                    <p v-if="form.errors.telefono" class="mt-2 text-sm text-red-600 font-medium">
                                        {{ form.errors.telefono }}
                                    </p>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Email
                                    </label>
                                    <input v-model="form.email" type="email" id="email"
                                        placeholder="huesped@ejemplo.com"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                                        :class="{ 'border-red-500 focus:border-red-500': form.errors.email }" />
                                    <p v-if="form.errors.email" class="mt-2 text-sm text-red-600 font-medium">
                                        {{ form.errors.email }}
                                    </p>
                                </div>
                            </div>

                            <!-- Campos Persona -->
                            <div v-if="tipoHuesped === 'persona'" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="nombre"
                                        class="block text-sm font-semibold text-gray-700 mb-2">Nombre</label>
                                    <input v-model="form.nombre" type="text" id="nombre" placeholder="Juan"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                                        :class="{ 'border-red-500': form.errors.nombre }" />
                                    <p v-if="form.errors.nombre" class="mt-2 text-sm text-red-600 font-medium">
                                        {{ form.errors.nombre }}
                                    </p>
                                </div>

                                <div>
                                    <label for="apellido"
                                        class="block text-sm font-semibold text-gray-700 mb-2">Apellido</label>
                                    <input v-model="form.apellido" type="text" id="apellido" placeholder="Pérez"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                                        :class="{ 'border-red-500': form.errors.apellido }" />
                                    <p v-if="form.errors.apellido" class="mt-2 text-sm text-red-600 font-medium">
                                        {{ form.errors.apellido }}
                                    </p>
                                </div>

                                <div>
                                    <label for="documento" class="block text-sm font-semibold text-gray-700 mb-2">DNI /
                                        Pasaporte</label>
                                    <input v-model="form.documento" type="text" id="documento" placeholder="12.345.678"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                                        :class="{ 'border-red-500': form.errors.documento }" />
                                    <p v-if="form.errors.documento" class="mt-2 text-sm text-red-600 font-medium">
                                        {{ form.errors.documento }}
                                    </p>
                                </div>
                            </div>

                            <!-- Campos Empresa -->
                            <div v-if="tipoHuesped === 'empresa'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="razon_social"
                                        class="block text-sm font-semibold text-gray-700 mb-2">Razón
                                        Social</label>
                                    <input v-model="form.razon_social" type="text" id="razon_social"
                                        placeholder="Ej: Hotel del Sol S.A."
                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                                        :class="{ 'border-red-500': form.errors.razon_social }" />
                                    <p v-if="form.errors.razon_social" class="mt-2 text-sm text-red-600 font-medium">
                                        {{ form.errors.razon_social }}
                                    </p>
                                </div>

                                <div>
                                    <label for="cuit"
                                        class="block text-sm font-semibold text-gray-700 mb-2">CUIT</label>
                                    <input v-model="form.cuit" type="text" id="cuit" placeholder="30-12345678-9"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                                        :class="{ 'border-red-500': form.errors.cuit }" />
                                    <p v-if="form.errors.cuit" class="mt-2 text-sm text-red-600 font-medium">
                                        {{ form.errors.cuit }}
                                    </p>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                                <Link :href="route('huesped.index')"
                                    class="px-7 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                Cancelar
                                </Link>

                                <button type="submit" :disabled="form.processing"
                                    class="px-9 py-3 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-lg transition flex items-center gap-3 disabled:opacity-60">
                                    <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <span>{{ form.processing ? 'Actualizando...' : 'Actualizar Huésped' }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    huesped: Object,
});

const tipoHuesped = ref(props.huesped.tipo_huesped || 'persona');

const form = useForm({
    tipo_huesped: props.huesped.tipo_huesped || 'persona',
    telefono: props.huesped.telefono || '',
    email: props.huesped.email || '',
    nombre: props.huesped.personas?.nombre || null,
    apellido: props.huesped.personas?.apellido || null,
    documento: props.huesped.personas?.documento || null,
    razon_social: props.huesped.empresas?.razon_social || null,
    cuit: props.huesped.empresas?.cuit || null,
});

// Limpia campos del otro tipo al cambiar
watch(tipoHuesped, (newVal) => {
    form.tipo_huesped = newVal;
    if (newVal === 'persona') {
        form.razon_social = null;
        form.cuit = null;
    } else {
        form.nombre = null;
        form.apellido = null;
        form.documento = null;
    }
});

const submit = () => {
    form.put(route('huesped.update', props.huesped.id), {
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: '¡Perfecto!',
                text: 'Huésped actualizado correctamente.',
                confirmButtonText: 'Aceptar'
            });
        },
        onError: () => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor, revisá los campos.',
                confirmButtonText: 'Aceptar'
            });
        }
    });
};
</script>