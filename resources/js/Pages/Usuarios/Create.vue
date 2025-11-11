<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Crear Usuario
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-md mx-auto bg-white p-6 rounded-xl shadow">
        <form @submit.prevent="submit">
          <!-- Nombre -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Nombre</label>
            <input v-model="form.name" type="text" class="input" :class="{ 'border-red-500': form.errors.name }" />
            <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
              {{ form.errors.name }}
            </p>
          </div>

          <!-- Email -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input v-model="form.email" type="email" class="input" :class="{ 'border-red-500': form.errors.email }" />
            <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Rol -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Rol</label>
            <select v-model="form.rol" class="input" :class="{ 'border-red-500': form.errors.rol }">
              <option value="">Seleccionar rol</option>
              <option value="admin">Administrador</option>
              <option value="recepcion">Recepción</option>
              <option value="limpieza">Limpieza</option>
              <option value="mantenimiento">Mantenimiento</option>
            </select>
            <p v-if="form.errors.rol" class="text-red-500 text-sm mt-1">
              {{ form.errors.rol }}
            </p>
          </div>

          <!-- Contraseña -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Contraseña</label>
            <input v-model="form.password" type="password" class="input"
              :class="{ 'border-red-500': form.errors.password }" />
            <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Confirmar contraseña -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700">Confirmar contraseña</label>
            <input v-model="form.password_confirmation" type="password" class="input" />
            <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">
              {{ form.errors.password_confirmation }}
            </p>
          </div>

          <div class="flex justify-end">
            <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700"
              :disabled="form.processing">
              Crear usuario
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
  rol: '',
  password: '',
  password_confirmation: '',
})

// Envío del formulario
const submit = () => {
  form.post(route('usuarios.store'), {
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: '¡Usuario creado!',
        text: 'El nuevo usuario se ha creado correctamente.',
        confirmButtonColor: '#10B981',
        confirmButtonText: 'Aceptar',
      })
      form.reset()
    },
  })
}
</script>

<style scoped>
.input {
  @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500;
}
</style>
