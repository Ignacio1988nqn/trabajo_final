<template>
  <div class="relative p-4 rounded-lg shadow-md border-l-4 mb-4 transition-all duration-300" :class="cardClasses"
    ref="cardContainer">

    <!-- Botón superior derecho para cambiar estado -->
    <div class="absolute top-2 right-2 flex space-x-2">
      <button @click.stop="toggleMenu" class="px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 rounded-md shadow-sm 
               hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 
               focus:ring-offset-1 transition-all duration-200">
        Cambiar Estado
      </button>
      <button v-if="room.estado_actual === 'ocupada'" @click.stop="toggleCambiarHabitacion" class="px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-md shadow-sm 
         hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 
         focus:ring-offset-1 transition-all duration-200">
        Cambiar Habitación
      </button>
    </div>

    <!-- Menú desplegable para cambiar estado -->
    <div v-if="mostrarMenu" class="absolute right-0 mt-2 w-40 bg-white border rounded-md shadow-lg z-10">
      <ul>
        <li v-for="estado in estados" :key="estado" @click="cambiarEstado(estado)"
          class="px-3 py-2 text-sm hover:bg-gray-100 cursor-pointer">
          {{ capitalizar(estado) }}
        </li>
      </ul>
    </div>

    <!-- Formulario para cambiar habitación -->
    <div v-if="mostrarFormularioCambio"
      class="absolute right-0 mt-2 w-64 bg-white border rounded-md shadow-lg z-10 p-4">
      <h4 class="text-sm font-medium mb-2">Cambiar Habitación</h4>
      <div class="mb-2">
        <label for="nuevaHabitacion" class="block text-sm font-medium text-gray-700">
          Seleccionar nueva habitación:
        </label>
        <select id="nuevaHabitacion" v-model="nuevaHabitacionId"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
          <option value="" disabled>Seleccione una habitación</option>
          <option v-for="habitacion in habitacionesDisponibles" :key="habitacion.id" :value="habitacion.id">
            {{ habitacion.numero }} ({{ habitacion.tipo }})
          </option>
        </select>
      </div>
      <div class="mb-2">
        <label for="motivoCambio" class="block text-sm font-medium text-gray-700">
          Motivo del cambio:
        </label>
        <input id="motivoCambio" v-model="motivoCambio" type="text"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          placeholder="Ej. Solicitud del cliente" />
      </div>
      <div class="flex justify-end space-x-2">
        <button @click="toggleCambiarHabitacion"
          class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
          Cancelar
        </button>
        <button @click="cambiarHabitacion" :disabled="!nuevaHabitacionId || !motivoCambio"
          class="px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700"
          :class="{ 'opacity-50 cursor-not-allowed': !nuevaHabitacionId || !motivoCambio }">
          Confirmar
        </button>
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
import axios from 'axios';

export default {
  name: 'RoomCard',
  props: {
    room: {
      type: Object,
      required: true,
    },
    asignacionVigente: {
      type: Object,
      default: null,
    },
    habitacionesDisponibles: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      mostrarMenu: false,
      mostrarFormularioCambio: false,
      estados: ['disponible', 'mantenimiento', 'limpieza'],
      nuevaHabitacionId: '',
      motivoCambio: '',
    };
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside);
    console.log('Habitaciones disponibles en RoomCard:', this.habitacionesDisponibles);
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
          return 'bg-blue-100 border-blue-500';
        case 'limpieza':
          return 'bg-yellow-100 border-yellow-500';
        default:
          return 'bg-gray-100 border-gray-400';
      }
    },
  },
  methods: {
    toggleMenu() {
      this.mostrarMenu = !this.mostrarMenu;
      this.mostrarFormularioCambio = false; // Cerrar el formulario de cambio si está abierto
    },
    toggleCambiarHabitacion() {
      this.mostrarFormularioCambio = !this.mostrarFormularioCambio;
      this.mostrarMenu = false; // Cerrar el menú de estados si está abierto
    },
    handleClickOutside(event) {
      if (this.mostrarMenu && !this.$refs.cardContainer.contains(event.target)) {
        this.mostrarMenu = false;
      }
      if (this.mostrarFormularioCambio && !this.$refs.cardContainer.contains(event.target)) {
        this.mostrarFormularioCambio = false;
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
    cambiarHabitacion() {
      if (!this.nuevaHabitacionId || !this.motivoCambio) return;
      router.post(
        route('habitaciones.cambiarHabitacion', this.room.id),
        {
          nueva_habitacion_id: this.nuevaHabitacionId,
          motivo_cambio: this.motivoCambio,
          asignacion_id: this.asignacionVigente?.id,
        },
        {
          preserveScroll: true,
          preserveState: true,
          onSuccess: () => {
            this.mostrarFormularioCambio = false;
            this.nuevaHabitacionId = '';
            this.motivoCambio = '';
          },
        }
      );
    },
    capitalizar(texto) {
      return texto.charAt(0).toUpperCase() + texto.slice(1);
    },
  },
};
</script>