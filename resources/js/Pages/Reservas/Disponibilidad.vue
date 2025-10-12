<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Verificar Disponibilidad</h2>
    </template>

    <div class="py-8 max-w-5xl mx-auto px-4">
      <!-- Filtro de búsqueda -->
      <form @submit.prevent="buscarDisponibilidad" class="flex flex-wrap items-end gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-700">Fecha inicio</label>
          <input type="date" v-model="fecha_inicio"
            class="mt-1 block w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Fecha fin</label>
          <input type="date" v-model="fecha_fin"
            class="mt-1 block w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Tipo</label>
          <select v-model="tipo"
            class="mt-1 block w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option disabled value="">Seleccione</option>
            <option value="simple">Simple</option>
            <option value="doble">Doble</option>
            <option value="triple">Triple</option>
            <option value="cuadruple">Cuádruple</option>
          </select>
        </div>

        <button type="submit"
          class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 shadow-sm">
          Buscar
        </button>
      </form>

      <!-- Tabla de resultados -->
      <div v-if="habitaciones.length">
        <table class="min-w-full border text-sm text-gray-700">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2 border">N° Habitación</th>
              <th class="px-4 py-2 border">Tipo</th>
              <th class="px-4 py-2 border">Precio/Noche</th>
              <th class="px-4 py-2 border">Estado</th>
              <th class="px-4 py-2 border text-center">Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="h in habitaciones" :key="h.id" class="border-b hover:bg-gray-50">
              <td class="px-4 py-2 text-center">{{ h.numero }}</td>
              <td class="px-4 py-2 text-center capitalize">{{ h.tipo }}</td>
              <td class="px-4 py-2 text-center">${{ h.precio_noche }}</td>
              <td class="px-4 py-2 text-center">{{ h.estado_actual }}</td>
              <td class="px-4 py-2 text-center">
                <button @click="irAReserva(h)"
                  class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700">
                  Reservar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-gray-500 mt-6" v-if="fecha_inicio && fecha_fin && tipo">
        No hay habitaciones disponibles para el rango y tipo seleccionados.
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  habitaciones: Array,
  fecha_inicio: String,
  fecha_fin: String,
  tipo: String,
});

const fecha_inicio = ref(props.fecha_inicio || '');
const fecha_fin = ref(props.fecha_fin || '');
const tipo = ref(props.tipo || '');

const buscarDisponibilidad = () => {
  router.get(route('disponibilidad.index'), {
    fecha_inicio: fecha_inicio.value,
    fecha_fin: fecha_fin.value,
    tipo: tipo.value,
  }, { preserveState: true });
};

const irAReserva = (habitacion) => {
  router.get(route('reservas.create'), {
    habitacion_id: habitacion.id,
    fecha_inicio: fecha_inicio.value,
    fecha_fin: fecha_fin.value,
  });
};
</script> 
