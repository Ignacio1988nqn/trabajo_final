<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  reservas: { type: Array, default: () => [] }
})

function fDate(val) {
  if (!val) return '—'
  const d = new Date(val)
  return isNaN(d) ? val : d.toLocaleDateString('es-AR') // 21/10/2025
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

            <Link
              :href="route('reservas.create')"
              class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
            >
              Alta Reserva
            </Link>

            <div class="mt-4 overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">Huésped</th>
                    <th class="px-4 py-2 text-left">N° Habitación</th>
                    <th class="px-4 py-2 text-left">Check-in</th>
                    <th class="px-4 py-2 text-left">Check-out</th>
                    <th class="px-4 py-2 text-left">Estado</th>
                  </tr>
                </thead>

                <tbody v-if="props.reservas.length" class="divide-y divide-gray-100">
                  <!-- usar asignacion_id si existe; si no, una key basada en la reserva -->
                  <tr
                    v-for="r in props.reservas"
                    :key="r.asignacion_id ?? `R-${r.reserva_id}`"
                    class="hover:bg-gray-50"
                  >
                    <!-- número de reserva -->
                    <td class="px-4 py-2">{{ r.reserva_id }}</td>

                    <td class="px-4 py-2">{{ r.huesped_nombre }}</td>

                    <td class="px-4 py-2">
                      {{ r.habitacion_numero ? r.habitacion_numero : '—' }}
                    </td>

                    <!-- fechas por asignación -->
                    <td class="px-4 py-2">{{ fDate(r.checkin_det) }}</td>
                    <td class="px-4 py-2">{{ fDate(r.checkout_det) }}</td>

                    <td class="px-4 py-2">
                      <span
                        class="px-3 py-1 rounded-full text-sm font-medium capitalize"
                        :class="{
                          'text-yellow-800 bg-yellow-100': r.estado === 'pendiente',
                          'text-green-800 bg-green-100':  r.estado === 'checkin',
                          'text-blue-800  bg-blue-100':   r.estado === 'checkout',
                          'text-red-800   bg-red-100':    r.estado === 'cancelada'
                        }"
                      >
                        {{ r.estado }}
                      </span>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr>
                    <td class="px-4 py-2 text-gray-500 text-center" colspan="6">
                      Sin reservas aún.
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
