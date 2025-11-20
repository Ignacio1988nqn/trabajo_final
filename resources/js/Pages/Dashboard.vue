<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import CardLink from "@/Components/CardLink.vue";
import KpiCard from "@/Components/KpiCard.vue";

const props = defineProps({
    kpis: {
        type: Object,
        default: () => ({
            ocupacion_hoy: 0,
            habitaciones_disponibles: 0,
            checkins_hoy: 0,
        }),
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-[#0F3D3E]"
                    >
                        Panel general
                    </h1>
                    <p class="mt-1 text-sm text-neutral-500">
                        Gestión hotelera · accesos rápidos
                    </p>
                </div>
                <div
                    class="rounded-xl border border-black/5 bg-white px-4 py-2 text-sm shadow-sm"
                >
                    <span class="font-medium text-[#0F3D3E]">Hotel</span>
                    <span class="mx-2 text-neutral-300">•</span>
                    <span class="text-neutral-500">We Key</span>
                </div>
            </div>
        </template>

        <!-- GRID de accesos -->
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <CardLink
                title="Huésped"
                desc="Crear y administrar huéspedes"
                :to="route('huesped.index')"
            />
            <CardLink
                title="Reservas"
                desc="Gestionar reservas y calendario"
                :to="route('reservas.index')"
            />
            <CardLink
                title="Check-In"
                desc="Ingresos y asignaciones"
                :to="route('checkin.index')"
            />
            <CardLink
                title="Check-Out"
                desc="Egresos y paso a limpieza"
                :to="route('checkout.index')"
            />
            <CardLink
                title="Habitaciones"
                desc="Estados y mantenimiento"
                :to="route('habitaciones.index')"
            />
            <CardLink
                title="Disponibilidad"
                desc="Vista de ocupación"
                :to="route('disponibilidad.index')"
            />
            <CardLink
                title="Gastos"
                desc="Consumos y cargos"
                :to="route('gastos.index')"
            />
        </section>

        <!-- KPIs con numeritos animados -->
        <section class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <KpiCard
                label="Ocupación"
                chip="Hoy"
                :value="props.kpis.ocupacion_hoy"
                suffix="%"
                subtitle="Porcentaje de habitaciones ocupadas."
            />

            <KpiCard
                label="Disponibles"
                chip="Habitaciones"
                :value="props.kpis.habitaciones_disponibles"
                subtitle="Listas para asignación."
            />

            <KpiCard
                label="Check-ins"
                chip="Programados"
                :value="props.kpis.checkins_hoy"
                subtitle="Entradas previstas para hoy."
            />
        </section>

        <footer class="mt-10 text-center text-xs text-neutral-400">
            © {{ new Date().getFullYear() }} We Key · Gestión Hotelera
        </footer>
    </AuthenticatedLayout>
</template>
