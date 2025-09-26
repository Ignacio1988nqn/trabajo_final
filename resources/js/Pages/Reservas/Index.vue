<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
</script>

<template>
  <Head title="Reservas" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reservas</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <Link
              :href="route('reservas.create')"
              class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
            >
              Alta Reserva
            </Link>

            <div class="mt-4 overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">Huésped</th>
                    <th class="px-4 py-2 text-left">Check-in</th>
                    <th class="px-4 py-2 text-left">Check-out</th>
                    <th class="px-4 py-2 text-left">Estado</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr>
                    <td class="px-4 py-2 text-gray-500" colspan="5">Sin reservas aún.</td>
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
