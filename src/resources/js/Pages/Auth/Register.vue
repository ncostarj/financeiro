<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Criar Conta" />

    <div class="min-h-screen bg-gray-100 flex flex-col justify-center items-center">
        <div class="max-w-md w-full bg-white rounded-xl shadow-md p-8">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Criar Nova Conta</h2>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Nome -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nome completo</label>
                    <input 
                        id="name" 
                        type="text" 
                        v-model="form.name" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500" 
                        required 
                        autofocus
                    >
                    <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                </div>

                <!-- E-mail -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input 
                        id="email" 
                        type="email" 
                        v-model="form.email" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500" 
                        required
                    >
                    <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                </div>

                <!-- Senha -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
                    <input 
                        id="password" 
                        type="password" 
                        v-model="form.password" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500" 
                        required
                    >
                    <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
                </div>

                <!-- Confirmar Senha -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Senha</label>
                    <input 
                        id="password_confirmation" 
                        type="password" 
                        v-model="form.password_confirmation" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500" 
                        required
                    >
                </div>

                <!-- Botão Registrar -->
                <button 
                    type="submit" 
                    :disabled="form.processing" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 mt-4"
                >
                    Registrar
                </button>
            </form>

            <!-- Link Voltar para Login -->
            <div class="mt-6 text-center text-sm text-gray-600 border-t pt-4">
                Já tem uma conta? 
                <Link href="/login" class="font-medium text-blue-600 hover:text-blue-500">
                    Faça login aqui
                </Link>
            </div>
        </div>
    </div>
</template>