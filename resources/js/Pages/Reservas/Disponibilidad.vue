<script setup>
import { Head, router } from "@inertiajs/vue3";
import { ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useCalendarioDisponibilidad } from "@/composables/useDisponibilidadCalendar";

const props = defineProps({
    from: { type: String, required: true },
    days: { type: Number, required: true },
    daysArr: { type: Array, required: true },
    tipo: { type: String, default: null },
    rooms: { type: Array, default: () => [] },
});

const { colIndex, spanDays, gridTemplate, go, hoy, colorState } =
    useCalendarioDisponibilidad(props);

// 🔹 nueva fecha "hasta" solo para generar reserva
const hasta = ref("");

// 🔹 botón "Generar reserva"
function generarReserva() {
    if (!props.from || !hasta.value) return;

    // opcional: validar que hasta >= desde
    if (hasta.value < props.from) {
        alert("La fecha 'Hasta' no puede ser menor que 'Desde'.");
        return;
    }

    router.get(route("reservas.create"), {
        from: props.from,
        to: hasta.value,
    });
}
</script>

<template>
    <Head title="Calendario de Disponibilidad" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-[#0F3D3E]"
                >
                    Calendario de disponibilidad
                </h1>
                <p class="mt-1 text-sm text-neutral-500">
                    Visualizar cronograma de reservas y creá una.
                </p>
            </div>
        </template>
        <!-- Filtros + Calendario, todo hilvanado -->
        <div class="max-w-7xl mx-auto px-4 mt-6">
            <div class="bg-white rounded-3xl shadow-lg overflow-hidden">
                <!-- CABECERA DE FILTROS -->
                <div
                    class="bg-gradient-to-r from-indigo-600 to-purple-700 p-6 text-white"
                >
                    <div
                        class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-6"
                    >
                        <!-- Desde -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">
                                Desde
                            </label>
                            <input
                                type="date"
                                class="w-full px-5 py-3 rounded-xl bg-white/20 backdrop-blur placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-white/30 transition text-white text-base font-medium"
                                :value="from"
                                @change="
                                    (e) =>
                                        go({ from: e.target.value, days, tipo })
                                "
                            />
                        </div>

                        <!-- Días -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">
                                Días
                            </label>
                            <select
                                class="w-full px-5 py-3 rounded-xl bg-white/20 backdrop-blur focus:outline-none focus:ring-4 focus:ring-white/30 transition text-white text-base font-medium [appearance:none] [&_*]::text-white [&_*]::bg-indigo-700 [&_option]:bg-indigo-700 [&_option]:text-white [&_option:hover]:bg-indigo-600"
                                :value="days"
                                @change="
                                    (e) =>
                                        go({
                                            from,
                                            days: Number(e.target.value),
                                            tipo,
                                        })
                                "
                            >
                                <option :value="7">7 días</option>
                                <option :value="14">14 días</option>
                                <option :value="21">21 días</option>
                                <option :value="30">30 días</option>
                            </select>
                        </div>

                        <!-- Tipo -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">
                                Tipo de habitación
                            </label>
                            <select
                                class="w-full px-5 py-3 rounded-xl bg-white/20 backdrop-blur focus:outline-none focus:ring-4 focus:ring-white/30 transition text-white text-base font-medium [appearance:none] [&_*]::text-white [&_*]::bg-indigo-700 [&_option]:bg-indigo-700 [&_option]:text-white [&_option:hover]:bg-indigo-600"
                                :value="tipo || ''"
                                @change="
                                    (e) =>
                                        go({
                                            from,
                                            days,
                                            tipo: e.target.value || null,
                                        })
                                "
                            >
                                <option value="">Todos</option>
                                <option value="simple">Simple</option>
                                <option value="doble">Doble</option>
                                <option value="triple">Triple</option>
                                <option value="cuadruple">Cuádruple</option>
                            </select>
                        </div>

                        <!-- Hasta -->
                        <div>
                            <label class="block text-sm font-semibold mb-2">
                                Hasta
                            </label>
                            <input
                                type="date"
                                class="w-full px-5 py-3 rounded-xl bg-white/20 backdrop-blur placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-white/30 transition text-white text-base font-medium"
                                v-model="hasta"
                                :min="from"
                            />
                        </div>

                        <!-- Botón Generar -->
                        <div class="flex items-end">
                            <button
                                type="button"
                                class="w-full px-5 py-3 rounded-xl bg-white text-indigo-700 text-sm md:text-base font-semibold shadow-md hover:bg-slate-100 hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                                @click="generarReserva"
                                :disabled="!hasta"
                            >
                                Generar reserva
                            </button>
                        </div>
                    </div>

                    <!-- Leyenda -->
                    <div
                        class="mt-4 flex flex-wrap items-center gap-4 text-xs md:text-sm"
                    >
                        <span class="inline-flex items-center gap-2">
                            <span class="w-3 h-3 rounded bg-yellow-400"></span
                            >Pendiente
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <span class="w-3 h-3 rounded bg-rose-500"></span
                            >Ocupada
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <span class="w-3 h-3 rounded bg-amber-500"></span
                            >Limpieza
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <span class="w-3 h-3 rounded bg-purple-500"></span
                            >Mantenimiento
                        </span>
                    </div>
                </div>

                <!-- CUERPO: CALENDARIO -->
                <div class="border-t border-slate-200">
                    <div class="overflow-x-auto bg-white" id="cal-wrapper">
                        <!-- Header de días -->
                        <div
                            class="grid sticky top-0 z-10 bg-white"
                            :style="{
                                gridTemplateColumns: gridTemplate,
                                minWidth: `calc(260px + ${days} * 60px)`,
                            }"
                        >
                            <div class="border-b px-3 py-2 font-semibold">
                                Habitación
                            </div>
                            <div
                                v-for="d in daysArr"
                                :key="d.iso"
                                class="border-b px-2 py-2 text-center text-sm"
                            >
                                <div class="font-semibold">{{ d.dow }}</div>
                                <div>{{ d.dom }}</div>
                            </div>
                        </div>

                        <!-- Filas por habitación -->
                        <div
                            v-for="r in rooms"
                            :key="r.id"
                            class="relative border-b"
                            :style="{
                                minWidth: `calc(260px + ${days} * 60px)`,
                            }"
                        >
                            <!-- Columna izquierda fija -->
                            <div
                                class="flex items-center gap-2 px-3 py-2 bg-white border-r w-[260px] absolute left-0 top-0 bottom-0"
                            >
                                <div>
                                    <div class="font-semibold">
                                        Hab. {{ r.numero }}
                                    </div>
                                    <div
                                        class="text-xs text-gray-500 capitalize"
                                    >
                                        {{ r.tipo }}
                                    </div>
                                </div>
                                <span
                                    class="ml-auto text-xs capitalize px-2 py-1 rounded bg-gray-100"
                                >
                                    {{ r.estado }}
                                </span>
                            </div>

                            <!-- Grilla de días -->
                            <div
                                class="grid ml-[260px] relative"
                                :style="{
                                    gridTemplateColumns: `repeat(${days}, minmax(3rem, 1fr))`,
                                }"
                            >
                                <!-- Columnas de fondo -->
                                <div
                                    v-for="d in daysArr"
                                    :key="d.iso + r.id"
                                    class="h-10 md:h-12 border-r last:border-r-0 border-gray-200 relative select-none"
                                >
                                    <div
                                        v-if="d.iso === hoy"
                                        class="absolute inset-y-0 w-0.5 bg-indigo-500 left-0"
                                    ></div>
                                </div>

                                <!-- Bloques de reservas -->
                                <template
                                    v-for="(b, j) in r.bookings"
                                    :key="`res-${j}`"
                                >
                                    <div
                                        class="h-8 flex items-center text-xs font-medium text-white rounded-md px-2 overflow-hidden whitespace-nowrap shadow-sm"
                                        :class="colorState(b.state)"
                                        :style="{
                                            gridColumn: `${
                                                colIndex(b.start) + 1
                                            } / ${colIndex(b.end) + 1}`,
                                            gridRow: '1 / 2',
                                        }"
                                    >
                                        {{ b.huesped }} — {{ b.start }} →
                                        {{ b.end }}
                                    </div>
                                </template>

                                <!-- Bloques -->
                                <template
                                    v-for="(b, j) in r.blocks"
                                    :key="`blk-${j}`"
                                >
                                    <div
                                        class="h-10 md:h-12 rounded-md flex items-center justify-center text-xs text-gray-800 font-medium"
                                        :class="colorState(b.state)"
                                        :style="{
                                            gridColumn: `1 / ${days + 1}`,
                                        }"
                                    >
                                        {{ b.state }}
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Ajustes finos que necesites para el grid */
</style>
