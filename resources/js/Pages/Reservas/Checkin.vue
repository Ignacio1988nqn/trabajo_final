<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

const props = defineProps({
  reservas: { type: Array, default: () => [] },  // 1 fila por asignación
  ref_mode: { type: String, default: 'plan' }    // 'plan' | 'hoy'
})

function confirmCheckin(reservaId) {
  // SweetAlert2 global (si lo usás global)
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

    // usa ruta nombrada; ver más abajo la definición
    router.post(route('checkin.do', reservaId), {}, {
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
      <Link :href="route('checkin.index', { ref: 'plan' })"
            class="px-3 py-1 border rounded"
            :class="{ 'bg-blue-600 text-white': props.ref_mode === 'plan' }">
        Planificadas
      </Link>
      <Link :href="route('checkin.index', { ref: 'hoy' })"
            class="px-3 py-1 border rounded"
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
            <table class="min-w-full table-auto border">
              <thead class="bg-gray-50">
                <tr>
                  <th class="border px-4 py-2 text-left">Huésped</th>
                  <th class="border px-4 py-2 text-left">Habitación</th>
                  <th class="border px-4 py-2 text-left">Fecha de Reserva</th>
                  <th class="border px-4 py-2 text-left">Check-in (asig.)</th>
                  <th class="border px-4 py-2 text-left">Check-out (asig.)</th>
                  <th class="border px-4 py-2 text-left">Estado</th>
                  <th class="border px-4 py-2 text-left">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in props.reservas" :key="r.asignacion_id" class="hover:bg-gray-50">
                  <td class="border px-4 py-2">{{ r.huesped_nombre }}</td>
                  <td class="border px-4 py-2">{{ r.habitacion_numero }}</td>
                  <td class="border px-4 py-2">{{ fmt(r.fecha_reserva) }}</td>
                  <td class="border px-4 py-2">{{ fmt(r.checkin_det) }}</td>
                  <td class="border px-4 py-2">{{ fmt(r.checkout_det) }}</td>
                  <td class="border px-4 py-2 capitalize">{{ r.estado }}</td>
                  <td class="border px-4 py-2">
                    <button
                      class="rounded bg-blue-600 px-3 py-1 text-white hover:bg-blue-700"
                      @click="confirmCheckin(r.reserva_id)"
                    >
                      Check-in
                    </button>
                  </td>
                </tr>
                <tr v-if="!props.reservas.length">
                  <td class="px-4 py-6 text-center text-gray-500" colspan="7">
                    No hay reservas para mostrar.
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
