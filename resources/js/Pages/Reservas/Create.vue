<script setup>
/**
 * Importamos helpers de Inertia y el layout autenticado
 */
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

/**
 * Props que vienen desde el backend:
 * - huespedes: lista de huéspedes para seleccionar
 * - habitaciones: lista de habitaciones disponibles
 */
const props = defineProps({
  huespedes: { type: Array, default: () => [] },
  habitaciones: { type: Array, default: () => [] }
})

/**
 * Formulario con los campos de la reserva
 * useForm gestiona estado, errores y submit a Inertia
 */
const form = useForm({
  huesped_id: '',
  habitacion_id: '',
  fecha_checkin: '',
  fecha_checkout: '',
  estado: 'reservada',
  observaciones: ''
})

/**
 * Acción al enviar el formulario
 * post() envía al endpoint reservas.store definido en Laravel
 */
function submit() {
  form.post(route('reservas.store'))
}
</script>

<template>
  <!-- Título de la página en el navegador -->
  <Head title="Nueva reserva" />

  <!-- Layout común con navbar, etc -->
  <AuthenticatedLayout>
    <!-- Slot del header, título grande arriba -->
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva reserva</h2>
    </template>

    <!-- Contenedor principal -->
    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">

            <!-- Formulario de creación de reserva -->
            <form @submit.prevent="submit" class="space-y-5">

              <!-- Selección de huésped -->
              <div>
                <label class="block text-sm font-medium mb-1">Huésped</label>
                <select v-model="form.huesped_id" class="border rounded w-full p-2">
                  <option value="">Seleccionar…</option>
                  <option v-for="h in props.huespedes" :key="h.id" :value="h.id">
                    {{ h.display ?? h.nombre }}
                  </option>
                </select>
                <!-- Mostrar errores -->
                <p v-if="form.errors.huesped_id" class="text-red-600 text-sm mt-1">{{ form.errors.huesped_id }}</p>
              </div>

              <!-- Fechas de check-in y check-out -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium mb-1">Check-in</label>
                  <input type="date" v-model="form.fecha_checkin" class="border rounded w-full p-2" />
                  <p v-if="form.errors.fecha_checkin" class="text-red-600 text-sm mt-1">{{ form.errors.fecha_checkin }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Check-out</label>
                  <input type="date" v-model="form.fecha_checkout" class="border rounded w-full p-2" />
                  <p v-if="form.errors.fecha_checkout" class="text-red-600 text-sm mt-1">{{ form.errors.fecha_checkout }}</p>
                </div>
              </div>

              <!-- Selección de habitación -->
              <div>
                <label class="block text-sm font-medium mb-1">Habitación</label>
                <select v-model="form.habitacion_id" class="border rounded w-full p-2">
                  <option value="">Seleccionar…</option>
                  <option v-for="hab in props.habitaciones" :key="hab.id" :value="hab.id">
                    Nº {{ hab.numero }} — {{ hab.tipo }}
                  </option>
                </select>
                <p v-if="form.errors.habitacion_id" class="text-red-600 text-sm mt-1">{{ form.errors.habitacion_id }}</p>
              </div>

              <!-- Estado + observaciones -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium mb-1">Estado</label>
                  <select v-model="form.estado" class="border rounded w-full p-2">
                    <option value="reservada">Reservada</option>
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

              <!-- Botones de acción -->
              <div class="flex gap-3">
                <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50"
                        :disabled="form.processing">
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
