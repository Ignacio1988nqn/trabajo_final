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
// const props = defineProps({
//     from: { type: String, required: true },
//     days: { type: Number, required: true },
//     daysArr: { type: Array, required: true },
//     tipo: { type: String, default: null },
//     rooms: { type: Array, default: () => [] },
// });

// const { colIndex, spanDays, gridTemplate, go, hoy, colorState } =
//     useCalendarioDisponibilidad(props);
</script>

<template>
    <Head title="Calendario de Disponibilidad" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">
                Calendario de disponibilidad
            </h2>
        </template>

        <!-- Filtros -->
        <div
            class="max-w-7xl mx-auto px-4 mt-6 mb-4 flex flex-wrap items-end gap-3"
        >
            <div>
                <label class="text-sm font-medium">Desde</label>
                <input
                    type="date"
                    class="block border rounded p-2"
                    :value="from"
                    @change="(e) => go({ from: e.target.value, days, tipo })"
                />
            </div>

            <div>
                <label class="text-sm font-medium">Días</label>
                <select
                    class="block border rounded p-2"
                    :value="days"
                    @change="
                        (e) => go({ from, days: Number(e.target.value), tipo })
                    "
                >
                    <option :value="7">7</option>
                    <option :value="14">14</option>
                    <option :value="21">21</option>
                    <option :value="30">30</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium">Tipo</label>
                <select
                    class="block border rounded p-2"
                    :value="tipo || ''"
                    @change="
                        (e) => go({ from, days, tipo: e.target.value || null })
                    "
                >
                    <option value="">Todos</option>
                    <option value="simple">Simple</option>
                    <option value="doble">Doble</option>
                    <option value="triple">Triple</option>
                    <option value="cuadruple">Cuádruple</option>
                </select>
            </div>

            <!-- 🔹 NUEVO: Hasta + botón -->
            <div>
                <label class="text-sm font-medium">Hasta</label>
                <input
                    type="date"
                    class="block border rounded p-2"
                    v-model="hasta"
                    :min="from"
                />
                <!-- opcional: no dejar elegir menos que 'desde' -->
            </div>

            <button
                type="button"
                class="px-4 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50"
                @click="generarReserva"
                :disabled="!hasta"
            >
                Generar reserva
            </button>

            <div class="ml-auto flex gap-2 text-sm">
                <!-- leyenda colores -->
                <span class="inline-flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-yellow-400"></span>Pendiente
                </span>
                <span class="inline-flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-rose-500"></span>Ocupada
                </span>
                <span class="inline-flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-amber-500"></span>Limpieza
                </span>
                <span class="inline-flex items-center gap-2">
                    <span class="w-3 h-3 rounded bg-purple-500"></span
                    >Mantenimiento
                </span>
            </div>
        </div>

        <!-- Calendario -->
        <div class="max-w-7xl mx-auto px-4">
            <div
                class="overflow-x-auto rounded-lg ring-1 ring-black/5 bg-white"
                id="cal-wrapper"
            >
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
                    :style="{ minWidth: `calc(260px + ${days} * 60px)` }"
                >
                    <!-- Columna izquierda fija -->
                    <div
                        class="flex items-center gap-2 px-3 py-2 bg-white border-r w-[260px] absolute left-0 top-0 bottom-0"
                    >
                        <div>
                            <div class="font-semibold">Hab. {{ r.numero }}</div>
                            <div class="text-xs text-gray-500 capitalize">
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
                            class="h-8 border-r last:border-r-0 border-gray-200 relative select-none"
                        >
                            <!-- Línea de hoy -->
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
                                    gridColumn: `${colIndex(b.start) + 1} / ${
                                        colIndex(b.end) + 1
                                    }`,
                                }"
                            >
                                {{ b.huesped }} — {{ b.start }} → {{ b.end }}
                            </div>
                        </template>

                        <!-- Bloqueos (limpieza/mantenimiento) -->
                        <template v-for="(b, j) in r.blocks" :key="`blk-${j}`">
                            <div
                                class="h-8 rounded-md flex items-center justify-center text-xs text-gray-800 font-medium"
                                :class="colorState(b.state)"
                                :style="{ gridColumn: `1 / ${days + 1}` }"
                            >
                                {{ b.state }}
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Ajustes finos que necesites para el grid */
</style>
