<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

const props = defineProps({
  reservas: { type: Array, default: () => [] },  // 1 fila por asignación
  ref_mode: { type: String, default: 'plan' }    // 'plan' | 'hoy'
})

function confirmCheckin(detalleId) {
  Swal.fire({
    title: '¿Confirmar check-in?',
    text: 'Se verificará disponibilidad y se ocuparán las habitaciones vigentes.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, confirmar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
  }).then((result) => {
    if (!result.isConfirmed) return

    router.post(route('checkin.do', detalleId), {
      detalle: detalleId
    }, {
      onSuccess: () => {
        Swal.fire('¡Éxito!', 'Check-in realizado.', 'success')
      },
      onError: (errors) => {
        Swal.fire('Error', errors?.error || 'No se pudo realizar el check-in.', 'error')
      },
      preserveScroll: true,
    })
  })
}

function fmt(d) {
  return d ?? '—'
}
</script>

<template>

  <Head title="Check In" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Check-in de Reservas
      </h2>
    </template>

    <div class="mb-4 flex gap-2">
      <Link :href="route('checkin.index', { ref: 'plan' })" class="px-3 py-1 border rounded"
        :class="{ 'bg-blue-600 text-white': props.ref_mode === 'plan' }">
      Planificadas
      </Link>
      <Link :href="route('checkin.index', { ref: 'hoy' })" class="px-3 py-1 border rounded"
        :class="{ 'bg-blue-600 text-white': props.ref_mode === 'hoy' }">
      Solo hoy
      </Link>
    </div>

    <div v-if="$page.props.errors?.error" class="mb-4 rounded bg-red-100 p-3 text-red-700">
      {{ $page.props.errors.error }}
    </div>

    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Reserva
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Huésped
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Habitación
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Fecha de Reserva
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Check-in (asig.)
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Check-out (asig.)
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Estado
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>

              <tbody v-if="props.reservas.length" class="bg-white divide-y divide-gray-200">
                <tr v-for="r in props.reservas" :key="r.asignacion_id"
                  class="hover:bg-gray-50 transition-colors duration-200">
                  <!-- Nro. Reserva -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    #{{ r.reserva_id }}
                  </td>

                  <!-- Huésped -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ r.huesped_nombre }}
                  </td>

                  <!-- Habitación (badge bonito) -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                      {{ r.habitacion_numero }}
                    </span>
                  </td>

                  <!-- Fecha de reserva -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                    {{ fmt(r.fecha_reserva) }}
                  </td>

                  <!-- Check-in asignado -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                    {{ fmt(r.checkin_det) }}
                  </td>

                  <!-- Check-out asignado -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                    {{ fmt(r.checkout_det) }}
                  </td>

                  <!-- Estado (mismo esquema de colores que las otras tablas) -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full capitalize" :class="{
                      'bg-yellow-100 text-yellow-800': r.estado === 'pendiente',
                      'bg-green-100 text-green-800': r.estado === 'checkin',
                      'bg-blue-100 text-blue-800': r.estado === 'checkout',
                      'bg-red-100 text-red-800': r.estado === 'cancelada',
                      'bg-gray-100 text-gray-800': !['pendiente', 'checkin', 'checkout', 'cancelada'].includes(r.estado)
                    }">
                      {{ r.estado || 'desconocido' }}
                    </span>
                  </td>

                  <!-- Botón de Check-in -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button @click="confirmCheckin(r.detalle_id)"
                      class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                      Check-in
                    </button>
                  </td>
                </tr>
              </tbody>

              <!-- Sin reservas -->
              <tbody v-else>
                <tr>
                  <td colspan="8" class="px-6 py-12 text-center text-gray-500 bg-gray-50">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-lg font-medium">No hay reservas pendientes de check-in</p>
                    <p class="text-sm mt-1">Las reservas listas para hacer check-in aparecerán aquí.</p>
                  </td>
                </tr>
              </tbody>
            </table>

          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
