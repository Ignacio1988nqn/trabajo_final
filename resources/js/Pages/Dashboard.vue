<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight text-[#0F3D3E]">Panel general</h1>
          <p class="mt-1 text-sm text-neutral-500">Gestión hotelera · accesos rápidos</p>
        </div>
        <div class="rounded-xl border border-black/5 bg-white px-4 py-2 text-sm shadow-sm">
          <span class="font-medium text-[#0F3D3E]">Hotel</span>
          <span class="mx-2 text-neutral-300">•</span>
          <span class="text-neutral-500">We Key</span>
        </div>
      </div>
    </template>

    <!-- GRID de accesos -->
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <CardLink title="Huésped" desc="Crear y administrar huéspedes" :to="route('huesped.index')" />
      <CardLink title="Reservas" desc="Gestionar reservas y calendario" :to="route('reservas.index')" />
      <CardLink title="Check-In" desc="Ingresos y asignaciones" :to="route('checkin.index')" />
      <CardLink title="Check-Out" desc="Egresos y paso a limpieza" :to="route('checkout.index')" />
      <CardLink title="Habitaciones" desc="Estados y mantenimiento" :to="route('habitaciones.index')" />
      <CardLink title="Disponibilidad" desc="Vista de ocupación" :to="route('disponibilidad.index')" />
      <CardLink title="Gastos" desc="Consumos y cargos" :to="route('gastos.index')" />
    </section>

    <!-- (Opcional) KPIs vacíos para completar luego -->
    <section class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <KpiCard label="Ocupación" chip="Hoy" value="— %" subtitle="Porcentaje de habitaciones ocupadas." />
      <KpiCard label="Disponibles" chip="Habitaciones" value="—" subtitle="Listas para asignación." />
      <KpiCard label="Check-ins" chip="Programados" value="—" subtitle="Entradas previstas para hoy." />
    </section>

    <footer class="mt-10 text-center text-xs text-neutral-400">
      © {{ new Date().getFullYear() }} We Key · Gestión Hotelera
    </footer>
  </AuthenticatedLayout>
</template>

<script>
export default {
  components: {
    CardLink: {
      props: { title: String, desc: String, to: String },
      components: { Link }, // 👈 Registramos Link acá
      template: `
        <Link :href="to"
          class="group block rounded-2xl border border-black/5 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#C69C6D]/40">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-[#0F3D3E]">{{ title }}</h3>
            <span class="rounded-lg bg-[#0F3D3E] px-2 py-1 text-xs text-white transition group-hover:opacity-90">Abrir</span>
          </div>
          <p class="mt-2 text-sm text-neutral-500">{{ desc }}</p>
        </Link>
      `
    },
    KpiCard: {
      props: { label: String, chip: String, value: String, subtitle: String },
      template: `
        <article class="rounded-2xl border border-black/5 bg-white p-5 shadow-sm">
          <div class="text-xs uppercase tracking-widest text-neutral-400">{{ label }}</div>
          <div class="mt-2 flex items-end justify-between">
            <p class="text-3xl font-semibold text-[#0F3D3E]">{{ value }}</p>
            <span class="rounded-lg bg-[#C69C6D]/10 px-2 py-1 text-xs font-medium text-[#8A6A47]">{{ chip }}</span>
          </div>
          <p class="mt-2 text-sm text-neutral-500">{{ subtitle }}</p>
        </article>
      `
    }
  }
}
</script>
