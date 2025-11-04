<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'

const openMain = ref(false)
const openGuestDesktop = ref(false)
const openGuestMobile = ref(false)
const openUserDesktop = ref(false)

const guestMenuRef = ref(null)
const userMenuRef  = ref(null)
function onClickOutside(e) {
  if (guestMenuRef.value && !guestMenuRef.value.contains(e.target)) openGuestDesktop.value = false
  if (userMenuRef.value  && !userMenuRef.value.contains(e.target))  openUserDesktop.value  = false
}
onMounted(() => document.addEventListener('click', onClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', onClickOutside))
watch(openMain, o => { if (!o) openGuestMobile.value = false })

const routes = {
  dashboard: route('dashboard'),
  huesped: route('huesped.index'),
  reservas: route('reservas.index'),
  checkin: route('checkin.index'),
  checkout: route('checkout.index'),
  gastos: route('gastos.index'),
  habitaciones: route('habitaciones.index'),
  disponibilidad: route('disponibilidad.index'),
  profileEdit: route('profile.edit'),
  logout: route('logout'),
  avatarUpload: route('profile.avatar'), // nueva ruta POST
}

// --- Upload de avatar desde el dropdown
const fileInput = ref(null)
const form = useForm({ avatar: null })
function chooseFile() {
  fileInput.value?.click()
}
function onFileChange(e) {
  const [file] = e.target.files || []
  if (!file) return
  form.avatar = file
  form.post(routes.avatarUpload, {
    forceFormData: true,
    onSuccess: () => { openUserDesktop.value = false },
  })
}
</script>

<template>
  <nav class="sticky top-0 z-40 border-b border-white/20 bg-white/70 backdrop-blur-xl supports-[backdrop-filter]:bg-white/40">
    <!-- Glow decorativo -->
    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-sky-400/60 to-transparent"></div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between">
        <!-- Brand -->
        <div class="flex items-center gap-3">
          <Link :href="routes.dashboard" class="group flex items-center gap-2">
            <ApplicationLogo class="block h-8 w-auto fill-current text-[#0F3D3E] transition-transform group-hover:scale-105" />
            <span class="hidden text-sm font-semibold tracking-wide text-[#0F3D3E] sm:block">We Key</span>
          </Link>
        </div>

        <!-- Desktop nav -->
        <div class="hidden items-center gap-1 md:flex">
          <Link
            :href="routes.dashboard"
            class="relative rounded-full px-3 py-2 text-sm font-medium text-slate-600 hover:text-[#0F3D3E] hover:bg-slate-100/70"
          >
            <span :class="route().current('dashboard') ? 'text-[#0F3D3E]' : ''">Inicio</span>
            <span
              v-if="route().current('dashboard')"
              class="absolute inset-x-2 -bottom-1 h-0.5 rounded-full bg-gradient-to-r from-sky-400 to-indigo-400"
            />
          </Link>

          <!-- Huésped -->
          <div class="relative" ref="guestMenuRef">
            <button
              @click.stop="openGuestDesktop = !openGuestDesktop"
              class="inline-flex items-center gap-1 rounded-full px-3 py-2 text-sm font-medium text-slate-600 hover:text-[#0F3D3E] hover:bg-slate-100/70"
            >
              Huésped
              <svg class="h-4 w-4 transition" :class="openGuestDesktop ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
              </svg>
            </button>

            <div
              v-show="openGuestDesktop"
              class="animate-in fade-in slide-in-from-top-2 absolute left-0 z-50 mt-2 w-60 overflow-hidden rounded-2xl border border-black/5 bg-white/90 p-2 shadow-xl backdrop-blur"
            >
              <Link :href="routes.huesped" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">Huésped</Link>
              <Link :href="routes.reservas" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">Reservas</Link>
              <Link :href="routes.checkin" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">Check-In</Link>
              <Link :href="routes.checkout" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">Check-Out</Link>
              <Link :href="routes.gastos" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">Gastos</Link>
            </div>
          </div>

          <Link
            :href="routes.habitaciones"
            class="relative rounded-full px-3 py-2 text-sm font-medium text-slate-600 hover:text-[#0F3D3E] hover:bg-slate-100/70"
          >
            <span :class="route().current('habitaciones.*') ? 'text-[#0F3D3E]' : ''">Habitaciones</span>
            <span v-if="route().current('habitaciones.*')" class="absolute inset-x-2 -bottom-1 h-0.5 rounded-full bg-gradient-to-r from-sky-400 to-indigo-400" />
          </Link>

          <Link
            :href="routes.disponibilidad"
            class="relative rounded-full px-3 py-2 text-sm font-medium text-slate-600 hover:text-[#0F3D3E] hover:bg-slate-100/70"
          >
            <span :class="route().current('disponibilidad.*') ? 'text-[#0F3D3E]' : ''">Disponibilidad</span>
            <span v-if="route().current('disponibilidad.*')" class="absolute inset-x-2 -bottom-1 h-0.5 rounded-full bg-gradient-to-r from-sky-400 to-indigo-400" />
          </Link>
        </div>

        <!-- Usuario -->
        <div class="relative hidden items-center gap-3 md:flex" ref="userMenuRef">
          <button
            @click.stop="openUserDesktop = !openUserDesktop"
            class="group flex items-center gap-2 rounded-full border border-black/5 bg-white/70 px-2 py-1 shadow-sm hover:shadow"
          >
            <div class="h-9 w-9 overflow-hidden rounded-full ring-1 ring-black/5">
              <img
                v-if="$page.props.auth.user.avatar_url"
                :src="$page.props.auth.user.avatar_url"
                alt="Avatar"
                class="h-full w-full object-cover"
              />
              <div v-else class="flex h-full w-full items-center justify-center bg-[#0F3D3E] text-xs font-semibold text-white">
                {{ ($page.props.auth.user.name || 'U').split(' ').map(s=>s[0]).join('').slice(0,2).toUpperCase() }}
              </div>
            </div>
            <div class="flex items-center gap-1 text-sm font-medium text-[#0F3D3E]">
              <span class="max-w-[160px] truncate">{{ $page.props.auth.user.name }}</span>
              <svg class="h-4 w-4 text-slate-500 transition group-hover:text-[#0F3D3E]" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
              </svg>
            </div>
          </button>

          <div
            v-show="openUserDesktop"
            class="animate-in fade-in slide-in-from-top-2 absolute right-0 top-12 z-50 w-64 overflow-hidden rounded-2xl border border-black/5 bg-white/95 p-2 shadow-xl backdrop-blur"
          >
            <Link :href="routes.profileEdit" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">Mi perfil</Link>

            <!-- Subir foto -->
            <button @click="chooseFile" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100">
              Cambiar foto de perfil
            </button>
            <input ref="fileInput" type="file" class="hidden" accept="image/png,image/jpeg,image/webp" @change="onFileChange" />

            <hr class="my-2 border-slate-200" />
            <Link :href="routes.logout" method="post" as="button" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50">
              Cerrar sesión
            </Link>
          </div>
        </div>

        <!-- Hamburguesa -->
        <button
          @click="openMain = !openMain"
          class="inline-flex items-center justify-center rounded-md p-2 text-[#0F3D3E] md:hidden"
          aria-label="Abrir menú"
        >
          <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path v-if="!openMain" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile -->
    <div v-show="openMain" class="md:hidden border-t border-black/5 bg-white/95 backdrop-blur">
      <div class="space-y-1 py-2">
        <Link :href="routes.dashboard" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Inicio</Link>

        <div>
          <button
            @click="openGuestMobile = !openGuestMobile"
            class="flex w-full items-center justify-between px-4 py-2 text-left text-sm font-medium text-slate-700 hover:bg-slate-100"
          >
            <span>Huésped</span>
            <svg class="h-4 w-4 transition" :class="openGuestMobile ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
            </svg>
          </button>
          <div v-show="openGuestMobile" class="space-y-1 pb-2">
            <Link :href="routes.huesped" class="block px-6 py-2 text-sm text-slate-700 hover:bg-slate-100">Huésped</Link>
            <Link :href="routes.reservas" class="block px-6 py-2 text-sm text-slate-700 hover:bg-slate-100">Reservas</Link>
            <Link :href="routes.checkin" class="block px-6 py-2 text-sm text-slate-700 hover:bg-slate-100">Check-In</Link>
            <Link :href="routes.checkout" class="block px-6 py-2 text-sm text-slate-700 hover:bg-slate-100">Check-Out</Link>
            <Link :href="routes.gastos" class="block px-6 py-2 text-sm text-slate-700 hover:bg-slate-100">Gastos</Link>
          </div>
        </div>

        <Link :href="routes.habitaciones" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Habitaciones</Link>
        <Link :href="routes.disponibilidad" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">Disponibilidad</Link>

        <!-- Usuario -->
        <div class="mt-2 border-t border-black/5 px-4 pt-2">
          <div class="flex items-center gap-2">
            <div class="h-8 w-8 overflow-hidden rounded-full ring-1 ring-black/5">
              <img v-if="$page.props.auth.user.avatar_url" :src="$page.props.auth.user.avatar_url" class="h-full w-full object-cover" />
              <div v-else class="flex h-full w-full items-center justify-center bg-[#0F3D3E] text-[10px] font-semibold text-white">
                {{ ($page.props.auth.user.name || 'U').split(' ').map(s=>s[0]).join('').slice(0,2).toUpperCase() }}
              </div>
            </div>
            <div>
              <div class="text-sm font-medium text-[#0F3D3E]">{{ $page.props.auth.user.name }}</div>
              <div class="text-xs text-slate-500">{{ $page.props.auth.user.email }}</div>
            </div>
          </div>

          <div class="mt-2 space-y-1">
            <Link :href="routes.profileEdit" class="block rounded-md px-2 py-2 text-sm text-slate-700 hover:bg-slate-100">Mi perfil</Link>
            <button @click="chooseFile" class="block w-full rounded-md px-2 py-2 text-left text-sm text-slate-700 hover:bg-slate-100">
              Cambiar foto de perfil
            </button>
            <input ref="fileInput" type="file" class="hidden" accept="image/png,image/jpeg,image/webp" @change="onFileChange" />

            <Link :href="routes.logout" method="post" as="button" class="block w-full rounded-md px-2 py-2 text-left text-sm text-red-600 hover:bg-red-50">
              Cerrar sesión
            </Link>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
/* pequeñas animaciones utilitarias */
.animate-in {
  animation: fadeIn .15s ease-out;
}
@keyframes fadeIn {
  from { opacity: .0; transform: translateY(-4px); }
  to   { opacity: 1;  transform: translateY(0); }
}
</style>
