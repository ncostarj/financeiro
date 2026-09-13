<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'; // 👈 Importamos o Link aqui

const form = useForm({
    email: '',
    password: '',
    remember: false
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Acesso ao Sistema" />

    <div class="min-h-screen bg-gray-100 flex flex-col justify-center items-center">
        <div class="max-w-md w-full bg-white rounded-xl shadow-md p-8">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Acesso Restrito</h2>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- E-mail -->
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
                </div>

                <!-- Lembrar-me e Esqueci a Senha -->
                <div class="flex items-center justify-between mt-4">
                    <div class="flex items-center">
                        <input id="remember" type="checkbox" v-model="form.remember" class="rounded border-gray-300 text-blue-600 shadow-sm">
                        <label for="remember" class="ml-2 block text-sm text-gray-900">Lembrar-me</label>
                    </div>

                    <!-- 👈 Link: Esqueci a senha -->
                    <Link href="/forgot-password" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                        Esqueci a senha?
                    </Link>
                </div>

                <!-- Botão Entrar -->
                <button 
                    type="submit" 
                    :disabled="form.processing" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 mt-4"
                >
                    Entrar
                </button>
            </form>

            <!-- 👈 Link: Registre-se -->
            <div class="mt-6 text-center text-sm text-gray-600 border-t pt-4">
                Não tem uma conta? 
                <Link href="/register" class="font-medium text-blue-600 hover:text-blue-500">
                    Registre-se agora
                </Link>
            </div>
        </div>
    </div>
</template>