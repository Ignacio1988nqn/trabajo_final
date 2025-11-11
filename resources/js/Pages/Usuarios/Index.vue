<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Lista de Usuarios
        </h2>
        <Link
          :href="route('usuarios.create')"
          class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg shadow transition"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Crear usuario
        </Link>
      </div>
    </template>

    <div class="py-10">
      <div class="max-w-5xl mx-auto bg-white shadow rounded-xl p-6">
        <div v-if="users.length === 0" class="text-center text-gray-500 py-6">
          No hay usuarios registrados.
        </div>

        <table v-else class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
              <td class="px-6 py-3 text-sm text-gray-700">{{ user.id }}</td>
              <td class="px-6 py-3 text-sm text-gray-700">{{ user.name }}</td>
              <td class="px-6 py-3 text-sm text-gray-700">{{ user.email }}</td>
              <td class="px-6 py-3">
                <span
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                  :class="{
                    'bg-blue-100 text-blue-800': user.rol === 'admin',
                    'bg-green-100 text-green-800': user.rol === 'recepcion',
                    'bg-yellow-100 text-yellow-800': user.rol === 'limpieza',
                    'bg-orange-100 text-orange-800': user.rol === 'mantenimiento',
                  }"
                >
                  {{ user.rol }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
  users: Array,
})
</script>
