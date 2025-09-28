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
                <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                <input v-model="form.descripcion" type="text" id="descripcion" required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                  :class="{ 'border-red-500': form.errors.descripcion }" />
                <span v-if="form.errors.descripcion" class="mt-1 text-sm text-red-500">
                  {{ form.errors.descripcion }}
                </span>
              </div>

              <div>
                <label for="monto" class="block text-sm font-medium text-gray-700">Monto</label>
                <input v-model="form.monto" type="number" step="0.01" id="monto" required min="0"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                  :class="{ 'border-red-500': form.errors.monto }" />
                <span v-if="form.errors.monto" class="mt-1 text-sm text-red-500">
                  {{ form.errors.monto }}
                </span>
              </div>

              <div>
                <label for="fecha" class="block text-sm font-medium text-gray-700">Fecha</label>
                <input v-model="form.fecha" type="date" id="fecha" required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                  :class="{ 'border-red-500': form.errors.fecha }" />
                <span v-if="form.errors.fecha" class="mt-1 text-sm text-red-500">
                  {{ form.errors.fecha }}
                </span>
              </div>

              <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo de Gasto</label>
                <select v-model="form.tipo" id="tipo" required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                  :class="{ 'border-red-500': form.errors.tipo }">
                  <option v-for="tipo in tiposGasto" :key="tipo" :value="tipo">{{ tipo }}</option>
                </select>
                <span v-if="form.errors.tipo" class="mt-1 text-sm text-red-500">
                  {{ form.errors.tipo }}
                </span>
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
import { Head, useForm, Link} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

export default {
  components: {
    Head, Link,
    AuthenticatedLayout,
  },
  props: {
    reserva: Object,
    tiposGasto: Array,
  },
  setup(props) {
    // Obtener la fecha actual en formato YYYY-MM-DD
    const today = new Date().toISOString().split('T')[0];

    const form = useForm({
      reserva_id: props.reserva.id,
      descripcion: '',
      monto: '',
      fecha: today,
      tipo: '',
    });

    function submit() {
      form.post(route('gastos.store'), {
        onSuccess: () => {
          // Mostrar mensaje de éxito con SweetAlert2
          window.Swal.fire({
            title: '¡Éxito!',
            text: 'Gasto registrado con éxito.',
            icon: 'success',
            confirmButtonText: 'Aceptar',
          });
          // Restablecer los campos del formulario
          form.reset('descripcion', 'monto', 'fecha', 'tipo');
          // Establecer la fecha actual nuevamente después de resetear
          form.fecha = today;
        },
        onError: (errors) => {
          // Mostrar mensaje de error con SweetAlert2
          window.Swal.fire({
            title: 'Error',
            text: errors.error || 'Ocurrió un error al registrar el gasto.',
            icon: 'error',
            confirmButtonText: 'Aceptar',
          });
        },
      });
    }

    return { form, submit };
  },
};
</script>
