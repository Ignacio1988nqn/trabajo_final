<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { watch } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useReservaCreate } from '@/composables/useReservaCreate'
import { useHuespedSearch } from '@/composables/useHuespedSearch'

const props = defineProps({
  huespedes: { type: Array, default: () => [] },
  habitaciones: { type: Array, default: () => [] }
})

const {
  form,
  disponibles,
  addAsignacion,
  removeAsignacion,
  submit,
  setupWatchers,
  isSeleccionDisponible
} = useReservaCreate()

// Buscador huésped
const buscador = useHuespedSearch()

// copiá el id elegido al form
watch(() => buscador.state.seleccionadoId, (val) => {
  form.huesped_id = val || ''
})

// inicializar watchers de fechas
setupWatchers()
</script>
<template>
  <Head title="Nueva reserva" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva reserva</h2>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <form @submit.prevent="submit" class="space-y-6">
<!-- Huésped (buscador personas+empresas) -->
<div class="relative">
  <label class="block text-sm font-medium mb-1">Huésped</label>

  <div class="flex gap-2">
    <input
      type="text"
      v-model="buscador.state.busq"
      @input="buscador.onInput"
      @keydown="buscador.onKeydown"
      @blur="buscador.blurConDelay"
      placeholder="Buscar por Apellido/Nombre/DNI o Razón Social/CUIT"
      class="border rounded w-full p-2"
      autocomplete="off"
    />
    <button
      type="button"
      @click="buscador.limpiar"
      class="px-3 py-2 border rounded hover:bg-gray-50"
    >
      Limpiar
    </button>
  </div>

  <div v-if="buscador.state.cargando" class="text-xs text-gray-500 mt-1">Buscando…</div>

  <!-- Dropdown resultados -->
  <div
    v-if="buscador.state.abierto && buscador.state.opciones.length"
    class="absolute z-10 mt-1 w-full max-h-60 overflow-auto border rounded bg-white shadow"
    role="listbox"
  >
    <button
      v-for="(op, i) in buscador.state.opciones"
      :key="op.id"
      type="button"
      class="w-full text-left px-3 py-2 hover:bg-gray-50"
      :class="i === buscador.state.highlighted ? 'bg-gray-100' : ''"
      @mousedown.prevent="buscador.seleccionar(op)"
      role="option"
    >
      {{ op.display }}
    </button>
  </div>

  <!-- id real que viaja al backend -->
  <input type="hidden" v-model="form.huesped_id" />

  <p v-if="form.errors.huesped_id" class="text-red-600 text-sm mt-1">
    {{ form.errors.huesped_id }}
  </p>
</div>
              <!-- Habitaciones asignadas -->
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <label class="text-sm font-medium">Habitaciones asignadas</label>
                  <button type="button" @click="addAsignacion" class="text-sm px-3 py-1 rounded border hover:bg-gray-50">
                    + Agregar habitación
                  </button>
                </div>

                <div v-for="(a, idx) in form.asignaciones" :key="idx" class="rounded-xl border p-4">
                  <div class="flex items-center justify-between mb-3">
                    <h4 class="font-semibold">Habitación #{{ idx + 1 }}</h4>
                    <button
                      type="button"
                      @click="removeAsignacion(idx)"
                      class="text-sm px-3 py-1 rounded border hover:bg-gray-50"
                      :disabled="form.asignaciones.length === 1"
                    >
                      Quitar
                    </button>
                  </div>

                  <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 md:col-span-6">
                      <label class="block text-sm font-medium mb-1">Habitación</label>

                      <div class="flex items-center gap-2">
                        <select
                          v-model="a.habitacion_id"
                          class="border rounded w-full p-2"
                          :disabled="!a.fecha_inicio || !a.fecha_fin || (disponibles[idx]||[]).length===0"
                        >
                          <option value="">
                            {{ (a.fecha_inicio && a.fecha_fin) ? 'Seleccionar...' : 'Definí inicio y fin' }}
                          </option>
                          <option
                            v-for="hab in (disponibles[idx] || [])"
                            :key="hab.id"
                            :value="hab.id"
                          >
                            Nº {{ hab.numero }} — {{ hab.tipo }}
                          </option>
                        </select>

                        <!-- Badge Disponible/Ocupada -->
                        <span
                          v-if="a.habitacion_id"
                          class="text-xs px-2 py-1 rounded whitespace-nowrap"
                          :class="isSeleccionDisponible(idx, a.habitacion_id)
                                  ? 'bg-green-100 text-green-700'
                                  : 'bg-red-100 text-red-700'"
                        >
                          {{ isSeleccionDisponible(idx, a.habitacion_id) ? 'Disponible' : 'Ocupada' }}
                        </span>
                      </div>

                      <p v-if="form.errors[`asignaciones.${idx}.habitacion_id`]" class="text-red-600 text-sm mt-1">
                        {{ form.errors[`asignaciones.${idx}.habitacion_id`] }}
                      </p>
                    </div>

                    <div class="col-span-6 md:col-span-3">
                      <label class="block text-sm font-medium mb-1">Inicio</label>
                      <input
                        type="date"
                        v-model="a.fecha_inicio"
                        class="border rounded w-full p-2"
                      />
                      <p v-if="form.errors[`asignaciones.${idx}.fecha_inicio`]" class="text-red-600 text-sm mt-1">
                        {{ form.errors[`asignaciones.${idx}.fecha_inicio`] }}
                      </p>
                    </div>

                    <div class="col-span-6 md:col-span-3">
                      <label class="block text-sm font-medium mb-1">Fin</label>
                      <input
                        type="date"
                        v-model="a.fecha_fin"
                        class="border rounded w-full p-2"
                        :min="a.fecha_inicio || undefined"
                        required
                      />
                      <p v-if="form.errors[`asignaciones.${idx}.fecha_fin`]" class="text-red-600 text-sm mt-1">
                        {{ form.errors[`asignaciones.${idx}.fecha_fin`] }}
                      </p>
                    </div>
                  </div>
                </div>

                <p v-if="form.errors.asignaciones" class="text-red-600 text-sm">
                  {{ form.errors.asignaciones }}
                </p>
              </div>

              <!-- Estado / observaciones -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium mb-1">Estado</label>
                  <select v-model="form.estado" class="border rounded w-full p-2">
                    <option value="pendiente">Pendiente</option>
                    <option value="checkin">Check-in</option>
                    <option value="checkout">Check-out</option>
                    <option value="cancelada">Cancelada</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Observaciones</label>
                  <input type="text" v-model="form.observaciones" class="border rounded w-full p-2" />
                </div>
              </div>

              <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" :disabled="form.processing">
                  Guardar
                </button>
                <Link :href="route('reservas.index')" class="px-4 py-2 rounded border hover:bg-gray-50">
                  Cancelar
                </Link>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
