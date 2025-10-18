<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  reservas: { type: Array, default: () => [] }
})
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

            <!-- Botón de alta -->
            <Link
              :href="route('reservas.create')"
              class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
            >
              Alta Reserva
            </Link>

            <!-- Tabla -->
            <div class="mt-4 overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">Huésped</th>
                    <th class="px-4 py-2 text-left">Tipo Habitación</th>
                    <th class="px-4 py-2 text-left">Check-in</th>
                    <th class="px-4 py-2 text-left">Check-out</th>
                    <th class="px-4 py-2 text-left">Estado</th>
                  </tr>
                </thead>

                <!-- Si hay reservas -->
                <tbody v-if="props.reservas.length" class="divide-y divide-gray-100">
                  <tr v-for="r in props.reservas" :key="r.id" class="hover:bg-gray-50">
                    <td class="px-4 py-2">{{ r.id }}</td>
                    <td class="px-4 py-2">{{ r.huesped_nombre }}</td>
                    <td class="px-4 py-2">
                      {{ r.habitacion_tipo ? r.habitacion_tipo : '—' }}
                    </td>
                    <td class="px-4 py-2">{{ r.fecha_checkin }}</td>
                    <td class="px-4 py-2">{{ r.fecha_checkout }}</td>
                    <td class="px-4 py-2">
                      <span
                        class="px-3 py-1 rounded-full text-sm font-medium capitalize"
                        :class="{
                          'text-yellow-800 bg-yellow-100': r.estado === 'pendiente',
                          'text-green-800 bg-green-100': r.estado === 'checkin',
                          'text-blue-800 bg-blue-100': r.estado === 'checkout',
                          'text-red-800 bg-red-100': r.estado === 'cancelada'
                        }"
                      >
                        {{ r.estado }}
                      </span>
                    </td>
                  </tr>
                </tbody>

                <!-- Si no hay reservas -->
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
