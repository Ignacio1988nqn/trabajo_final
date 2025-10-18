<script setup>

import NavBar from '@/Components/NavBar.vue'

</script>

<template>
    <div class="min-h-screen bg-[var(--bg)] text-[var(--text)]">

  <div class="min-h-screen bg-[#F8F5F0] text-[#0F3D3E]" >
    <!-- 👉 Nav como componente -->
    <NavBar :user="$page.props.auth.user" />

    <!-- Encabezado de página -->
    <header v-if="$slots.header" class="bg-transparent">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>

    <!-- Contenido -->
    <main>
      <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <slot />
      </div>
    </main>
  </div>

  </div>
</template>