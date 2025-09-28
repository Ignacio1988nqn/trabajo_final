<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
  reservas: Array,
});

const form = useForm({});
function confirmCheckout(reservaId) {
  // Mostrar confirmación con SweetAlert2
  window.Swal.fire({
    title: '¿Confirmar check-out?',
    text: '¿Estás seguro de que deseas realizar el check-out para esta reserva?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, confirmar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
  }).then((result) => {
    if (result.isConfirmed) {
      form.post(`/checkout/${reservaId}`, {
        onSuccess: () => {
          // Mostrar mensaje de éxito
          window.Swal.fire({
            title: '¡Éxito!',
            text: 'Check-out realizado con éxito.',
            icon: 'success',
            confirmButtonText: 'Aceptar',
          });
        },
        onError: (errors) => {
          // Mostrar mensaje de error
          window.Swal.fire({
            title: 'Error',
            text: errors.error || 'Ocurrió un error al realizar el check-out.',
            icon: 'error',
            confirmButtonText: 'Aceptar',
          });
        },
      });
    }
  });
}

</script>

<template>

  <Head title="Check-out de Reservas" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Check-in de Reservas
      </h2>
    </template>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <!-- Tabla de reservas en check-in -->
            <table class="min-w-full bg-white border">
              <thead>
                <tr>
                  <th class="py-2 px-4 border">Huésped</th>
                  <th class="py-2 px-4 border">Fecha de Reserva</th>
                  <th class="py-2 px-4 border">Fecha Check-in</th>
                  <th class="py-2 px-4 border">Fecha Check-out</th>
                  <th class="py-2 px-4 border">Estado</th>
                  <th class="py-2 px-4 border">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="reserva in reservas" :key="reserva.id">
                  <td class="py-2 px-4 border">{{ reserva.huesped || 'Sin nombre' }}</td>
                  <td class="py-2 px-4 border">{{ reserva.fecha_reserva || 'No asignada' }}</td>
                  <td class="py-2 px-4 border">{{ reserva.fecha_checkin || 'No asignada' }}</td>
                  <td class="py-2 px-4 border">{{ reserva.fecha_checkout || 'No asignada' }}</td>
                  <td class="py-2 px-4 border">{{ reserva.estado }}</td>
                  <td class="py-2 px-4 border">
                    <button @click="confirmCheckout(reserva.id)"
                      class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                      Check-out
                    </button>
                  </td>
                </tr>
                <tr v-if="!reservas || reservas.length === 0">
                  <td colspan="6" class="py-2 px-4 border text-center">No hay reservas en check-in</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!-- </div> -->
  </AuthenticatedLayout>
</template>