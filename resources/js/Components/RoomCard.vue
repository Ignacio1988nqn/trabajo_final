<template>
    <!-- Solo renderizamos cuando room existe -->
    <div
        v-if="room && room.id"
        class="relative p-4 rounded-lg shadow-md border-l-4 mb-4 transition-all duration-300"
        :class="cardClasses"
        ref="cardContainer"
    >
        <!-- Botones de acción (solo admin y recepción) -->
        <template
            v-if="['admin', 'recepcion'].includes($page.props.auth?.user?.rol)"
        >
            <div class="absolute top-4 right-6 flex gap-3">
                <!-- Botón menú de estados -->
                <button
                    v-if="estadosDisponibles.length > 0"
                    @click.stop="toggleMenu"
                    class="p-3 rounded-full bg-gray-800/80 backdrop-blur-sm text-white hover:bg-gray-900 hover:shadow-lg transition-all duration-200"
                    title="Cambiar estado"
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
                            stroke-width="2.5"
                            d="M12 6v.01M12 12v.01M12 18v.01M12 7a1 1 0 110-2 1 1 0 010 2zm0 6a1 1 0 110-2 1 1 0 010 2zm0 6a1 1 0 110-2 1 1 0 010 2z"
                        />
                    </svg>
                </button>

                <!-- Botón cambiar habitación (solo si está ocupada) -->
                <button
                    v-if="room.estado_actual === 'ocupada'"
                    @click.stop="toggleCambiarHabitacion"
                    class="p-3 rounded-full bg-green-600 text-white hover:bg-green-700 hover:shadow-lg transition-all duration-200"
                    title="Cambiar habitación"
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
                            stroke-width="2.5"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                        />
                    </svg>
                </button>
            </div>
        </template>

        <!-- Menú de cambio de estado (solo opciones válidas) -->
        <div
            v-if="mostrarMenu"
            class="absolute right-6 top-16 mt-2 w-40 bg-white border rounded-md shadow-lg z-20"
        >
            <ul>
                <li
                    v-for="estado in estadosDisponibles"
                    :key="estado"
                    @click="cambiarEstado(estado)"
                    class="px-3 py-2 text-sm hover:bg-gray-100 cursor-pointer first:rounded-t-md last:rounded-b-md"
                >
                    {{ capitalizar(estado) }}
                </li>
            </ul>
        </div>

        <!-- Formulario cambio de habitación -->
        <div
            v-if="mostrarFormularioCambio"
            class="absolute right-6 top-16 mt-2 w-72 bg-white border rounded-md shadow-lg z-20 p-4"
        >
            <h4 class="text-sm font-semibold mb-3">Cambiar Habitación</h4>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700"
                        >Nueva habitación</label
                    >
                    <select
                        v-model="nuevaHabitacionId"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    >
                        <option value="" disabled>
                            Seleccione una habitación
                        </option>
                        <option
                            v-for="hab in habitacionesDisponibles"
                            :key="hab.id"
                            :value="hab.id"
                        >
                            {{ hab.numero }} ({{ hab.tipo }})
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700"
                        >Motivo del cambio</label
                    >
                    <input
                        v-model="motivoCambio"
                        type="text"
                        placeholder="Ej. Solicitud del cliente"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    />
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button
                        @click="toggleCambiarHabitacion"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded hover:bg-gray-300"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="cambiarHabitacion"
                        :disabled="!nuevaHabitacionId || !motivoCambio"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Confirmar
                    </button>
                </div>
            </div>
        </div>

        <!-- Contenido de la tarjeta -->
        <h3 class="text-lg font-bold">Habitación {{ room.numero }}</h3>
        <p><strong>Tipo:</strong> {{ room.tipo }}</p>
        <p><strong>Precio por noche:</strong> ${{ room.precio_noche }}</p>
        <p>
            <strong>Estado:</strong>
            <span class="font-medium">{{
                capitalizar(room.estado_actual)
            }}</span>
        </p>
        <p>
            <strong>Última limpieza:</strong>
            {{
                room.ultima_limpieza
                    ? new Date(room.ultima_limpieza).toLocaleDateString("es-ES")
                    : "No registrada"
            }}
        </p>
    </div>

    <!-- Skeleton mientras carga (opcional pero queda pro) -->
    <div
        v-else
        class="relative p-4 rounded-lg shadow-md border-l-4 mb-4 bg-gray-50 border-gray-300 animate-pulse"
    >
        <div class="h-7 bg-gray-200 rounded w-32 mb-3"></div>
        <div class="h-4 bg-gray-200 rounded w-48 mb-2"></div>
        <div class="h-4 bg-gray-200 rounded w-40 mb-2"></div>
        <div class="h-4 bg-gray-200 rounded w-36"></div>
    </div>
