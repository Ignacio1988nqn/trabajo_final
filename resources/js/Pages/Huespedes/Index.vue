<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import BaseButton from "@/Components/BaseButton.vue";

defineProps({
    huespedes: Array,
    success: String,
});

const destroy = (id) => {
    Swal.fire({
        title: "¿Estás seguro?",
        text: "¿Confirmas que deseas eliminar este huésped? Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route("huesped.destroy", id), {
                onSuccess: () => {
                    Swal.fire({
                        icon: "success",
                        title: "¡Éxito!",
                        text: "El huésped ha sido eliminado correctamente.",
                        confirmButtonText: "Aceptar",
                    });
                },
                onError: (errors) => {
                    Swal.fire({
                        icon: "error",
                        title: "¡Error!",
                        text: "Hubo un error al eliminar el huésped.",
                        confirmButtonText: "Aceptar",
                    });
                },
            });
        }
    });
};
</script>

<template>
    <Head title="Huéspedes" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-[#0F3D3E]"
                >
                    Gestión de Huespedes
                </h1>
                <p class="mt-1 text-sm text-neutral-500">
                    Eliminar, editar y crear huesped
                </p>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="container mx-auto p-4">
                            <h1 class="text-2xl font-bold mb-4">Huéspedes</h1>

                            <!-- boton redieño  -->
                            <BaseButton to="huesped.create">
                                Ingresar huésped
                            </BaseButton>
                            <br />
                            <br />
                            <div
                                v-if="$page.props.success"
                                class="bg-green-100 text-green-700 p-4 mb-4 rounded"
                            >
                                {{ $page.props.success }}
                            </div>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Tipo
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Nombre / Razón Social
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Teléfono
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Email
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    v-if="huespedes.length"
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr
                                        v-for="huesped in huespedes"
                                        :key="huesped.id"
                                        class="hover:bg-gray-50 transition-colors duration-200"
                                    >
                                        <!-- Tipo de huésped con badge bonito -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                                                :class="{
                                                    'bg-blue-100 text-blue-800':
                                                        huesped.tipo_huesped ===
                                                        'persona',
                                                    'bg-purple-100 text-purple-800':
                                                        huesped.tipo_huesped ===
                                                        'empresa',
                                                    'bg-gray-100 text-gray-800':
                                                        !huesped.tipo_huesped,
                                                }"
                                            >
                                                {{
                                                    huesped.tipo_huesped ||
                                                    "desconocido"
                                                }}
                                            </span>
                                        </td>

                                        <!-- Nombre completo o razón social (destacado) -->
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900"
                                        >
                                            <div class="flex items-center">
                                                <!-- Icono según tipo -->
                                                <svg
                                                    v-if="
                                                        huesped.tipo_huesped ===
                                                        'persona'
                                                    "
                                                    class="w-5 h-5 text-gray-400 mr-3"
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                                <svg
                                                    v-else
                                                    class="w-5 h-5 text-gray-400 mr-3"
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path
                                                        d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4z"
                                                    />
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M4 12a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>

                                                {{
                                                    huesped.tipo_huesped ===
                                                    "persona"
                                                        ? huesped.personas
                                                              ?.nombre +
                                                          " " +
                                                          huesped.personas
                                                              ?.apellido
                                                        : huesped.empresas
                                                              ?.razon_social ||
                                                          "Sin razón social"
                                                }}
                                            </div>
                                        </td>

                                        <!-- Teléfono -->
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"
                                        >
                                            {{ huesped.telefono || "—" }}
                                        </td>

                                        <!-- Email -->
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"
                                        >
                                            <a
                                                :href="
                                                    'mailto:' + huesped.email
                                                "
                                                class="text-indigo-600 hover:text-indigo-800 underline"
                                            >
                                                {{ huesped.email || "—" }}
                                            </a>
                                        </td>

                                        <!-- Acciones (botones premium) -->
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'huesped.edit',
                                                        huesped.id
                                                    )
                                                "
                                                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition"
                                            >
                                                Editar
                                            </Link>

                                            <button
                                                @click="destroy(huesped.id)"
                                                class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-700 transition"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>

                                <!-- Sin huéspedes -->
                                <tbody v-else>
                                    <tr>
                                        <td
                                            colspan="5"
                                            class="px-6 py-16 text-center text-gray-500 bg-gray-50"
                                        >
                                            <svg
                                                class="mx-auto h-16 w-16 text-gray-400 mb-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                                />
                                            </svg>
                                            <p
                                                class="text-xl font-semibold text-gray-700"
                                            >
                                                No hay huéspedes registrados
                                            </p>
                                            <p class="text-sm mt-2">
                                                Agregá personas o empresas para
                                                poder hacer reservas.
                                            </p>
                                            <Link
                                                :href="route('huesped.create')"
                                                class="mt-5 inline-flex items-center rounded-md bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow hover:bg-indigo-700 transition"
                                            >
                                                + Agregar primer huésped
                                            </Link>
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
