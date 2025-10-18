<template>
  <Head title="Gestión de Ítems de Gasto" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Gestión de Ítems de Gasto
      </h2>
    </template>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="flex justify-end mb-4">
              <Link :href="route('gasto-items.create')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Nuevo Ítem
              </Link>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="item in items" :key="item.id">
                    <td class="px-6 py-4 whitespace-nowrap">{{ item.nombre }}</td>
                    <td class="px-6 py-4">{{ item.descripcion || 'Sin descripción' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${{ item.precio }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ item.tipo }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ item.stock ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <Link :href="route('gasto-items.edit', item.id)" class="text-blue-600 hover:text-blue-800 mr-2">
                        Editar
                      </Link>
                      <button @click="confirmDelete(item.id)" class="text-red-600 hover:text-red-800">
                        Eliminar
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

export default {
  components: { Head, Link, AuthenticatedLayout },
  props: {
    items: Array,
  },
  setup() {
    function confirmDelete(id) {
      window.Swal.fire({
        title: '¿Estás seguro?',
        text: 'No podrás deshacer esta acción.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
      }).then((result) => {
        if (result.isConfirmed) {
          window.inertia.delete(route('gasto-items.destroy', id), {
            onSuccess: () => {
              window.Swal.fire({
                title: 'Eliminado',
                text: 'Ítem eliminado correctamente.',
                icon: 'success',
                confirmButtonText: 'Aceptar',
              });
            },
          });
        }
      });
    }

    return { confirmDelete };
  },
};
</script>