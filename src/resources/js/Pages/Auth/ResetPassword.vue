<script setup>
import { useForm, Head } from '@inertiajs/vue3';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/reset-password', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Redefinir Senha" />

    <div class="min-h-screen bg-gray-100 flex flex-col justify-center items-center">
        <div class="max-w-md w-full bg-white rounded-xl shadow-md p-8">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Criar Nova Senha</h2>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input 
                        id="email" 
                        type="email" 
                        v-model="form.email" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500 bg-gray-50" 
                        required 
                        readonly
                    >
                    <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Nova Senha</label>
                    <input 
                        id="password" 
                        type="password" 
                        v-model="form.password" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500" 
                        required
                        autofocus
                    >
                    <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Nova Senha</label>
                    <input 
                        id="password_confirmation" 
                        type="password" 
                        v-model="form.password_confirmation" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500" 
                        required
                    >
                </div>

                <button 
                    type="submit" 
                    :disabled="form.processing" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 mt-4"
                >
                    Redefinir Senha
                </button>
            </form>
        </div>
    </div>
</template>