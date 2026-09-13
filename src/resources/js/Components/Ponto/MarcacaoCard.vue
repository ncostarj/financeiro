<script setup>
import { ref, computed, toRef } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { useLunchDeadline } from '@/composables/useLunchDeadline.js';
import { useLunchReturnDeadline } from '@/composables/useLunchReturnDeadline.js';
import { useExitDeadline } from '@/composables/useExitDeadline.js';
import { useDeadlineNotification } from '@/composables/useDeadlineNotification.js';
import { useWorkedTimeBalance } from '@/composables/useWorkedTimeBalance.js';

const props = defineProps({
    registro: {
        type: Object,
        default: null,
    },
});

const registroRef = toRef(props, 'registro');
const { deadline, isExceeded, shouldShow } = useLunchDeadline(registroRef);
useDeadlineNotification(deadline, shouldShow, trans);

const {
    deadline: returnDeadline,
    isExceeded: returnIsExceeded,
    shouldShow: returnShouldShow,
} = useLunchReturnDeadline(registroRef);
useDeadlineNotification(returnDeadline, returnShouldShow, trans, {
    titleKey: 'Return deadline approaching',
    bodyKey: 'Your lunch return deadline is :time',
});

const {
    deadline: exitDeadline,
    isExceeded: exitIsExceeded,
    shouldShow: exitShouldShow,
} = useExitDeadline(registroRef);
useDeadlineNotification(exitDeadline, exitShouldShow, trans, {
    titleKey: 'Exit time approaching',
    bodyKey: 'Your exit deadline is :time',
});

const {
    balance: workedTimeBalance,
    shouldShow: balanceShouldShow,
} = useWorkedTimeBalance(registroRef);

const lunchExcessMinutes = computed(() => {
    const reg = props.registro;
    if (!reg?.saida_almoco || !reg?.retorno_almoco) return null;

    const saida = reg.saida_almoco.substring(0, 5);
    const retorno = reg.retorno_almoco.substring(0, 5);

    const [sh, sm] = saida.split(':').map(Number);
    const [rh, rm] = retorno.split(':').map(Number);

    const diffMinutes = (rh * 60 + rm) - (sh * 60 + sm);
    const excess = diffMinutes - 60;

    return excess > 0 ? excess : null;
});

const marcacoes = computed(() => [
    { tipo: 'entrada', label: 'Entrada', valor: props.registro?.entrada ?? null },
    { tipo: 'saida_almoco', label: 'Saída Almoço', valor: props.registro?.saida_almoco ?? null },
    { tipo: 'retorno_almoco', label: 'Retorno Almoço', valor: props.registro?.retorno_almoco ?? null },
    { tipo: 'saida', label: 'Saída', valor: props.registro?.saida ?? null },
]);

const editando = ref(null);

const form = useForm({
    tipo: '',
    horario: '',
});

const formatarHorario = (valor) => {
    if (!valor) return '---';
    return valor.substring(0, 5);
};

const iniciarEdicao = (tipo, valorAtual) => {
    editando.value = tipo;
    form.tipo = tipo;
    form.horario = valorAtual ? valorAtual.substring(0, 5) : '';
};

const cancelarEdicao = () => {
    editando.value = null;
    form.reset();
    form.clearErrors();
};

const salvarEdicao = () => {
    if (!props.registro) return;

    form.put(`/ponto/${props.registro.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editando.value = null;
            form.reset();
        },
    });
};
</script>

<template>
    <div class="rounded-lg bg-white p-6 shadow">
        <h3 class="mb-4 text-lg font-semibold text-gray-700">
            Marcações do Dia
        </h3>

        <div v-if="!registro" class="py-4 text-center text-gray-500">
            Nenhum registro para hoje.
        </div>

        <div v-else class="space-y-3">
            <div
                v-for="marcacao in marcacoes"
                :key="marcacao.tipo"
                class="flex items-center justify-between rounded-md border border-gray-200 px-4 py-3"
            >
                <!-- Display mode -->
                <template v-if="editando !== marcacao.tipo">
                    <div class="flex items-center gap-3">
                        <span class="w-32 text-sm font-medium text-gray-600">
                            {{ marcacao.label }}
                        </span>
                        <span
                            class="font-mono text-base"
                            :class="marcacao.valor ? 'text-gray-900' : 'text-gray-400'"
                        >
                            {{ formatarHorario(marcacao.valor) }}
                        </span>
                        <span
                            v-if="marcacao.tipo === 'saida_almoco' && shouldShow"
                            class="ml-2 text-xs"
                            :class="isExceeded ? 'text-red-600 font-semibold' : 'text-gray-500'"
                        >
                            {{ $t('Lunch deadline') }}: {{ deadline }}
                        </span>
                        <span
                            v-if="marcacao.tipo === 'retorno_almoco' && returnShouldShow"
                            class="ml-2 text-xs"
                            :class="returnIsExceeded ? 'text-red-600 font-semibold' : 'text-gray-500'"
                        >
                            {{ $t('Return deadline') }}: {{ returnDeadline }}
                        </span>
                        <span
                            v-if="marcacao.tipo === 'retorno_almoco' && lunchExcessMinutes"
                            class="ml-2 text-xs text-amber-600 font-medium"
                        >
                            (+{{ lunchExcessMinutes }}min)
                        </span>
                        <span
                            v-if="marcacao.tipo === 'saida' && exitShouldShow"
                            class="ml-2 text-xs text-gray-500"
                        >
                            {{ $t('Exit deadline') }}: {{ exitDeadline }}
                        </span>
                        <span
                            v-if="marcacao.tipo === 'saida' && balanceShouldShow && workedTimeBalance"
                            class="ml-2 text-xs font-medium"
                            :class="workedTimeBalance.isPositive ? 'text-emerald-600' : 'text-red-600'"
                        >
                            ({{ workedTimeBalance.formatted }})
                        </span>
                    </div>

                    <button
                        v-if="marcacao.valor"
                        @click="iniciarEdicao(marcacao.tipo, marcacao.valor)"
                        class="rounded p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                        :aria-label="`Editar ${marcacao.label}`"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </button>
                </template>

                <!-- Edit mode -->
                <template v-else>
                    <div class="flex flex-1 items-center gap-3">
                        <span class="w-32 text-sm font-medium text-gray-600">
                            {{ marcacao.label }}
                        </span>
                        <div class="flex flex-1 flex-col">
                            <div class="flex items-center gap-2">
                                <input
                                    type="time"
                                    v-model="form.horario"
                                    class="rounded-md border-gray-300 px-2 py-1 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :aria-label="`Novo horário para ${marcacao.label}`"
                                />
                                <button
                                    @click="salvarEdicao"
                                    :disabled="form.processing"
                                    class="rounded bg-indigo-600 px-2.5 py-1 text-xs font-medium text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    aria-label="Salvar"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                <button
                                    @click="cancelarEdicao"
                                    :disabled="form.processing"
                                    class="rounded bg-gray-200 px-2.5 py-1 text-xs font-medium text-gray-700 transition hover:bg-gray-300 disabled:cursor-not-allowed disabled:opacity-50"
                                    aria-label="Cancelar"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <p v-if="form.errors.horario" class="mt-1 text-xs text-red-600">
                                {{ form.errors.horario }}
                            </p>
                            <p v-if="form.errors.tipo" class="mt-1 text-xs text-red-600">
                                {{ form.errors.tipo }}
                            </p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>
