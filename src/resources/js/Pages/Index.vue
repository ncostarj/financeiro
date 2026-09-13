<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    user: Object
});
</script>

<template>
    <Head title="Página Inicial" />

    <div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-6">
        <div class="max-w-2xl w-full bg-white rounded-xl shadow-md p-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Bem-vindo(a) ao Sistema!</h1>
            
            <p class="text-gray-600 mb-6">
                Você está logado como: <span class="font-semibold text-blue-600">{{ user?.name || 'Usuário' }}</span>
            </p>

            <Link 
                href="/logout" 
                method="post" 
                as="button" 
                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded transition"
            >
                Sair (Logout)
            </Link>
        </div>
    </div>
</template>