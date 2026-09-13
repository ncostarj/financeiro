<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    proximaMarcacao: {
        type: String,
        default: null,
    },
    proximaMarcacaoLabel: {
        type: String,
        default: null,
    },
});

const currentTime = ref('');
let timer = null;

const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

onMounted(() => {
    updateTime();
    timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
    if (timer) {
        clearInterval(timer);
    }
});

const jornadaCompleta = computed(() => props.proximaMarcacao === null);

const buttonLabel = computed(() => {
    if (jornadaCompleta.value) {
        return 'Jornada Completa';
    }
    return `Registrar ${props.proximaMarcacaoLabel}`;
});

const form = useForm({
    tipo: props.proximaMarcacao,
});

const registrar = () => {
    if (jornadaCompleta.value || form.processing) {
        return;
    }
    form.tipo = props.proximaMarcacao;
    form.post('/ponto/registrar', {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="flex flex-col items-center gap-4">
        <div class="text-4xl font-bold text-gray-700 tabular-nums">
            {{ currentTime }}
        </div>

        <button
            @click="registrar"
            :disabled="jornadaCompleta || form.processing"
            class="w-full rounded-lg px-8 py-4 text-lg font-semibold text-white shadow-md transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2"
            :class="{
                'bg-green-600 hover:bg-green-700 focus:ring-green-500 active:bg-green-800': !jornadaCompleta && !form.processing,
                'bg-gray-400 cursor-not-allowed': jornadaCompleta,
                'bg-green-400 cursor-wait': form.processing && !jornadaCompleta,
            }"
        >
            <span v-if="form.processing" class="flex items-center justify-center gap-2">
                <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Registrando...
            </span>
            <span v-else>
                {{ buttonLabel }}
            </span>
        </button>
    </div>
</template>
