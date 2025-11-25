<template>
    <Head title="Consultar Gastos de Reservas" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-[#0F3D3E]"
                >
                    Gestionar gastos de Reservas
                </h1>
                <p class="mt-1 text-sm text-neutral-500">
                    Visualizar y gestionar gastos por habitación.
                </p>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Reserva
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Huésped
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Habitación
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Estancia
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Total Gastos
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                v-if="reservas.length"
                                class="bg-white divide-y divide-gray-200"
                            >
                                <tr
                                    v-for="reserva in reservas"
                                    :key="reserva.id"
                                    class="hover:bg-gray-50 transition-colors duration-200"
                                >
                                    <!-- ID Reserva -->
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                    >
                                        #{{ reserva.id }}
                                    </td>

                                    <!-- Huésped -->
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                    >
                                        {{
                                            reserva.cliente ||
                                            reserva.huesped_nombre ||
                                            "Sin nombre"
                                        }}
                                    </td>

                                    <!-- Habitación con colores según estado del detalle -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-wrap gap-2">
                                            <template
                                                v-for="hab in reserva.habitaciones_data"
                                                :key="hab.numero"
                                            >
                                                <span
                                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                    :class="{
                                                        'bg-yellow-100 text-yellow-900':
                                                            hab.estado ===
                                                            'pendiente',
                                                        'bg-green-100 text-green-900':
                                                            hab.estado ===
                                                            'checkin',
                                                        'bg-red-100 text-red-800':
                                                            hab.estado ===
                                                            'checkout',
                                                        'bg-red-100 text-red-900':
                                                            hab.estado ===
                                                            'cancelado',
                                                        'bg-gray-100 text-gray-700':
                                                            !hab.estado ||
                                                            hab.estado ===
                                                                'desconocido',
                                                    }"
                                                    :title="
                                                        hab.estado
                                                            ? hab.estado
                                                                  .charAt(0)
                                                                  .toUpperCase() +
                                                              hab.estado.slice(
                                                                  1
                                                              )
                                                            : 'Sin estado'
                                                    "
                                                >
                                                    {{ hab.numero }}
                                                </span>
                                            </template>
                                            <span
                                                v-if="
                                                    !reserva.habitaciones_data
                                                        .length
                                                "
                                                class="text-gray-400 text-xs"
                                                >—</span
                                            >
                                        </div>
                                    </td>

                                    <!-- Estancia (fechas perfectas) -->
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"
                                    >
                                        <div class="flex flex-col">
                                            <span class="text-xs text-gray-500"
                                                >In</span
                                            >
                                            <span class="font-medium">{{
                                                reserva.fecha_inicio ?? "—"
                                            }}</span>
                                            <span
                                                class="text-xs text-gray-500 mt-1"
                                                >Out</span
                                            >
                                            <span class="font-medium">{{
                                                reserva.fecha_fin
                                            }}</span>
                                        </div>
                                    </td>

                                    <!-- Total Gastos (puedes calcularlo en el backend o con un computed) -->
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900"
                                    >
                                        {{
                                            reserva.total_gastos
                                                ? "$" +
                                                  Number(
                                                      reserva.total_gastos
                                                  ).toLocaleString()
                                                : "$0"
                                        }}
                                    </td>

                                    <!-- Botón Ver detalle -->
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'gastos.asignaciones',
                                                    reserva.id
                                                )
                                            "
                                            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                                        >
                                            <svg
                                                class="w-4 h-4 mr-1"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                            Ver detalle
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>

                            <!-- Sin reservas con gastos -->
                            <tbody v-else>
                                <tr>
                                    <td
                                        colspan="7"
                                        class="px-6 py-12 text-center text-gray-500 bg-gray-50"
                                    >
                                        <svg
                                            class="mx-auto h-12 w-12 text-gray-400 mb-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                                            />
                                        </svg>
                                        <p class="text-lg font-medium">
                                            No hay consumos registrados aún
                                        </p>
                                        <p class="text-sm mt-1">
                                            Cuando los huéspedes consuman
                                            servicios aparecerán aquí.
                                        </p>
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
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

defineProps({
    reservas: {
        type: Array,
        default: () => [],
    },
});
const formatFecha = (fecha) => {
    if (!fecha) return "—";
    const [year, month, day] = fecha.split("T")[0].split("-");
    const date = new Date(year, month - 1, day); // ¡Esto ignora el timezone!

    return date.toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};
</script>
