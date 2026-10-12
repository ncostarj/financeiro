<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
};
</script>

<template>
    <Head title="Recuperar Senha" />

    <div class="min-h-screen bg-gray-100 flex flex-col justify-center items-center">
        <div class="max-w-md w-full bg-white rounded-xl shadow-md p-8">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Recuperar Senha</h2>

            <p class="text-sm text-gray-600 mb-6 text-center">
                Informe o seu e-mail e enviaremos um link para você redefinir sua senha.
            </p>

            <!-- Mensagem de Sucesso -->
            <div v-if="status" class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-3 rounded-md">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input 
                        id="email" 
                        type="email" 
                        v-model="form.email" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500" 
                        required 
                        autofocus
                    >
                    <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                </div>

                <button 
                    type="submit" 
                    :disabled="form.processing" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 mt-4"
                >
                    Enviar Link de Recuperação
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-gray-600 border-t pt-4">
                Lembrou a senha? 
                <Link href="/login" class="font-medium text-blue-600 hover:text-blue-500">
                    Voltar para o login
                </Link>
            </div>
        </div>
    </div>
</template>