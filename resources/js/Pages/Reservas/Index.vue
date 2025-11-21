<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  reservas: { type: Array, default: () => [] }
})
function fDate(val) {
  if (!val) return '—'
  // Si viene en formato YYYY-MM-DD
  if (/^\d{4}-\d{2}-\d{2}$/.test(val)) {
    const [y, m, d] = val.split('-')
    return `${d}/${m}/${y}`
  }

  const d = new Date(val.replace(' ', 'T'))
  return isNaN(d) ? val : d.toLocaleDateString('es-AR')
}


</script>

<template>

  <Head title="Reservas" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reservas</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">

            <Link :href="route('reservas.create')"
              class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Alta Reserva
            </Link>

            <div class="mt-4 overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Nro. Reserva
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Huésped
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      N° Habitación
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Check-in
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Check-out
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Estado
                    </th>
                  </tr>
                </thead>

                <tbody v-if="props.reservas.length" class="bg-white divide-y divide-gray-200">
                  <tr v-for="r in props.reservas" :key="r.asignacion_id ?? `R-${r.reserva_id}`"
                    class="hover:bg-gray-50 transition-colors duration-200">
                    <!-- Nro. Reserva -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      #{{ r.reserva_id }}
                    </td>

                    <!-- Huésped -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ r.huesped_nombre }}
                    </td>

                    <!-- Habitación -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span v-if="r.habitacion_numero"
                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                        {{ r.habitacion_numero }}
                      </span>
                      <span v-else class="text-gray-400 text-sm">—</span>
                    </td>

                    <!-- Check-in -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      {{ fDate(r.checkin_det) }}
                    </td>

                    <!-- Check-out -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      {{ fDate(r.checkout_det) }}
                    </td>

                    <!-- Estado con badges más elegantes -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" :class="{
                        'bg-yellow-100 text-yellow-800': r.estado === 'pendiente',
                        'bg-green-100 text-green-800': r.estado === 'checkin',
                        'bg-blue-100 text-blue-800': r.estado === 'checkout',
                        'bg-red-100 text-red-800': r.estado === 'cancelada',
                        'bg-gray-100 text-gray-800': !['pendiente', 'checkin', 'checkout', 'cancelada'].includes(r.estado)
                      }">
                        {{ r.estado || 'desconocido' }}
                      </span>
                    </td>
                  </tr>
                </tbody>

                <!-- Sin reservas -->
                <tbody v-else>
                  <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 bg-gray-50">
                      <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-4a2 2 0 00-2 2v3m-4-5h.01">
                        </path>
                      </svg>
                      <p class="text-lg font-medium">Sin reservas aún</p>
                      <p class="text-sm">Cuando lleguen reservas aparecerán aquí.</p>
                    </td>
                  </tr>
                </tbody>
              </table>             
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
