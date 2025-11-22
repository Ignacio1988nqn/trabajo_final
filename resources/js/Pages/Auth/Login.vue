<script setup>
import { Head, useForm, Link } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";

import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

import Logo from "@/assets/logo1.png";

const props = defineProps({
    canResetPassword: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: "",
    },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar sesión" />

        <div
            class="min-h-screen bg-[#f5f3ef] flex items-center justify-center px-4"
        >
            <!-- Card principal -->
            <div
                class="w-full max-w-4xl overflow-hidden rounded-3xl bg-white shadow-xl border border-black/5 grid md:grid-cols-2"
            >
                <!-- Columna izquierda: formulario -->
                <div
                    class="px-8 py-10 md:px-10 md:py-12 flex flex-col justify-center"
                >
                    <!-- Logo + título -->
                    <div class="flex items-center gap-3 mb-6">
                        <img
                            :src="Logo"
                            alt="Hotel Iberia"
                            class="h-10 w-auto"
                        />
                        <div>
                            <p
                                class="text-xs font-semibold tracking-[0.2em] text-[#3b82f6] uppercase"
                            >
                                HOTEL IBERIA
                            </p>
                            <h1 class="text-xl font-semibold text-[#0F172A]">
                                Panel de gestión
                            </h1>
                        </div>
                    </div>

                    <!-- Texto introductorio -->
                    <p class="text-sm text-slate-500 mb-6">
                        Ingresá con tu usuario para gestionar reservas,
                        habitaciones y tareas diarias del hotel.
                    </p>

                    <!-- Mensaje de estado -->
                    <div
                        v-if="status"
                        class="mb-4 text-sm font-medium text-emerald-600"
                    >
                        {{ status }}
                    </div>

                    <!-- Formulario -->
                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Email -->
                        <div>
                            <InputLabel
                                for="email"
                                value="Correo electrónico"
                            />

                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full"
                                autocomplete="username"
                                required
                                placeholder="nombre@hoteliberia.com"
                            />

                            <InputError
                                class="mt-2"
                                :message="form.errors.email"
                            />
                        </div>

                        <!-- Password -->
                        <div>
                            <div class="flex items-center justify-between">
                                <InputLabel for="password" value="Contraseña" />

                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-xs font-medium text-[#3b82f6] hover:text-[#1d4ed8]"
                                >
                                    ¿Olvidaste tu contraseña?
                                </Link>
                            </div>

                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-1 block w-full"
                                autocomplete="current-password"
                                required
                                placeholder="••••••••"
                            />

                            <InputError
                                class="mt-2"
                                :message="form.errors.password"
                            />
                        </div>

                        <!-- Recordarme -->
                        <div class="flex items-center justify-between">
                            <label
                                class="inline-flex items-center gap-2 text-xs text-slate-600"
                            >
                                <input
                                    v-model="form.remember"
                                    type="checkbox"
                                    class="rounded border-slate-300 text-[#0F3D3E] shadow-sm focus:ring-[#0F3D3E]"
                                    name="remember"
                                />
                                <span>Recordarme en este dispositivo</span>
                            </label>
                        </div>

                        <!-- Botón -->
                        <div class="pt-2">
                            <PrimaryButton
                                class="w-full justify-center bg-[#0F3D3E] hover:bg-[#0b2830]"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Iniciar sesión
                            </PrimaryButton>
                        </div>
                    </form>

                    <!-- Registro opcional -->
                    <div
                        class="mt-6 text-xs text-slate-500 flex items-center justify-between"
                    >
                        <span>Sistema interno de gestión hotelera.</span>
                        <Link
                            v-if="route().has('register')"
                            :href="route('register')"
                            class="font-medium text-[#3b82f6] hover:text-[#1d4ed8]"
                        >
                            Registrarse
                        </Link>
                    </div>
                </div>

                <!-- Columna derecha: imagen / decoración -->
                <div
                    class="hidden md:block bg-gradient-to-br from-[#0F3D3E] via-[#1f2933] to-[#020617] relative"
                >
                    <div
                        class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_top,_#facc15,_transparent_60%),_radial-gradient(circle_at_bottom,_#3b82f6,_transparent_55%)]"
                    />

                    <div
                        class="relative h-full flex flex-col justify-between p-8 text-slate-100"
                    >
                        <div>
                            <h2 class="text-lg font-semibold">
                                Bienvenido al panel de Hotel Iberia
                            </h2>
                            <p class="mt-2 text-sm text-slate-200/80">
                                Controlá reservas, ocupación, limpieza y gastos
                                del hotel desde un solo lugar, con una interfaz
                                pensada para el trabajo diario.
                            </p>
                        </div>

                        <div class="space-y-3 text-xs text-slate-200/80">
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/10 text-[10px]"
                                >
                                    1
                                </span>
                                <p>
                                    Accedé solo con tu usuario interno del
                                    hotel.
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/10 text-[10px]"
                                >
                                    2
                                </span>
                                <p>
                                    Visualizá en tiempo real la ocupación y el
                                    estado de habitaciones.
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/10 text-[10px]"
                                >
                                    3
                                </span>
                                <p>
                                    Gestioná tareas de limpieza y gastos sin
                                    salir del sistema.
                                </p>
                            </div>
                        </div>

                        <p class="mt-6 text-[10px] text-slate-400">
                            © {{ new Date().getFullYear() }} Hotel Iberia ·
                            Sistema de gestión interna.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
