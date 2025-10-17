<template>

  <Head title="Cargar Gasto para Reserva" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Cargar Gasto para Reserva #{{ reserva.id || 'N/A' }}
      </h2>
    </template>
    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <form @submit.prevent="submit" class="space-y-5">
              <div>
                <label for="tipo_gasto" class="block text-sm font-medium text-gray-700">
                  Tipo de Gasto
                </label>
                <select v-model="selectedTipo" id="tipo_gasto" required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                  :class="{ 'border-red-500': form.errors.tipo_gasto }">
                  <option value="">Seleccione un tipo</option>
                  <option v-for="tipo in tipos" :key="tipo" :value="tipo">
                    {{ tipo }}
                  </option>
                </select>
                <span v-if="form.errors.tipo_gasto" class="mt-1 text-sm text-red-500">
                  {{ form.errors.tipo_gasto }}
                </span>
              </div>

              <div>
                <label for="gasto_item_id" class="block text-sm font-medium text-gray-700">
                  Ítem de Gasto
                </label>
                <select v-model="form.gasto_item_id" id="gasto_item_id" required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                  :class="{ 'border-red-500': form.errors.gasto_item_id }" :disabled="!selectedTipo">
                  <option value="">Seleccione un ítem</option>
                  <option v-for="item in filteredItems" :key="item.id" :value="item.id">
                    {{ item.nombre }} — ${{ item.precio }}
                  </option>
                </select>
                <span v-if="form.errors.gasto_item_id" class="mt-1 text-sm text-red-500">
                  {{ form.errors.gasto_item_id }}
                </span>
              </div>

              <div>
                <label for="fecha" class="block text-sm font-medium text-gray-700">Fecha</label>
                <input v-model="form.fecha" type="date" id="fecha" required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" />
              </div>

              <div class="flex gap-3">
                <button type="submit"
                  class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50"
                  :disabled="form.processing">
                  Guardar
                </button>
                <Link :href="route('gastos.show', reserva.id)" class="px-4 py-2 rounded border hover:bg-gray-50">
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

<script>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

export default {
  components: { Head, Link, AuthenticatedLayout },
  props: {
    reserva: Object,
    items: Array,
    tipos: Array,
  },
  setup(props) {
    const today = new Date().toISOString().split('T')[0];

    const form = useForm({
      reserva_id: props.reserva.id,
      gasto_item_id: '',
      fecha: today,
    });

    const selectedTipo = ref('');

    const filteredItems = computed(() => {
      if (!selectedTipo.value) return [];
      return props.items.filter(item => item.tipo === selectedTipo.value);
    });

    function submit() {
      form.post(route('gastos.store'), {
        onSuccess: () => {
          window.Swal.fire({
            title: '¡Éxito!',
            text: 'Gasto registrado con éxito.',
            icon: 'success',
            confirmButtonText: 'Aceptar',
          });
          form.reset('gasto_item_id');
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

    return { form, submit, selectedTipo, filteredItems };
  },
};
</script>