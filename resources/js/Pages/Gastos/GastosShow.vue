<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { computed } from 'vue'

const props = defineProps({
  detalleReserva: Object,
  gastos: Array,
  habitacionActual: Object,
  checkinDetalle: [String, Date]
})

const tipoNombre = (gasto) => {
  return (gasto.item?.tipo || 'otro').toUpperCase()
}

const tipoClase = (gasto) => {
  const tipo = gasto.item?.tipo
  return {
    minibar: 'bg-blue-100 text-blue-800',
    confiteria: 'bg-purple-100 text-purple-800',
    lavanderia: 'bg-yellow-100 text-yellow-800',
    servicios: 'bg-orange-100 text-orange-800',
  }[tipo] || 'bg-gray-100 text-gray-800'
}
const detalle = computed(() => props.detalleReserva)
const reserva = computed(() => detalle.value?.reserva)
const nombreCliente = computed(() => {
  const h = reserva.value?.huesped
  if (!h) return 'Sin huésped'

  if (h.tipo_huesped === 'persona') {
    const p = h.personas?.[0] || h.personas
    return p ? `${p.nombre} ${p.apellido}`.trim() : 'Sin nombre'
  }
  if (h.tipo_huesped === 'empresa') {
    const e = h.empresas?.[0] || h.empresas
    return e?.razon_social || 'Sin razón social'
  }
  return 'Huésped desconocido'
})

const formatFecha = (fecha) => {
  if (!fecha) return '—';

  const [year, month, day] = fecha.split('T')[0].split('-');
  const date = new Date(year, month - 1, day);

  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

const formatMonto = (monto) => {
  const valor = parseFloat(monto || 0)
  return valor.toLocaleString('es-AR', { style: 'currency', currency: 'ARS' })
}

const totalGastos = computed(() => {
  if (!props.gastos?.length) return '$0,00'
  const total = props.gastos.reduce((sum, g) => sum + parseFloat(g.monto || 0), 0)
  return formatMonto(total)
})

const puedeCargarGastos = computed(() => {
  return ['checkin', 'Checkin', 'CHECKIN'].includes(detalle.value?.estado?.trim())
})
</script>

<template>

  <Head title="Gastos del Huésped" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Gastos - Detalle #{{ detalle.id }}
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
          <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div>
                <p class="text-sm text-gray-600">Cliente</p>
                <p class="font-bold text-lg">{{ nombreCliente }}</p>
              </div>

              <div>
                <p class="text-sm text-gray-600">Habitación</p>
                <p class="font-bold text-indigo-600 text-xl">
                  {{ habitacionActual?.numero ? `Hab. ${habitacionActual.numero}` : 'Sin asignar' }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Check-in</p>
                <p class="font-medium">{{ formatFecha(checkinDetalle) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Estado</p>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold" :class="{
                  'bg-green-100 text-green-800': puedeCargarGastos,
                  'bg-red-100 text-red-800': detalle.estado === 'checkout',
                  'bg-yellow-100 text-yellow-800': detalle.estado === 'pendiente',
                  'bg-gray-100 text-gray-700': !detalle.estado
                }">
                  {{ detalle.estado?.toUpperCase() || 'SIN ESTADO' }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <!-- Acciones: Cargar gasto + Volver -->
        <div class="flex justify-between items-center mb-6">
          <div v-if="puedeCargarGastos">
            <Link :href="route('gastos.create', detalle.id)"
              class="inline-flex items-center px-6 py-3 bg-emerald-600 text-white font-bold rounded-lg hover:bg-emerald-700 transition shadow-lg">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Cargar Nuevo Gasto
            </Link>
          </div>

          <div v-else class="text-sm text-gray-500 italic">
            Los gastos solo se pueden cargar durante el check-in.
          </div>
        </div>

        <!-- Tabla de gastos -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Gastos Registrados</h3>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="gasto in gastos" :key="gasto.id" class="hover:bg-gray-50 transition">
                  <!-- Descripción -->
                  <td class="px-6 py-4 text-sm font-medium text-gray-900">
                    {{ gasto.item?.nombre || 'Ítem eliminado' }}
                  </td>

                  <!-- Monto -->
                  <td class="px-6 py-4 text-sm font-bold text-green-600">
                    {{ formatMonto(gasto.monto) }}
                  </td>

                  <!-- Fecha -->
                  <td class="px-6 py-4 text-sm text-gray-600">
                    {{ formatFecha(gasto.fecha) }}
                  </td>

                  <!-- Tipo con color -->
                  <td class="px-6 py-4">
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="tipoClase(gasto)">
                      {{ tipoNombre(gasto) }}
                    </span>
                  </td>
                </tr>

                <tr v-if="!gastos.length">
                  <td colspan="4" class="px-6 py-12 text-center text-gray-500 text-lg">
                    No hay gastos registrados aún.
                  </td>
                </tr>
              </tbody>
              <tfoot class="bg-gray-100 font-bold text-lg">
                <tr>
                  <td colspan="3" class="px-6 py-4 text-right">Total Consumos:</td>
                  <td class="px-6 py-4 text-green-600">{{ totalGastos }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
          <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <Link :href="route('gastos.asignaciones', detalle.reserva_id)"
              class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Volver al listado de habitaciones
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>