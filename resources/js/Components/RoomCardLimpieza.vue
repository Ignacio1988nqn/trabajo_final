<template>
  <div class="bg-yellow-100 shadow-md rounded-xl p-4 flex flex-col justify-between border border-yellow-300">
    <div>
      <h2 class="text-lg font-semibold text-gray-800">
        Habitación {{ habitacion.numero }}
      </h2>
      <p class="text-sm text-gray-700 mt-1">Tipo: {{ habitacion.tipo }}</p>
      <p class="text-sm text-gray-700 mt-1">
        Última limpieza:
        <span class="font-medium">
          {{ habitacion.ultima_limpieza ? formatDate(habitacion.ultima_limpieza) : '—' }}
        </span>
      </p>
    </div>

    <button @click="confirmarDisponible"
      class="mt-4 px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-200">
      ✅ Marcar como Disponible
    </button>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  habitacion: Object,
})

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('es-AR')
}

function confirmarDisponible() {
  Swal.fire({
    title: `¿Marcar habitación ${props.habitacion.numero} como disponible?`,
    text: "Esta acción actualizará la fecha de última limpieza.",
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, confirmar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#16a34a', 
    cancelButtonColor: '#d33',
  }).then((result) => {
    if (result.isConfirmed) {
      router.post(`/limpieza/${props.habitacion.id}/disponible`, {}, {
        onSuccess: () => {
          Swal.fire({
            icon: 'success',
            title: '¡Habitación disponible!',
            text: `La habitación ${props.habitacion.numero} fue marcada como disponible.`,
            confirmButtonText: 'Aceptar'
          })
        },
        onError: () => {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo actualizar el estado de la habitación.',
            confirmButtonText: 'Aceptar'
          })
        }
      })
    }
  })
}
</script>
