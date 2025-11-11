<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { router } from '@inertiajs/vue3';

const logout = () => {
  router.post(route('logout'));
};

// Estado UI
const openMain = ref(false)               // menú mobile
const openGuestDesktop = ref(false)       // submenu Huésped (desktop)
const openGuestMobile = ref(false)        // acordeón Huésped (mobile)

// Cerrar al click fuera (solo DESKTOP)
const guestMenuRef = ref(null)
function onClickOutside(e) {
  if (guestMenuRef.value && !guestMenuRef.value.contains(e.target)) {
    openGuestDesktop.value = false
  }
}
onMounted(() => document.addEventListener('click', onClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', onClickOutside))

// Si se cierra el menú mobile, cerramos su acordeón también
watch(openMain, (isOpen) => {
  if (!isOpen) openGuestMobile.value = false
})

const page = usePage()
const menu = page.props.menu

// Links (ajusta si cambias rutas)
const routes = {
  dashboard: route('dashboard'),

}
</script>

<template>
  <nav class="border-b border-black/5 bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/70">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between">

        <!-- Logo -->
        <Link :href="route('dashboard')" class="flex items-center gap-2">
        <ApplicationLogo class="block h-8 w-auto fill-current text-[#0F3D3E]" />
        <span class="hidden text-sm font-semibold tracking-wide text-[#0F3D3E] sm:block">We Key</span>
        </Link>

        <!-- Menú dinámico -->
        <div class="hidden items-center gap-1 md:flex">
          <template v-for="item in menu" :key="item.id">
            <!-- Si tiene submenús -->
            <div v-if="item.hijos.length" class="relative group">
              <button
                class="inline-flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium text-[#334155] hover:text-[#0F3D3E] hover:bg-[#0F3D3E]/5">
                {{ item.nombre }}
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.23 7.21a.75.75..." clip-rule="evenodd" />
                </svg>
              </button>

              <div
                class="absolute left-0 z-50 mt-2 w-56 overflow-hidden rounded-xl border border-black/5 bg-white p-2 shadow-lg opacity-0 group-hover:opacity-100 transition">
                <Link v-for="sub in item.hijos" :key="sub.id" :href="route(sub.ruta)"
                  class="block rounded-lg px-3 py-2 text-sm text-[#334155] hover:bg-[#0F3D3E]/5 hover:text-[#0F3D3E]">
                {{ sub.nombre }}
                </Link>
              </div>
            </div>

            <!-- Si es un link directo -->
            <Link v-else :href="route(item.ruta)"
              class="rounded-md px-3 py-2 text-sm font-medium text-[#334155] hover:text-[#0F3D3E] hover:bg-[#0F3D3E]/5"
              :class="route().current(item.ruta + '.*') ? 'text-[#0F3D3E] border-b-2 border-[#3B82F6]' : ''">
            {{ item.nombre }}
            </Link>
          </template>
        </div>

        <!-- Usuario (derecha) -->
        <div class="hidden sm:flex sm:items-center sm:ml-6">
          <div class="ml-3 relative">
            <Dropdown align="right" width="48">
              <template #trigger>
                <button
                  class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                  <div>{{ $page.props.auth.user.name }} ({{ $page.props.auth.user.rol }})</div>

                  <div class="ml-1">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                    </svg>
                  </div>
                </button>
              </template>

              <template #content>
                <!-- Account Management -->
                <div class="block px-4 py-2 text-xs text-gray-400">
                  Administrar cuenta
                </div>

                <DropdownLink :href="route('profile.edit')">
                  Perfil
                </DropdownLink>
                <template v-if="page.props.auth.user.rol === 'admin'">
                  <DropdownLink :href="route('usuarios.index')">
                    Administrar Usuarios
                  </DropdownLink>
                </template>

                <div class="border-t border-gray-200" />

                <!-- Authentication -->
                <form @submit.prevent="logout" @click="$inertia.post(route('logout'))">
                  <DropdownLink href="#" as="button">
                    Logout
                  </DropdownLink>
                </form>

              </template>
            </Dropdown>
          </div>
        </div>

        <!-- Hamburguesa (mobile) -->
        <button @click="openMain = !openMain"
          class="inline-flex items-center justify-center rounded-md p-2 text-[#0F3D3E] md:hidden"
          aria-label="Abrir menú">
          <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path v-if="!openMain" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16" />
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Menú mobile -->
    <div v-show="openMain" class="md:hidden border-t border-black/5 bg-white">
      <div class="space-y-1 py-2">
        <Link :href="routes.dashboard" class="block px-4 py-2 text-sm font-medium text-[#334155] hover:bg-[#0F3D3E]/5">
        Inicio</Link>

        <!-- Acordeón Huésped (MOBILE) -->
        <div>
          <button @click="openGuestMobile = !openGuestMobile"
            class="flex w-full items-center justify-between px-4 py-2 text-left text-sm font-medium text-[#334155] hover:bg-[#0F3D3E]/5">
            <span>Huésped</span>
            <svg class="h-4 w-4 transition" :class="openGuestMobile ? 'rotate-180' : ''" viewBox="0 0 20 20"
              fill="currentColor">
              <path fill-rule="evenodd"
                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                clip-rule="evenodd" />
            </svg>
          </button>
          <div v-show="openGuestMobile" class="space-y-1 pb-2">
            <Link :href="routes.huesped" class="block px-6 py-2 text-sm text-[#334155] hover:bg-[#0F3D3E]/5">Huésped
            </Link>
            <Link :href="routes.reservas" class="block px-6 py-2 text-sm text-[#334155] hover:bg-[#0F3D3E]/5">Reservas
            </Link>
            <Link :href="routes.checkin" class="block px-6 py-2 text-sm text-[#334155] hover:bg-[#0F3D3E]/5">Check-In
            </Link>
            <Link :href="routes.checkout" class="block px-6 py-2 text-sm text-[#334155] hover:bg-[#0F3D3E]/5">
            Check-Out
            </Link>
            <Link :href="routes.gastos" class="block px-6 py-2 text-sm text-[#334155] hover:bg-[#0F3D3E]/5">Gastos
            </Link>
          </div>
        </div>

        <Link :href="routes.habitaciones"
          class="block px-4 py-2 text-sm font-medium text-[#334155] hover:bg-[#0F3D3E]/5">
        Habitaciones</Link>
        <Link :href="routes.disponibilidad"
          class="block px-4 py-2 text-sm font-medium text-[#334155] hover:bg-[#0F3D3E]/5">
        Disponibilidad</Link>

        <!-- Usuario -->
        <div class="mt-2 border-t border-black/5 px-4 pt-2">
          <div class="text-sm font-medium text-[#0F3D3E]">{{ $page.props.auth.user.name }}</div>
          <div class="text-xs text-[#64748B]">{{ $page.props.auth.user.email }}</div>
        </div>
      </div>
    </div>
  </nav>
</template>
