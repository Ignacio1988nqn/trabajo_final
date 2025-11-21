<template>

  <Head :щиtitle="item ? 'Editar Ítem de Gasto' : 'Crear Ítem de Gasto'" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        {{ item ? 'Editar Ítem de Gasto' : 'Crear Ítem de Gasto' }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl">
          <div class="p-8">

            <form @submit.prevent="submit" class="space-y-10">

              <div class="grid grid-cols-1 md:grid-cols-2 gap-10">

                <!-- Columna izquierda -->
                <div class="space-y-7">

                  <!-- Nombre -->
                  <div>
                    <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-2">
                      Nombre del producto
                    </label>
                    <input v-model="form.nombre" id="nombre" type="text" required placeholder="Ej: Coca Cola 500ml"
                      class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                      :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-100': form.errors.nombre }" />
                    <p v-if="form.errors.nombre" class="mt-2 text-sm text-red-600 font-medium">
                      {{ form.errors.nombre }}
                    </p>
                  </div>

                  <!-- Descripción -->
                  <div>
                    <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-2">
                      Descripción (opcional)
                    </label>
                    <textarea v-model="form.descripcion" id="descripcion" rows="4"
                      placeholder="Ej: Bebida gaseosa sin alcohol..."
                      class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition resize-none"
                      :class="{ 'border-red-500': form.errors.descripcion }"></textarea>
                    <p v-if="form.errors.descripcion" class="mt-2 text-sm text-red-600 font-medium">
                      {{ form.errors.descripcion }}
                    </p>
                  </div>

                  <!-- Precio -->
                  <div>
                    <label for="precio" class="block text-sm font-semibold text-gray-700 mb-2">
                      Precio
                    </label>
                    <div class="relative">
                      <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-600 font-bold text-lg">$</span>
                      <input v-model="form.precio" id="precio" type="number" step="0.01" min="0" required
                        placeholder="0.00"
                        class="w-full pl-11 pr-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                        :class="{ 'border-red-500': form.errors.precio }" />
                    </div>
                    <p v-if="form.errors.precio" class="mt-2 text-sm text-red-600 font-medium">
                      {{ form.errors.precio }}
                    </p>
                  </div>
                </div>

                <!-- Col  derecha -->
                <div class="space-y-7">

                  <!-- Tipo -->
                  <div>
                    <label for="tipo" class="block text-sm font-semibold text-gray-700 mb-2">
                      Tipo de producto
                    </label>
                    <select v-model="form.tipo" id="tipo" required
                      class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                      :class="{ 'border-red-500': form.errors.tipo }">
                      <option value="" disabled>Seleccione un tipo</option>
                      <option v-for="tipo in tipos" :key="tipo" :value="tipo">
                        {{ tipo.charAt(0).toUpperCase() + tipo.slice(1) }}
                      </option>
                    </select>
                    <p v-if="form.errors.tipo" class="mt-2 text-sm text-red-600 font-medium">
                      {{ form.errors.tipo }}
                    </p>
                  </div>

                  <!-- Stock -->
                  <div>
                    <label for="stock" class="block text-sm font-semibold text-gray-700 mb-2">
                      Stock (vacío = ilimitado)
                    </label>
                    <input v-model="form.stock" id="stock" type="number" min="0" placeholder="Ej: 50"
                      class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition"
                      :class="{ 'border-red-500': form.errors.stock }" />
                    <p v-if="form.errors.stock" class="mt-2 text-sm text-red-600 font-medium">
                      {{ form.errors.stock }}
                    </p>
                  </div>

                  <!-- Imagen del producto -->
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-4">
                      Imagen del producto
                    </label>

                    <!-- 1. Preview de nueva imagen subida -->
                    <div v-if="previewImage" class="relative mt-4">
                      <img :src="previewImage" alt="Nueva imagen"
                        class="w-full max-w-md mx-auto h-64 object-cover rounded-xl shadow-lg border border-gray-200" />
                      <div
                        class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black bg-opacity-70 text-white text-xs px-4 py-2 rounded-lg">
                        {{ newFileName }}
                      </div>
                    </div>

                    <!-- 2. Imagen existente (solo edición) -->
                    <div v-else-if="existingImageUrl" class="relative mt-4">
                      <img :src="existingImageUrl" alt="Imagen actual"
                        class="w-full max-w-md mx-auto h-64 object-cover rounded-xl shadow-lg border border-gray-200" />
                      <button type="button" @click="$refs.fileInput.click()"
                        class="absolute top-4 right-4 bg-white bg-opacity-90 hover:bg-opacity-100 rounded-full p-3 shadow-lg transition">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                      </button>
                      <p class="text-center mt-3 text-sm font-medium text-gray-600">Imagen actual</p>
                    </div>

                    <!-- 3. Dropzone (solo si no hay imagen) -->
                    <div v-else>
                      <label for="dropzone-file"
                        class="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                        <div class="text-center pt-8 pb-10">
                          <svg class="mx-auto w-14 h-14 text-gray-400 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                          </svg>
                          <p class="text-sm font-medium text-gray-600">Haga clic para subir imagen</p>
                          <p class="text-xs text-gray-500 mt-1">JPG, PNG, WEBP · Hasta 2MB</p>
                        </div>
                        <input ref="fileInput" id="dropzone-file" type="file" accept="image/*" class="hidden"
                          @change="handleImageUpload" />
                      </label>
                    </div>

                    <!-- Input oculto para cambiar imagen -->
                    <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleImageUpload" />

                    <p class="mt-4 text-xs text-gray-500 text-center">
                      Tamaño recomendado: 600×600px
                    </p>
                  </div>

                </div>
              </div>

              <!-- Botones -->
              <div class="flex justify-end gap-4 pt-8 border-t border-gray-200">
                <Link :href="route('gasto-items.index')"
                  class="px-7 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Cancelar
                </Link>

                <button type="submit" :disabled="form.processing"
                  class="px-9 py-3 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-lg transition flex items-center gap-3 disabled:opacity-60">
                  <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                  </svg>
                  <span>{{ form.processing ? 'Guardando...' : 'Guardar producto' }}</span>
                </button>
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

  data() {
    return {
      form: useForm({
        nombre: this.item?.nombre || '',
        descripcion: this.item?.descripcion || '',
        precio: this.item?.precio || '',
        tipo: this.item?.tipo || '',
        stock: this.item?.stock || null,
        imagen: null,
      }),
      previewImage: null,
      newFileName: '',
    };
  },

  computed: {
    existingImageUrl() {
      if (this.item?.id) {
        return `/storage/gastoitems/gastoitem_${this.item.id}.jpg?t=${Date.now()}`;
      }
      return null;
    }
  },

  methods: {
    handleImageUpload(e) {
      const file = e.target.files[0];
      if (file) {
        this.form.imagen = file;
        this.previewImage = URL.createObjectURL(file);
        this.newFileName = file.name;
      }
    },

    submit() {
      const routeName = this.item ? 'gasto-items.update' : 'gasto-items.store';
      const routeParams = this.item ? this.item.id : {};

      this.form.post(route(routeName, routeParams), {
        forceFormData: true,
        onSuccess: () => {
          window.Swal.fire('¡Listo!', 'Producto guardado correctamente.', 'success');
          if (!this.item) {
            this.form.reset();
            this.previewImage = null;
            this.newFileName = '';
          }
        },
      });
    }
  }
};
</script>