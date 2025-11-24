<template>

  <Head title="Cargar Gasto" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Cargar Gasto —
        Hab. {{ habitacionActual.numero }} (Detalle #{{ detalleReserva.id }})
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
          <div class="p-8">

            <!-- Info del huésped -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
              <h3 class="text-lg font-bold mb-4">Datos de la Reserva</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <p class="text-sm text-gray-600">Cliente</p>
                  <p class="font-bold text-lg">
                    {{ nombreCliente }}
                  </p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Habitación</p>
                  <p class="font-bold text-indigo-600 text-xl">
                    {{ habitacionActual?.numero ? `Hab. ${habitacionActual.numero}` : 'Sin asignar' }}
                  </p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Check-in</p>
                  <p class="font-medium">
                    {{ formatFecha(detalleReserva.fecha_checkin) }}
                  </p>
                </div>

              </div>
            </div>

            <!-- Formulario -->
            <form @submit.prevent="submit" class="space-y-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de gasto</label>
                <select v-model="selectedTipo" required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                  <option value="">Seleccione un tipo</option>
                  <option v-for="tipo in tipos" :key="tipo" :value="tipo">
                    {{ tipo }}
                  </option>
                </select>
              </div>

              <!-- Ítem -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ítem</label>
                <select v-model="form.gasto_item_id" :disabled="!selectedTipo" required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition disabled:bg-gray-100">
                  <option value="">Seleccione un ítem</option>
                  <option v-for="item in filteredItems" :key="item.id" :value="item.id">
                    {{ item.nombre }} — ${{ item.precio }}
                    <span v-if="item.stock !== null" class="text-gray-500 text-xs">
                      (Stock: {{ item.stock }})
                    </span>
                  </option>
                </select>
              </div>

              <div v-if="selectedItem" class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                  Vista previa del ítem
                </label>
                <div class="flex justify-center">
                  <img :src="imagenUrl" :alt="selectedItem.nombre"
                    class="w-64 h-64 object-cover rounded-xl shadow-2xl border-4 border-white"
                    @error="imagenError = true" @load="imagenError = false" v-show="!imagenError" />
                  <div v-if="imagenError || !imagenUrl"
                    class="w-64 h-64 bg-gray-100 border-4 border-dashed border-gray-300 rounded-xl flex items-center justify-center flex-col text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-gray-500 font-medium">{{ selectedItem.nombre }}</p>
                    <p class="text-xs text-gray-400 mt-1">Sin imagen</p>
                  </div>
                </div>
              </div>

              <!-- Cantidad -->
              <div v-if="selectedItem">
                <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad</label>
                <input type="number" v-model.number="form.cantidad" min="1" required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                <p v-if="selectedItem.stock !== null && form.cantidad > selectedItem.stock"
                  class="text-red-600 font-medium text-sm mt-2">
                  Stock insuficiente (disponible: {{ selectedItem.stock }})
                </p>
              </div>

              <!-- Total -->
              <div v-if="selectedItem" class="bg-green-50 p-6 rounded-lg border-2 border-green-200">
                <p class="text-2xl font-bold text-green-700 text-center">
                  Total: ${{ (selectedItem.precio * form.cantidad).toFixed(2) }}
                </p>
              </div>

              <!-- Fecha -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha del consumo</label>
                <input v-model="form.fecha" type="date" :max="today" required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
              </div>

              <!-- Botones -->
              <div class="flex gap-4 pt-8 justify-center">

                <button type="submit" :disabled="form.processing || stockInsuficiente"
                  class="px-10 py-4 bg-emerald-600 text-white font-bold text-lg rounded-xl hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-xl transition transform hover:scale-105">
                  Guardar Gasto
                </button>
                <Link :href="route('gastos.show', detalleReserva.id)"
                  class="px-10 py-4 bg-gray-600 text-white font-bold text-lg rounded-xl hover:bg-gray-700 shadow-xl transition transform hover:scale-105">
                Cancelar
                </Link>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  detalleReserva: Object,
  habitacionActual: Object,  // ← viene del backend
  items: Array,
  tipos: Array,
})


const today = new Date().toISOString().split('T')[0]

const form = useForm({
  reserva_detalle_id: props.detalleReserva.id,
  gasto_item_id: '',
  cantidad: 1,
  fecha: today,
})

const imagenError = ref(false)

const imagenUrl = computed(() => {
  if (!form.gasto_item_id) return null

  const id = form.gasto_item_id
  const timestamp = new Date().getTime()
  return `/storage/gastoitems/gastoitem_${id}.jpg?t=${timestamp}`

})

const selectedTipo = ref('')

const filteredItems = computed(() => {
  if (!selectedTipo.value) return []
  return props.items.filter(item => item.tipo === selectedTipo.value)
})

const selectedItem = computed(() => {
  if (!form.gasto_item_id) return null
  return props.items.find(i => i.id === Number(form.gasto_item_id)) || null
})


const stockInsuficiente = computed(() => {
  if (!selectedItem.value) return false
  const stock = selectedItem.value.stock
  return stock !== null && form.cantidad > stock
})

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

const habitacionNumero = computed(() => {
  return props.detalleReserva.asignacionesHabitacion?.[0]?.habitacion?.numero || 'Sin asignar'
})

watch(selectedTipo, () => {
  form.gasto_item_id = ''
  form.cantidad = 1
})

function submit() {
  form.post(route('gastos.store'), {
    onSuccess: () => {
      window.Swal.fire({
        title: 'Éxito',
        text: 'Gasto registrado con éxito.',
        icon: 'success',
        confirmButtonText: 'Aceptar',
      });

      form.reset('gasto_item_id', 'cantidad');
      selectedTipo.value = '';
      form.fecha = today;
    },
    onError: (errors) => {
      window.Swal.fire({
        title: 'Error',
        text: errors.error || 'Ocurrió un error al registrar el gasto.',
        icon: 'error',
        confirmButtonText: 'Aceptar',
      });
    },
  });
}
</script>
