<template>
  <Head :title="item ? 'Editar Ítem de Gasto' : 'Crear Ítem de Gasto'" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        {{ item ? 'Editar Ítem de Gasto' : 'Crear Ítem de Gasto' }}
      </h2>
    </template>
    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <form @submit.prevent="submit" class="space-y-5">
              <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                <input v-model="form.nombre" id="nombre" type="text" required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                  :class="{ 'border-red-500': form.errors.nombre }" />
                <span v-if="form.errors.nombre" class="mt-1 text-sm text-red-500">
                  {{ form.errors.nombre }}
                </span>
              </div>

              <div>
                <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea v-model="form.descripcion" id="descripcion"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                  :class="{ 'border-red-500': form.errors.descripcion }" />
                <span v-if="form.errors.descripcion" class="mt-1 text-sm text-red-500">
                  {{ form.errors.descripcion }}
                </span>
              </div>

              <div>
                <label for="precio" class="block text-sm font-medium text-gray-700">Precio</label>
                <input v-model="form.precio" id="precio" type="number" step="0.01" min="0" required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"


                  :class="{ 'border-red-500': form.errors.precio }" />
                <span v-if="form.errors.precio" class="mt-1 text-sm text-red-500">
                  {{ form.errors.precio }}
                </span>
              </div>

              <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo</label>
                <select v-model="form.tipo" id="tipo" required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                  :class="{ 'border-red-500': form.errors.tipo }">
                  <option value="">Seleccione un tipo</option>
                  <option v-for="tipo in tipos" :key="tipo" :value="tipo">{{ tipo }}</option>
                </select>
                <span v-if="form.errors.tipo" class="mt-1 text-sm text-red-500">
                  {{ form.errors.tipo }}
                </span>
              </div>

              <div>
                <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                <input v-model="form.stock" id="stock" type="number" min="0"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                  :class="{ 'border-red-500': form.errors.stock }" />
                <span v-if="form.errors.stock" class="mt-1 text-sm text-red-500">
                  {{ form.errors.stock }}
                </span>
              </div>

              <div class="flex gap-3">
                <button type="submit"
                  class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50"
                  :disabled="form.processing">
                  Guardar
                </button>
                <Link :href="route('gasto-items.index')" class="px-4 py-2 rounded border hover:bg-gray-50">
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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

export default {
  components: { Head, Link, AuthenticatedLayout },
  props: {
    item: Object,
    tipos: Array,
  },
  setup(props) {
    const form = useForm({
      nombre: props.item?.nombre || '',
      descripcion: props.item?.descripcion || '',
      precio: props.item?.precio || '',
      tipo: props.item?.tipo || '',
      stock: props.item?.stock || '',
    });

    function submit() {
      if (props.item) {
        form.put(route('gasto-items.update', props.item.id), {
          onSuccess: () => {
            window.Swal.fire({
              title: '¡Éxito!',
              text: 'Ítem actualizado con éxito.',
              icon: 'success',
              confirmButtonText: 'Aceptar',
            });
          },
          onError: (errors) => {
            window.Swal.fire({
              title: 'Error',
              text: errors.error || 'Ocurrió un error al actualizar el ítem.',
              icon: 'error',
              confirmButtonText: 'Aceptar',
            });
          },
        });
      } else {
        form.post(route('gasto-items.store'), {
          onSuccess: () => {
            window.Swal.fire({
              title: '¡Éxito!',
              text: 'Ítem creado con éxito.',
              icon: 'success',
              confirmButtonText: 'Aceptar',
            });
            form.reset();
          },
          onError: (errors) => {
            window.Swal.fire({
              title: 'Error',
              text: errors.error || 'Ocurrió un error al crear el ítem.',
              icon: 'error',
              confirmButtonText: 'Aceptar',
            });
          },
        });
      }
    }

    return { form, submit };
  },
};
</script>