// resources/js/composables/useDashboardCards.js
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

export function useDashboardCards() {
    const page = usePage();

    // 👇 ajustá "rol" si en tu modelo se llama "role"
    const userRole = computed(() => page.props.auth.user?.rol ?? null);

    const cards = [
        {
            title: "Huésped",
            desc: "Crear y administrar huéspedes",
            routeName: "huesped.index",
            rolesDenied: ["limpieza", "mantenimiento"],
        },
        {
            title: "Reservas",
            desc: "Gestionar reservas y calendario",
            routeName: "reservas.index",
            rolesDenied: ["limpieza", "mantenimiento"],
        },
        {
            title: "Check-In",
            desc: "Ingresos y asignaciones",
            routeName: "checkin.index",
            rolesDenied: ["limpieza", "mantenimiento"],
        },
        {
            title: "Check-Out",
            desc: "Egresos y paso a limpieza",
            routeName: "checkout.index",
            rolesDenied: ["limpieza", "mantenimiento"],
        },
        {
            title: "Habitaciones",
            desc: "Estados y mantenimiento",
            routeName: "habitaciones.index",
            rolesDenied: [],
        },
        {
            title: "Disponibilidad",
            desc: "Vista de ocupación",
            routeName: "disponibilidad.index",
            rolesDenied: ["limpieza", "mantenimiento"],
        },
        {
            title: "Gastos",
            desc: "Consumos y cargos",
            routeName: "gastos.index",
            rolesDenied: ["limpieza", "mantenimiento"],
        },
        {
            title: "Checkear Habitaciones",
            desc: "Habitaciones en sin limpiar",
            routeName: "limpieza.index",
            rolesDenied: ["recepcion"],
        },
        {
            title: "Estadisticas",
            desc: "Consultar metricas del hotel",
            routeName: "estadisticas.index",
            rolesDenied: ["limpieza", "mantenimiento"],
        },
    ];

    const canShow = (card) => {
        if (!userRole.value) return true;
        return !card.rolesDenied.includes(userRole.value);
    };

    const visibleCards = computed(() =>
        cards.filter((card) => canShow(card))
    );

    return {
        userRole,
        cards,
        visibleCards,
        canShow,
    };
}