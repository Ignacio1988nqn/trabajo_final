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
              <Link :href="route('gasto-items.create')"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
              Nuevo Ítem
              </Link>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="w-8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      <!-- Columna foto -->
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Nombre
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Descripción
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Precio
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Tipo
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Stock
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Acciones
                    </th>
                  </tr>
                </thead>

                <tbody v-if="items.length" class="bg-white divide-y divide-gray-200">
                  <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50 transition-colors duration-200">
                    <!-- Foto del producto -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div
                        class="w-16 h-16 bg-gray-200 border-2 border-dashed rounded-xl overflow-hidden flex items-center justify-center">
                        <img :src="`/storage/gastoitems/gastoitem_${item.id}.jpg`" alt="item.nombre"
                          class="w-full h-full object-cover"
                          @error="e => e.target.src = '/images/placeholder-item.jpg'" />
                      </div>
                    </td>

                    <!-- Nombre (destacado) -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                      {{ item.nombre }}
                    </td>

                    <!-- Descripción -->
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">
                      {{ item.descripcion || 'Sin descripción' }}
                    </td>

                    <!-- Precio (con estilo moneda) -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-700">
                      ${{ Number(item.precio).toLocaleString('es-AR') }}
                    </td>

                    <!-- Tipo con badge bonito -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                        :class="{
                          'bg-purple-100 text-purple-800': item.tipo === 'bebida',
                          'bg-orange-100 text-orange-800': item.tipo === 'comida',
                          'bg-cyan-100 text-cyan-800': item.tipo === 'servicio',
                          'bg-pink-100 text-pink-800': item.tipo === 'extra',
                          'bg-gray-100 text-gray-800': !item.tipo
                        }">
                        {{ item.tipo || 'sin tipo' }}
                      </span>
                    </td>

                    <!-- Stock con indicador visual -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <span v-if="item.stock !== null && item.stock !== undefined" :class="{
                        'text-red-600': item.stock <= 5,
                        'text-yellow-600': item.stock > 5 && item.stock <= 15,
                        'text-green-600': item.stock > 15
                      }">
                        {{ item.stock }} unid.
                      </span>
                      <span v-else class="text-gray-400 italic">Ilimitado</span>
                    </td>

                    <!-- Acciones (botones más bonitos y modernos) -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                      <Link :href="route('gasto-items.edit', item.id)"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                      Editar
                      </Link>

                      <button @click="confirmDelete(item.id)"
                        class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 transition">
                        Eliminar
                      </button>
                    </td>
                  </tr>
                </tbody>

                <!-- Sin items -->
                <tbody v-else>
                  <tr>
                    <td colspan="7" class="px-6 py-16 text-center text-gray-500 bg-gray-50">
                      <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 13v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0V9a2 2 0 00-2-2h-2m4 6H4m0 0V9a2 2 0 012-2h2m10 6V9a2 2 0 00-2-2h-2" />
                      </svg>
                      <p class="text-xl font-semibold text-gray-700">No hay productos registrados</p>
                      <p class="text-sm mt-2">Agregá bebidas, comidas o servicios para empezar a cobrar consumos.</p>
                      <button @click="openCreateModal"
                        class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700">
                        + Agregar primer producto
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
import { Head, Link, router } from '@inertiajs/vue3';  // <--- AÑADE 'router'
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
        confirmButtonColor: '#d33',
      }).then((result) => {
        if (result.isConfirmed) {
          router.delete(route('gasto-items.destroy', id), {
            onSuccess: () => {
              window.Swal.fire({
                title: 'Eliminado',
                text: 'Ítem eliminado correctamente.',
                icon: 'success',
                confirmButtonText: 'Aceptar',
              });
            },
            onError: (errors) => {
              window.Swal.fire({
                title: 'Error',
                text: 'No se pudo eliminar el ítem.',
                icon: 'error',
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