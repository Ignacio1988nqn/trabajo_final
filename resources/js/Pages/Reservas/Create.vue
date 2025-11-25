<template>
    <Head title="Nueva Reserva" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Nueva Reserva
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-2xl rounded-2xl overflow-hidden">
                    <!-- Header bonito -->
                    <div
                        class="bg-gradient-to-r from-indigo-600 to-purple-700 p-8 text-white"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h1
                                    class="text-3xl font-bold flex items-center gap-4"
                                >
                                    <svg
                                        class="w-10 h-10"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                    Nueva Reserva
                                </h1>
                                <p class="mt-2 opacity-90 text-lg">
                                    Complete los datos para registrar una nueva
                                    estadía
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm opacity-80">Fecha de hoy</p>
                                <p class="text-2xl font-bold">{{ hoy }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        <form @submit.prevent="submit" class="space-y-10">
                            <!-- 1. Buscador de Huésped (con estilo premium) -->
                            <div class="relative">
                                <label
                                    class="block text-lg font-semibold text-gray-800 mb-3"
                                >
                                    Huésped
                                </label>

                                <div class="relative">
                                    <div class="flex gap-3">
                                        <div class="flex-1 relative">
                                            <input
                                                type="text"
                                                v-model="buscador.state.busq"
                                                @input="buscador.onInput"
                                                @keydown="buscador.onKeydown"
                                                @blur="buscador.blurConDelay"
                                                placeholder="Buscar por Apellido, Nombre, DNI, Razón Social o CUIT..."
                                                class="w-full px-5 py-4 pr-12 rounded-xl border-2 border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition text-lg"
                                                autocomplete="off"
                                            />

                                            <!-- Ícono a la derecha -->
                                            <div
                                                class="absolute inset-y-0 right-4 flex items-center pointer-events-none"
                                            >
                                                <svg
                                                    class="w-6 h-6 text-gray-400"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                                    />
                                                </svg>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            @click="buscador.limpiar"
                                            class="px-6 py-4 bg-gray-100 hover:bg-gray-200 rounded-xl font-medium transition"
                                        >
                                            Limpiar
                                        </button>
                                    </div>

                                    <!-- Resultados del buscador -->
                                    <div
                                        v-if="
                                            buscador.state.abierto &&
                                            buscador.state.opciones.length
                                        "
                                        class="absolute z-50 mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-200 max-h-80 overflow-y-auto"
                                    >
                                        <button
                                            v-for="(op, i) in buscador.state
                                                .opciones"
                                            :key="op.id"
                                            type="button"
                                            @mousedown.prevent="
                                                buscador.seleccionar(op)
                                            "
                                            class="w-full text-left px-6 py-4 hover:bg-indigo-50 transition flex items-center justify-between"
                                            :class="{
                                                'bg-indigo-100':
                                                    i ===
                                                    buscador.state.highlighted,
                                            }"
                                        >
                                            <div>
                                                <p
                                                    class="font-semibold text-gray-800"
                                                >
                                                    {{ op.display }}
                                                </p>
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    {{
                                                        op.tipo === "persona"
                                                            ? "DNI"
                                                            : "CUIT"
                                                    }}: {{ op.doc }}
                                                </p>
                                            </div>
                                            <span
                                                class="text-xs px-3 py-1 rounded-full"
                                                :class="
                                                    op.tipo === 'persona'
                                                        ? 'bg-blue-100 text-blue-800'
                                                        : 'bg-purple-100 text-purple-800'
                                                "
                                            >
                                                {{ op.tipo }}
                                            </span>
                                        </button>
                                    </div>
                                </div>

                                <p
                                    v-if="form.errors.huesped_id"
                                    class="mt-3 text-red-600 font-medium"
                                >
                                    {{ form.errors.huesped_id }}
                                </p>
                            </div>

                            <!-- 2. Asignación de habitaciones -->
                            <div>
                                <div
                                    class="flex items-center justify-between mb-6"
                                >
                                    <h3 class="text-xl font-bold text-gray-800">
                                        Habitaciones asignadas
                                    </h3>
                                </div>

                                <div
                                    v-for="(a, idx) in form.asignaciones"
                                    :key="idx"
                                    class="mb-8"
                                >
                                    <div
                                        class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl p-6 border-2 border-gray-200"
                                    >
                                        <div
                                            class="flex items-center justify-between mb-5"
                                        >
                                            <h4
                                                class="text-lg font-bold text-gray-800"
                                            >
                                                Habitación {{ idx + 1 }}
                                            </h4>
                                            <button
                                                type="button"
                                                @click="removeAsignacion(idx)"
                                                :disabled="
                                                    form.asignaciones.length ===
                                                    1
                                                "
                                                class="text-red-600 hover:text-red-800 font-medium transition"
                                            >
                                                Quitar
                                            </button>
                                        </div>

                                        <div
                                            class="grid grid-cols-1 lg:grid-cols-3 gap-6"
                                        >
                                            <!-- Fechas -->
                                            <div>
                                                <label
                                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                                    >Check-in</label
                                                >
                                                <input
                                                    type="date"
                                                    v-model="a.fecha_inicio"
                                                    :min="hoy"
                                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                                                    required
                                                />
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                                    >Check-out</label
                                                >
                                                <input
                                                    type="date"
                                                    v-model="a.fecha_fin"
                                                    :min="a.fecha_inicio || hoy"
                                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                                                    required
                                                />
                                            </div>

                                            <!-- Habitación -->
                                            <div>
                                                <label
                                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                                    >Habitación
                                                    disponible</label
                                                >
                                                <select
                                                    v-model="a.habitacion_id"
                                                    class="w-full px-4 py-3 rounded-lg border-2"
                                                    :class="[
                                                        a.habitacion_id &&
                                                        isSeleccionDisponible(
                                                            idx,
                                                            a.habitacion_id
                                                        )
                                                            ? 'border-green-500 bg-green-50'
                                                            : a.habitacion_id
                                                            ? 'border-red-500 bg-red-50'
                                                            : 'border-gray-300',
                                                    ]"
                                                    :disabled="
                                                        !a.fecha_inicio ||
                                                        !a.fecha_fin
                                                    "
                                                >
                                                    <option value="">
                                                        Seleccione fechas
                                                        primero...
                                                    </option>
                                                    <option
                                                        v-for="hab in disponibles[
                                                            idx
                                                        ] || []"
                                                        :key="hab.id"
                                                        :value="hab.id"
                                                    >
                                                        {{ hab.numero }} —
                                                        {{ hab.tipo }} ({{
                                                            hab.capacidad
                                                        }}
                                                        pers.)
                                                    </option>
                                                </select>

                                                <div
                                                    v-if="a.habitacion_id"
                                                    class="mt-2 flex items-center gap-2"
                                                >
                                                    <span
                                                        class="px-3 py-1 rounded-full text-xs font-bold"
                                                        :class="
                                                            isSeleccionDisponible(
                                                                idx,
                                                                a.habitacion_id
                                                            )
                                                                ? 'bg-green-100 text-green-800'
                                                                : 'bg-red-100 text-red-800'
                                                        "
                                                    >
                                                        {{
                                                            isSeleccionDisponible(
                                                                idx,
                                                                a.habitacion_id
                                                            )
                                                                ? "Disponible"
                                                                : "Ocupada en esas fechas"
                                                        }}
                                                    </span>
                                                </div>
                                                <p
                                                    v-if="
                                                        form.errors[
                                                            `asignaciones.${idx}.habitacion_id`
                                                        ]
                                                    "
                                                    class="text-red-600 text-sm mt-1"
                                                >
                                                    {{
                                                        form.errors[
                                                            `asignaciones.${idx}.habitacion_id`
                                                        ]
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Estado y observaciones -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <button
                                        type="button"
                                        @click="addAsignacion"
                                        class="inline-flex items-center gap-2 px-5 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-md transition font-semibold"
                                    >
                                        <svg
                                            class="w-5 h-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 4v16m8-8H4"
                                            />
                                        </svg>
                                        Agregar habitación
                                    </button>
                                    <!-- <label
                                        class="block text-lg font-semibold text-gray-800 mb-3"
                                        >Estado de la reserva</label
                                    > -->
                                    <!-- <select
                                        v-model="form.estado"
                                        class="w-full px-5 py-4 rounded-xl border-2 border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition text-lg"
                                    >
                                        <option value="pendiente">
                                            Pendiente
                                        </option> -->
                                    <!-- <option value="confirmada">
                                            Confirmada
                                        </option>
                                        <option value="checkin">
                                            Check-in realizado
                                        </option>
                                        <option value="checkout">
                                            Check-out realizado
                                        </option>
                                        <option value="cancelada">
                                            Cancelada
                                        </option> -->
                                    <!-- </select> -->
                                </div>

                                <div>
                                    <label
                                        class="block text-lg font-semibold text-gray-800 mb-3"
                                        >Observaciones (opcional)</label
                                    >
                                    <textarea
                                        v-model="form.observaciones"
                                        rows="4"
                                        placeholder="Ej: Llegada tarde, pedido de cuna, allergy..."
                                        class="w-full px-5 py-4 rounded-xl border-2 border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Botones finales -->
                            <div
                                class="flex justify-end gap-5 pt-8 border-t-2 border-gray-200"
                            >
                                <Link
                                    :href="route('reservas.index')"
                                    class="px-8 py-4 text-lg font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-xl hover:bg-gray-50 transition"
                                >
                                    Cancelar
                                </Link>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-10 py-4 text-lg font-bold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl hover:from-indigo-700 hover:to-purple-700 shadow-xl transition flex items-center gap-3 disabled:opacity-60"
                                >
                                    <svg
                                        v-if="form.processing"
                                        class="animate-spin h-6 w-6"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        ></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                                        ></path>
                                    </svg>
                                    <span>{{
                                        form.processing
                                            ? "Guardando reserva..."
                                            : "Crear Reserva"
                                    }}</span>
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
import { Head, Link } from "@inertiajs/vue3";
import { watch, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useReservaCreate } from "@/composables/useReservaCreate";
import { useHuespedSearch } from "@/composables/useHuespedSearch";

const props = defineProps({
    huespedes: { type: Array, default: () => [] },
    habitaciones: { type: Array, default: () => [] },
    initialFrom: { type: String, default: null },
    initialTo: { type: String, default: null },
});

const {
    form,
    disponibles,
    addAsignacion,
    removeAsignacion,
    submit,
    setupWatchers,
    isSeleccionDisponible,
} = useReservaCreate(props);

const buscador = useHuespedSearch();

watch(
    () => buscador.state.seleccionadoId,
    (val) => {
        form.huesped_id = val || "";
    }
);

setupWatchers();

const hoy = computed(() => new Date().toISOString().split("T")[0]);
</script>
