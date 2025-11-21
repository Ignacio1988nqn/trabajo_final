<template>
  <Head title="Habitaciones asignadas a la reserva" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Habitaciones asignadas a la Reserva #{{ reserva.id }}
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

        <!-- Info general de la reserva -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
          <h3 class="text-lg font-bold mb-4">Datos de la Reserva</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <p class="text-sm text-gray-600">Cliente</p>
              <p class="font-bold text-lg">{{ nombreCliente }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Check-in</p>
              <p class="font-medium">{{ formatFecha(reserva.fecha_checkin) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Check-out</p>
              <p class="font-medium">{{ formatFecha(reserva.fecha_checkout) }}</p>
            </div>
          </div>
        </div>

        <!-- Tabla de asignaciones -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Habitaciones Asignadas</h3>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Detalle
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Habitación
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Check-in Asignado
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Check-out Asignado
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Estado
                  </th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="a in asignaciones" :key="a.detalle_id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 text-sm text-gray-600">
                    #{{ a.detalle_id }}
                  </td>
                  <td class="px-6 py-4 font-bold text-indigo-600">
                    Hab. {{ a.habitacion_numero }}
                  </td>
                  <td class="px-6 py-4 text-sm">
                    {{ formatFecha(a.fecha_inicio_asignada) }}
                  </td>
                  <td class="px-6 py-4 text-sm">
                    {{ formatFecha(a.fecha_fin_asignada) }}
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                          :class="{
                            'bg-green-100 text-green-800': a.estado === 'checkin',
                            'bg-red-100 text-red-800': a.estado === 'checkout',
                            'bg-yellow-100 text-yellow-800': a.estado === 'pendiente',
                            'bg-gray-100 text-gray-800': !a.estado
                          }">
                      {{ a.estado ? a.estado.toUpperCase() : 'SIN ESTADO' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <Link 
                      :href="route('gastos.show', a.detalle_id)"
                      class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-md hover:bg-emerald-700 transition shadow-sm"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                      Ver Gastos
                    </Link>
                  </td>
                </tr>

                <tr v-if="!asignaciones.length">
                  <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    No hay habitaciones asignadas a esta reserva.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <Link href="/gastos" class="text-sm font-medium text-gray-600 hover:text-gray-900">
              ← Volver al listado
            </Link>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  reserva: {
    type: Object,
    required: true
  },
  asignaciones: {
    type: Array,
    default: () => []
  }
})

const nombreCliente = computed(() => {
  const h = props.reserva?.huesped
  if (!h) return "Huésped no encontrado"

  if (h.tipo_huesped === "persona") {
    const p = h.personas?.[0] || h.personas
    return p ? `${p.nombre} ${p.apellido}`.trim() : "Sin nombre"
  }

  if (h.tipo_huesped === "empresa") {
    const e = h.empresas?.[0] || h.empresas
    return e?.razon_social || "Sin razón social"
  }

  return "Huésped desconocido"
})

const formatFecha = (f) => {
  if (!f) return "---"
  return new Date(f).toLocaleDateString("es-AR", {
    year: "numeric",
    month: "long",
    day: "numeric"
  })
}
</script>