<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'

const props = defineProps({
  reservas: { type: Array, default: () => [] }
})

function confirmCheckout(detalleId) {
  Swal.fire({
    title: '¿Confirmar check-out?',
    text: 'Se liberará la habitación y se marcará como finalizada la estadía.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, hacer check-out',
    cancelButtonText: 'Cancelar',
  }).then((result) => {
    if (!result.isConfirmed) return

    router.post(route('checkout.do', detalleId), {}, {
      onSuccess: () => {
        Swal.fire('¡Listo!', 'Check-out realizado correctamente.', 'success')
      },
      onError: (errors) => {
        Swal.fire('Error', errors?.error || 'No se pudo realizar el check-out.', 'error')
      },
      preserveScroll: true,
    })
  })
}

function fmt(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<template>

  <Head title="Check-out de Reservas" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Check-out de Huéspedes
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
          <div class="p-6 text-gray-900">

            <div v-if="!props.reservas.length" class="text-center py-12 text-gray-500">
              <p class="text-lg">No hay huéspedes en check-in actualmente.</p>
            </div>

            <table v-else class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Huésped
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Habitación
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Check-in Real
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Check-out Planeado
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="r in props.reservas" :key="r.detalle_id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap font-medium">
                    {{ r.huesped_nombre }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                      {{ r.habitacion_numero }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    {{ fmt(r.fecha_checkin_real) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    {{ fmt(r.checkout_asignado) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button @click="confirmCheckout(r.detalle_id)"
                      class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition">
                      Check-out
                    </button>
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