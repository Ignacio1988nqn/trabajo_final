// resources/js/composables/useEstadoHabitaciones.js
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

export function useEstadoHabitaciones(initial = {}) {
    const numeroSeleccionado = ref(initial.numero || "");
    const estadoSeleccionado = ref(initial.estado || "todos");
    const tipoSeleccionado = ref(initial.tipo || "todos");

    const aplicarFiltros = () => {
        router.get(
            route("habitaciones.index"),
            {
                numero: numeroSeleccionado.value || null,
                estado: estadoSeleccionado.value,
                tipo: tipoSeleccionado.value,
            },
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    };

    // Cada vez que cambie algún filtro, disparamos la búsqueda
    watch(
        [numeroSeleccionado, estadoSeleccionado, tipoSeleccionado],
        aplicarFiltros
    );

    return {
        numeroSeleccionado,
        estadoSeleccionado,
        tipoSeleccionado,
        aplicarFiltros,
    };
}
