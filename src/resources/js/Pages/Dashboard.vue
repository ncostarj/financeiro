<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout>
    <div class="bg-white rounded-lg shadow p-6">
      <h1 class="text-2xl font-bold mb-2">Painel de Controle</h1>
      <p class="text-gray-600">Este conteúdo fica posicionado à esquerda, ajustando-se automaticamente ao espaço da tela.</p>
    </div>
  </AppLayout>
</template>