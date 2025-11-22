<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import Logo from "@/assets/logo1.png";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

// Estados UI
const openMain = ref(false);
const openGuestDesktop = ref(false);
const openGuestMobile = ref(false);

// Click fuera
const guestMenuRef = ref(null);
const onClickOutside = (e) => {
    if (guestMenuRef.value && !guestMenuRef.value.contains(e.target)) {
        openGuestDesktop.value = false;
    }
};
onMounted(() => document.addEventListener("click", onClickOutside));
onBeforeUnmount(() => document.removeEventListener("click", onClickOutside));

// Cierre de acordeón mobile
watch(openMain, (isOpen) => {
    if (!isOpen) openGuestMobile.value = false;
});

// Menu dinámico
const page = usePage();
const menu = page.props.menu;

const routes = {
    dashboard: route("dashboard"),
    huesped: route("huesped.index"),
    reservas: route("reservas.index"),
    checkin: route("checkin.index"),
    checkout: route("checkout.index"),
    gastos: route("gastos.index"),
    habitaciones: route("habitaciones.index"),
    disponibilidad: route("disponibilidad.index"),
};
</script>

<template>
    <nav
        class="border-b border-black/5 bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/70 shadow-sm"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between">
                <!-- LOGO -->
                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-2"
                >
                    <img :src="Logo" alt="We Key" class="h-12 w-auto md:h-14" />
                    <span
                        class="hidden text-sm font-semibold tracking-wide text-[#0F3D3E] sm:block"
                    >
                        Hotel Iberia
                    </span>
                </Link>
                <!-- <button
                                class="inline-flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium text-[#334155] hover:text-[#0F3D3E] hover:bg-[#0F3D3E]/5"
                            > -->
                <!-- MENÚ DESKTOP -->
                <div class="hidden items-center gap-1 md:flex">
                    <template v-for="item in menu" :key="item.id">
                        <!-- SI TIENE SUBMENÚS -->
                        <div v-if="item.hijos.length" class="relative group">
                            <!-- El padre ahora es un Link -->
                            <Link
                                :href="item.ruta ? route(item.ruta) : '#'"
                                class="inline-flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium text-[#334155] hover:text-[#0F3D3E] hover:bg-[#0F3D3E]/5"
                            >
                                {{ item.nombre }}
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </Link>

                            <!-- Dropdown al hacer hover -->
                            <div
                                class="absolute left-0 z-50 mt-2 w-56 overflow-hidden rounded-xl border border-black/5 bg-white p-2 shadow-lg opacity-0 group-hover:opacity-100 transition"
                            >
                                <Link
                                    v-for="sub in item.hijos"
                                    :key="sub.id"
                                    :href="route(sub.ruta)"
                                    class="block rounded-lg px-3 py-2 text-sm text-[#334155] hover:bg-[#0F3D3E]/5 hover:text-[#0F3D3E]"
                                >
                                    {{ sub.nombre }}
                                </Link>
                            </div>
                        </div>

                        <!-- ENLACE DIRECTO -->
                        <Link
                            v-else
                            :href="route(item.ruta)"
                            class="rounded-md px-3 py-2 text-sm font-medium text-[#334155] hover:text-[#0F3D3E] hover:bg-[#0F3D3E]/5"
                            :class="
                                route().current(item.ruta + '.*')
                                    ? 'text-[#0F3D3E] border-b-2 border-[#3B82F6]'
                                    : ''
                            "
                        >
                            {{ item.nombre }}
                        </Link>
                    </template>
                </div>

                <!-- USUARIO -->
                <div class="hidden sm:flex sm:items-center sm:ml-6">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700"
                            >
                                <div>
                                    {{ $page.props.auth.user.name }} ({{
                                        $page.props.auth.user.rol
                                    }})
                                </div>
                                <svg
                                    class="ml-1 h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <div class="block px-4 py-2 text-xs text-gray-400">
                                Administrar cuenta
                            </div>

                            <DropdownLink :href="route('profile.edit')"
                                >Perfil</DropdownLink
                            >

                            <DropdownLink
                                v-if="page.props.auth.user.rol === 'admin'"
                                :href="route('usuarios.index')"
                                >Administrar Usuarios</DropdownLink
                            >

                            <div class="border-t border-gray-200" />
                            <DropdownLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Logout
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>

                <!-- MENÚ MOBILE -->
                <button
                    @click="openMain = !openMain"
                    class="md:hidden rounded-md p-2 text-[#0F3D3E]"
                >
                    <svg
                        class="h-6 w-6"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            v-if="!openMain"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                        <path
                            v-else
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </div>

        <!-- MOBILE MENU -->
        <div
            v-show="openMain"
            class="md:hidden border-t border-black/5 bg-white"
        >
            <div class="py-2 space-y-1">
                <Link
                    :href="routes.dashboard"
                    class="block px-4 py-2 text-sm font-medium text-[#334155]"
                    >Inicio</Link
                >

                <!-- ACCORDIÓN HUÉSPED -->
                <div>
                    <button
                        @click="openGuestMobile = !openGuestMobile"
                        class="flex w-full items-center justify-between px-4 py-2 text-sm font-medium text-[#334155]"
                    >
                        <span>Huésped</span>

                        <svg
                            class="h-4 w-4 transition"
                            :class="openGuestMobile ? 'rotate-180' : ''"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>

                    <div v-show="openGuestMobile" class="pb-2 space-y-1">
                        <Link
                            :href="routes.huesped"
                            class="block px-6 py-2 text-sm text-[#334155]"
                            >Huésped</Link
                        >
                        <Link
                            :href="routes.reservas"
                            class="block px-6 py-2 text-sm text-[#334155]"
                            >Reservas</Link
                        >
                        <Link
                            :href="routes.checkin"
                            class="block px-6 py-2 text-sm text-[#334155]"
                            >Check-In</Link
                        >
                        <Link
                            :href="routes.checkout"
                            class="block px-6 py-2 text-sm text-[#334155]"
                            >Check-Out</Link
                        >
                        <Link
                            :href="routes.gastos"
                            class="block px-6 py-2 text-sm text-[#334155]"
                            >Gastos</Link
                        >
                    </div>
                </div>

                <Link
                    :href="routes.habitaciones"
                    class="block px-4 py-2 text-sm font-medium text-[#334155]"
                    >Habitaciones</Link
                >
                <Link
                    :href="routes.disponibilidad"
                    class="block px-4 py-2 text-sm font-medium text-[#334155]"
                    >Disponibilidad</Link
                >

                <!-- USUARIO -->
                <div class="border-t border-black/5 px-4 pt-2">
                    <div class="text-sm font-medium text-[#0F3D3E]">
                        {{ $page.props.auth.user.name }}
                    </div>
                    <div class="text-xs text-[#64748B]">
                        {{ $page.props.auth.user.email }}
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
