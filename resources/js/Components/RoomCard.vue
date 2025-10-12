<template>
  <div class="relative p-4 rounded-lg shadow-md border-l-4 mb-4 transition-all duration-300"
       :class="cardClasses"
       ref="cardContainer">

    <!-- Botón superior derecho -->
    <div class="absolute top-2 right-2">
      <button
        @click.stop="toggleMenu"
        class="px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 rounded-md shadow-sm 
               hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 
               focus:ring-offset-1 transition-all duration-200"
      >
        Cambiar Estado
      </button>

      <!-- Menú desplegable -->
      <div
        v-if="mostrarMenu"
        class="absolute right-0 mt-2 w-40 bg-white border rounded-md shadow-lg z-10"
      >
        <ul>
          <li
            v-for="estado in estados"
            :key="estado"
            @click="cambiarEstado(estado)"
            class="px-3 py-2 text-sm hover:bg-gray-100 cursor-pointer"
          >
            {{ capitalizar(estado) }}
          </li>
        </ul>
      </div>
    </div>

    <!-- Contenido de la tarjeta -->
    <h3 class="text-lg font-bold">Habitación {{ room.numero }}</h3>
    <p><strong>Tipo:</strong> {{ room.tipo }}</p>
    <p><strong>Precio por noche:</strong> ${{ room.precio_noche }}</p>
    <p><strong>Estado:</strong> {{ room.estado_actual }}</p>
    <p>
      <strong>Última limpieza:</strong>
      {{ room.ultima_limpieza ? new Date(room.ultima_limpieza).toLocaleDateString() : 'No registrada' }}
    </p>
  </div>
</template>

<script>
import { router } from '@inertiajs/vue3';

export default {
  name: 'RoomCard',
  props: {
    room: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      mostrarMenu: false,
      estados: ['disponible', 'ocupada', 'mantenimiento', 'limpieza'],
    };
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },
  computed: {
    cardClasses() {
      switch (this.room.estado_actual) {
        case 'disponible':
          return 'bg-green-100 border-green-500';
        case 'ocupada':
          return 'bg-red-100 border-red-500';
        case 'mantenimiento':
          return 'bg-yellow-100 border-yellow-500';
        case 'limpieza':
          return 'bg-blue-100 border-blue-500';
        default:
          return 'bg-gray-100 border-gray-400';
      }
    },
  },
  methods: {
    toggleMenu() {
      this.mostrarMenu = !this.mostrarMenu;
    },
    handleClickOutside(event) {
      if (this.mostrarMenu && !this.$refs.cardContainer.contains(event.target)) {
        this.mostrarMenu = false;
      }
    },
    cambiarEstado(nuevoEstado) {
      this.mostrarMenu = false;
      router.put(
        route('habitaciones.actualizarEstado', this.room.id),
        { estado_actual: nuevoEstado },
        { preserveScroll: true, preserveState: true }
      );
    },
    capitalizar(texto) {
      return texto.charAt(0).toUpperCase() + texto.slice(1);
    },
  },
};
</script>
