<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useCalendarioDisponibilidad  } from '@/composables/useCalendarioDisponibilidad'
const props = defineProps({
  from: String,
  days: Number,
  daysArr: Array,
  tipo: String,
  rooms: Array,
})

// const { colIndex, spanDays, gridTemplate, go, hoy, colorState } =
//   useCalendarioDisponibilidad(props)
  
const {
  colIndex, spanDays, gridTemplate, go, hoy, colorState,
  onCellMouseDown, onCellMouseEnter, finishSelection, isCellSelected
} = useCalendarioDisponibilidad(props)
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
    <div class="max-w-7xl mx-auto px-4 mt-6 mb-4 flex flex-wrap items-end gap-3">
      <div>
        <label class="text-sm font-medium">Desde</label>
        <input type="date" class="block border rounded p-2"
               :value="from"
               @change="e => go({from: e.target.value, days, tipo})">
      </div>
      <div>
        <label class="text-sm font-medium">Días</label>
        <select class="block border rounded p-2"
                :value="days"
                @change="e => go({from, days: e.target.value, tipo})">
          <option :value="7">7</option>
          <option :value="14">14</option>
          <option :value="21">21</option>
          <option :value="30">30</option>
        </select>
      </div>
      <div>
        <label class="text-sm font-medium">Tipo</label>
        <select class="block border rounded p-2"
                :value="tipo || ''"
                @change="e => go({from, days, tipo: e.target.value || null})">
          <option value="">Todos</option>
          <option value="simple">Simple</option>
          <option value="doble">Doble</option>
          <option value="triple">Triple</option>
          <option value="cuadruple">Cuádruple</option>
        </select>
      </div>
      <div class="ml-auto flex gap-2 text-sm">
        <span class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded bg-yellow-400"></span>Pendiente</span>
        <span class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded bg-rose-500"></span>Ocupada</span>
        <span class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded bg-amber-500"></span>Limpieza</span>
        <span class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded bg-purple-500"></span>Mantenimiento</span>
      </div>
    </div>
    
   <!-- Calendario -->
<div class="max-w-7xl mx-auto px-4" @mouseup="finishSelection">
  
  <div class="overflow-x-auto rounded-lg ring-1 ring-black/5 bg-white" id="cal-wrapper">
    <!-- header de días -->
    <div
      class="grid sticky top-0 z-10 bg-white"
      :style="{
        gridTemplateColumns: gridTemplate,
        minWidth: `calc(260px + ${days} * 60px)` // 260 fijo + 60px por día (match a tu minmax)
      }"
    >
      <div class="border-b px-3 py-2 font-semibold">Habitación</div>
      <div v-for="d in daysArr" :key="d.iso"
           class="border-b px-2 py-2 text-center text-sm">
        <div class="font-semibold">{{ d.dow }}</div>
        <div>{{ d.dom }}</div>
      </div>
    </div>


    <!-- filas -->
    <div
      v-for="r in rooms" :key="r.id"
      class="relative border-b"
      :style="{ minWidth: `calc(260px + ${days} * 60px)` }"
    >

    <!-- Columna izquierda -->
    <div class="flex items-center gap-2 px-3 py-2 bg-white border-r w-[260px] absolute left-0 top-0 bottom-0">
      <div>
        <div class="font-semibold">Hab. {{ r.numero }}</div>
        <div class="text-xs text-gray-500 capitalize">{{ r.tipo }}</div>
      </div>
      <span class="ml-auto text-xs capitalize px-2 py-1 rounded bg-gray-100">{{ r.estado }}</span>
    </div>

    <!-- Grilla de días -->
    <div class="grid ml-[260px] relative"
         :style="{ gridTemplateColumns: `repeat(${days}, minmax(3rem, 1fr))` }">

      <!-- columnas de fondo -->
      <!-- <div v-for="d in daysArr" :key="d.iso + r.id"
           class="h-8 border-r last:border-r-0 border-gray-200 relative"> -->
        <!-- Línea de hoy -->
        <!-- <div v-if="d.iso===hoy" class="absolute inset-y-0 w-0.5 bg-indigo-500 left-0"></div>
      </div> -->
      
<!-- columnas de fondo con selección -->
<div
  v-for="(d, dIdx) in daysArr"
  :key="d.iso + r.id"
  class="h-8 border-r last:border-r-0 border-gray-200 relative select-none"
  @mousedown.prevent="onCellMouseDown(r, dIdx)"
  @mouseenter="onCellMouseEnter(r, dIdx)"
>
  <!-- Línea de hoy -->
  <div v-if="d.iso===hoy" class="absolute inset-y-0 w-0.5 bg-indigo-500 left-0"></div>

  <!-- Highlight de selección -->
  <div
    v-if="isCellSelected(dIdx, r)"
    class="absolute inset-0 bg-indigo-400/20 ring-1 ring-indigo-400/40 rounded"
  ></div>
</div>



      <!-- Bloques de reservas -->
      <template v-for="(b, j) in r.bookings" :key="j">
        <div
          class="h-8 flex items-center text-xs font-medium text-white rounded-md px-2 overflow-hidden whitespace-nowrap shadow-sm"
          :class="colorState(b.state)"
          :style="{ gridColumn: `${colIndex(b.start)+1} / ${colIndex(b.end)+1}` }"
        >
          {{ b.huesped }} — {{ b.start }} → {{ b.end }}
        </div>
      </template>

      <!-- Bloqueos de mantenimiento/limpieza -->
      <template v-for="(b, j) in r.blocks" :key="'blk'+j">
        <div class="h-8 rounded-md flex items-center justify-center text-xs text-gray-800 font-medium"
             :class="colorState(b.state)"
             :style="{ gridColumn: `1 / ${days+1}` }">
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
/* Truco: hacer que los "absolute" respeten grid columns usando display:grid en la fila ya lo permite.
   Si tu build no respeta grid placement con absolute, podés envolver cada fila en un div grid relativo
   y usar inline styles con calc() para left/width. Esta versión usa grid-column-start/span. */
</style>
