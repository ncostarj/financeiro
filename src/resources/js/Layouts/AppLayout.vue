<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <div class="min-h-screen flex flex-col bg-gray-100 text-gray-800">
    <!-- NAVBAR SUPERIOR -->
    <header class="bg-white border-b border-gray-200 shadow-sm z-10">
      <div class="px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <span class="font-bold text-xl text-indigo-600">{{ $page.props.appName }}</span>
        </div>

        <div class="flex items-center gap-4">
          <Link href="/dashboard" class="text-sm font-medium hover:text-indigo-600">Início</Link>
          <Link href="/logout" as="button" class="text-sm font-medium text-red-600 hover:underline">
            Sair
          </Link>
        </div>
      </div>
    </header>

    <!-- CORPO PRINCIPAL -->
    <div class="flex-1 flex overflow-hidden">
      
      <!-- MENU LATERAL (Esquerda) -->
      <aside class="w-64 bg-white border-r border-gray-200 p-4 hidden md:flex flex-col justify-between">
        <div class="space-y-4">
          <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
            Navegação
          </h3>
          <nav class="flex flex-col space-y-1">
            <Link href="/dashboard" class="px-3 py-2 rounded-md bg-indigo-50 text-indigo-700 font-medium text-sm">
              Dashboard
            </Link>
          </nav>
        </div>

        <div class="border-t border-gray-100 pt-4 text-xs text-gray-400">
          Status do Sistema: <span class="text-green-500 font-semibold">Online</span>
        </div>
      </aside>

      <!-- CONTEÚDO PRINCIPAL (Direita) -->
      <main class="flex-1 p-6 overflow-y-auto">
        <slot />
      </main>

    </div>
  </div>
</template>