</template>

<script>
import { router } from "@inertiajs/vue3";
// ❌ IMPORTANTE: sacamos esto
// import Swal from "sweetalert2";

export default {
    name: "RoomCard",
    props: {
        room: {
            type: Object,
            required: true,
            default: () => ({}),
        },
        asignacionVigente: {
            type: Object,
            default: null,
        },
        habitacionesDisponibles: {
            type: Array,
            default: () => [],
        },
    },

    data() {
        return {
            mostrarMenu: false,
            mostrarFormularioCambio: false,
            nuevaHabitacionId: "",
            motivoCambio: "",
            transicionesPermitidas: {
                disponible: ["limpieza", "mantenimiento"],
                limpieza: ["disponible", "mantenimiento"],
                mantenimiento: ["disponible", "limpieza"],
                ocupada: [],
            },
        };
    },

    computed: {
        cardClasses() {
            const estado = this.room?.estado_actual || "";
            switch (estado) {
                case "disponible":
                    return "bg-green-100 border-green-500";
                case "ocupada":
                    return "bg-red-100 border-red-500";
                case "mantenimiento":
                    return "bg-blue-100 border-blue-500";
                case "limpieza":
                    return "bg-yellow-100 border-yellow-500";
                default:
                    return "bg-gray-100 border-gray-400";
            }
        },
        estadosDisponibles() {
            const actual = this.room?.estado_actual;
            if (!actual) return [];
            return this.transicionesPermitidas[actual] || [];
        },
    },

    mounted() {
        document.addEventListener("click", this.handleClickOutside);
    },

    beforeUnmount() {
        document.removeEventListener("click", this.handleClickOutside);
    },

    methods: {
        toggleMenu() {
            this.mostrarMenu = !this.mostrarMenu;
            this.mostrarFormularioCambio = false;
        },

        toggleCambiarHabitacion() {
            this.mostrarFormularioCambio = !this.mostrarFormularioCambio;
            this.mostrarMenu = false;
        },

        handleClickOutside(event) {
            if (
                this.$refs.cardContainer &&
                !this.$refs.cardContainer.contains(event.target)
            ) {
                this.mostrarMenu = false;
                this.mostrarFormularioCambio = false;
            }
        },

        cambiarEstado(nuevoEstado) {
            if (!this.estadosDisponibles.includes(nuevoEstado)) {
                alert("Transición no permitida");
                return;
            }

            this.mostrarMenu = false;

            router.put(
                route("habitaciones.actualizarEstado", this.room.id),
                { estado_actual: nuevoEstado },
                {
                    preserveScroll: true,
                    preserveState: true,
                    replace: true,
                }
            );
        },

        async cambiarHabitacion() {
            if (!this.nuevaHabitacionId || !this.motivoCambio) return;

            // 👉 usamos el Swal GLOBAL que ya tenés
            const Swal = window.Swal;

            if (!Swal) {
                console.error("Swal global no está disponible");
                return;
            }

            const result = await Swal.fire({
                title: "¿Confirmar cambio de habitación?",
                text: "Se verificará disponibilidad y se moverá la reserva si no hay solapamientos.",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sí, cambiar",
                cancelButtonText: "Cancelar",
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
            });

            if (!result.isConfirmed) {
                return;
            }

            this.mostrarFormularioCambio = false;

            router.post(
                route("habitaciones.cambiarHabitacion", this.room.id),
                {
                    nueva_habitacion_id: this.nuevaHabitacionId,
                    motivo_cambio: this.motivoCambio,
                    asignacion_id: this.asignacionVigente?.id,
                },
                {
                    preserveScroll: true,
                    preserveState: true,
                    replace: true,
                    onSuccess: () => {
                        this.nuevaHabitacionId = "";
                        this.motivoCambio = "";
                        Swal.fire(
                            "¡Éxito!",
                            "La habitación se cambió correctamente.",
                            "success"
                        );
                    },
                    onError: (errors) => {
                        Swal.fire(
                            "No se pudo cambiar",
                            errors?.error ||
                                "La nueva habitación no está disponible en ese período.",
                            "error"
                        );
                    },
                }
            );
        },

        capitalizar(texto) {
            if (!texto) return "";
            return texto.charAt(0).toUpperCase() + texto.slice(1);
        },
    },
};
</script>
