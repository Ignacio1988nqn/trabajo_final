<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
</script>

<template>

  <Head title="Check In" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Check-in de Reservas
      </h2>
    </template>

    <div v-if="$page.props.errors.error" class="bg-red-100 text-red-700 p-4 mb-4 rounded">
      {{ $page.props.errors.error }}
    </div>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <!-- Tabla de reservas pendientes -->
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
                  <td class="py-2 px-4 border">{{ reserva.huesped }}</td>
                  <td class="py-2 px-4 border">{{ reserva.fecha_reserva }}</td>
                  <td class="py-2 px-4 border">{{ reserva.fecha_checkin }}</td>
                  <td class="py-2 px-4 border">{{ reserva.fecha_checkout }}</td>
                  <td class="py-2 px-4 border">{{ reserva.estado }}</td>
                  <td class="py-2 px-4 border">
                    <button @click="confirmCheckin(reserva.id)"
                      class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                      Check-in
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

<script>
export default {
  props: {
    reservas: Array,
  },
  methods: {
    confirmCheckin(reservaId) {
      // Mostrar confirmación con SweetAlert2
      Swal.fire({
        title: '¿Confirmar check-in?',
        text: '¿Estás seguro de que deseas realizar el check-in para esta reserva?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
      }).then((result) => {
        if (result.isConfirmed) {
          this.$inertia.post(`/checkin/${reservaId}`, {}, {
            onSuccess: () => {
              // Mostrar mensaje de éxito
              Swal.fire({
                title: '¡Éxito!',
                text: 'Check-in realizado con éxito.',
                icon: 'success',
                confirmButtonText: 'Aceptar',
              });
            },
            onError: (errors) => {
              // Mostrar mensaje de error
              Swal.fire({
                title: 'Error',
                text: errors.error || 'Ocurrió un error al realizar el check-in.',
                icon: 'error',
                confirmButtonText: 'Aceptar',
              });
            },
          });
        }
      });
    },
  },
};
</script>