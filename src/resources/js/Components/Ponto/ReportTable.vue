<script setup>
import { computed } from 'vue';
import { classeCorSaldo } from '@/utils/pontoBalanceColor.js';

const props = defineProps({
    relatorio: {
        type: Object,
        required: true,
    },
});

const temRegistros = computed(() => {
    return props.relatorio.registros && props.relatorio.registros.length > 0;
});

const formatarData = (dataStr) => {
    const [ano, mes, dia] = dataStr.split('-');
    return `${dia}/${mes}/${ano}`;
};

const formatarHorario = (valor) => {
    if (!valor) return '---';
    return valor.substring(0, 5);
};

const calcularExcedenteAlmoco = (saidaAlmoco, retornoAlmoco) => {
    if (!saidaAlmoco || !retornoAlmoco) return null;

    const saida = saidaAlmoco.substring(0, 5);
    const retorno = retornoAlmoco.substring(0, 5);

    const [sh, sm] = saida.split(':').map(Number);
    const [rh, rm] = retorno.split(':').map(Number);

    const diffMinutes = (rh * 60 + rm) - (sh * 60 + sm);
    const excess = diffMinutes - 60;

    if (excess === 0) return null;
    return excess;
};

const calcularExcedenteSaida = (entrada, saidaAlmoco, retornoAlmoco, saida) => {
    if (!entrada || !saidaAlmoco || !retornoAlmoco || !saida) return null;

    const e = entrada.substring(0, 5);
    const sa = saidaAlmoco.substring(0, 5);
    const r = retornoAlmoco.substring(0, 5);
    const s = saida.substring(0, 5);

    const [eh, em] = e.split(':').map(Number);
    const [sah, sam] = sa.split(':').map(Number);
    const [rh, rm] = r.split(':').map(Number);
    const [sh, smin] = s.split(':').map(Number);

    const entradaMin = eh * 60 + em;
    const saidaAlmocoMin = sah * 60 + sam;
    const retornoAlmocoMin = rh * 60 + rm;
    const saidaMin = sh * 60 + smin;

    let lunchDuration = retornoAlmocoMin - saidaAlmocoMin;
    if (lunchDuration < 0) lunchDuration += 24 * 60;

    const exitDeadlineMin = (entradaMin + 8 * 60 + lunchDuration) % (24 * 60);
    const excess = saidaMin - exitDeadlineMin;

    if (excess === 0) return null;
    return excess;
};

const formatarExcedente = (minutos) => {
    if (minutos === null) return '';
    const sinal = minutos > 0 ? '+' : '';
    return `${sinal}${minutos}min`;
};

const formatarTotalHoras = (totalHoras) => {
    if (totalHoras === null || totalHoras === undefined) return '---';
    const horas = Math.floor(totalHoras);
    const minutos = Math.round((totalHoras - horas) * 60);
    return `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}`;
};

const classSaldoFinal = (saldoFinalStr) => {
    if (!saldoFinalStr) return 'text-gray-700';
    if (saldoFinalStr.startsWith('+') && saldoFinalStr !== '+00:00') return 'text-orange-600 font-bold';
    if (saldoFinalStr.startsWith('-') && saldoFinalStr !== '-00:00') return 'text-red-600 font-bold';
    return 'text-gray-700 font-bold';
};

const classSaldoTotal = (saldoStr) => {
    if (!saldoStr) return 'text-gray-700';
    if (saldoStr.startsWith('+') && saldoStr !== '+00:00') return 'text-green-600 font-bold';
    if (saldoStr.startsWith('-') && saldoStr !== '-00:00') return 'text-red-600 font-bold';
    return 'text-gray-700 font-bold';
};
</script>

<template>
    <div class="rounded-lg bg-white shadow">
        <!-- Empty state -->
        <div v-if="!temRegistros" class="px-6 py-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="mt-4 text-sm text-gray-500">
                Nenhum registro de ponto encontrado para este período.
            </p>
        </div>

        <!-- Table with responsive scroll -->
        <div v-else class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-4 py-3 font-semibold text-gray-600">Data</th>
                        <th class="px-4 py-3 font-semibold text-gray-600">Entrada</th>
                        <th class="px-4 py-3 font-semibold text-gray-600">Saída Almoço</th>
                        <th class="px-4 py-3 font-semibold text-gray-600">Retorno Almoço</th>
                        <th class="px-4 py-3 font-semibold text-gray-600">Saída</th>
                        <th class="px-4 py-3 font-semibold text-gray-600">Total</th>
                        <th class="px-4 py-3 font-semibold text-gray-600">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr
                        v-for="registro in relatorio.registros"
                        :key="registro.data"
                        class="transition hover:bg-gray-50"
                        :class="{ 'bg-yellow-50/50': !registro.calculavel }"
                    >
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ formatarData(registro.data) }}
                        </td>
                        <td class="px-4 py-3 font-mono text-gray-700">
                            {{ formatarHorario(registro.entrada) }}
                        </td>
                        <td class="px-4 py-3 font-mono text-gray-700">
                            {{ formatarHorario(registro.saidaAlmoco) }}
                        </td>
                        <td class="px-4 py-3 font-mono text-gray-700">
                            {{ formatarHorario(registro.retornoAlmoco) }}
                            <span
                                v-if="calcularExcedenteAlmoco(registro.saidaAlmoco, registro.retornoAlmoco)"
                                class="ml-1 text-xs text-red-600 font-medium"
                            >
                                ({{ formatarExcedente(calcularExcedenteAlmoco(registro.saidaAlmoco, registro.retornoAlmoco)) }})
                            </span>
                        </td>
                        <td class="px-4 py-3 font-mono text-gray-700">
                            {{ formatarHorario(registro.saida) }}
                            <span
                                v-if="calcularExcedenteSaida(registro.entrada, registro.saidaAlmoco, registro.retornoAlmoco, registro.saida)"
                                class="ml-1 text-xs text-red-600 font-medium"
                            >
                                ({{ formatarExcedente(calcularExcedenteSaida(registro.entrada, registro.saidaAlmoco, registro.retornoAlmoco, registro.saida)) }})
                            </span>
                        </td>
                        <td class="px-4 py-3 font-mono" :class="classeCorSaldo(registro.saldoMinutos)">
                            {{ registro.calculavel ? formatarTotalHoras(registro.totalHoras) : '---' }}
                        </td>
                        <td class="px-4 py-3 font-mono" :class="classeCorSaldo(registro.saldoMinutos)">
                            {{ registro.calculavel ? registro.saldoFormatado : '---' }}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-300 bg-gray-50">
                        <td colspan="5" class="px-4 py-3 text-right font-semibold text-gray-700">
                            Totais do Mês
                        </td>
                        <td colspan="2" class="px-4 py-3">
                            <div class="space-y-1 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-600">Extras:</span>
                                    <span class="font-mono font-medium text-orange-600">
                                        {{ relatorio.totalHorasExtras }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-600">Débito:</span>
                                    <span class="font-mono font-medium text-red-600">
                                        {{ relatorio.totalHorasDebito }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 border-t border-gray-200 pt-1">
                                    <span class="text-gray-700 font-semibold">Saldo:</span>
                                    <span class="font-mono" :class="classSaldoFinal(relatorio.saldoFinal)">
                                        {{ relatorio.saldoFinal }}
                                    </span>
                                </div>
                                <template v-if="relatorio.saldoBancoHorasMinutos !== null && relatorio.saldoBancoHorasMinutos !== undefined">
                                    <div class="flex items-center gap-2 border-t border-gray-200 pt-1">
                                        <span class="text-gray-600">{{ $t('Hour Bank Balance') }}:</span>
                                        <span class="font-mono" :class="classSaldoTotal(relatorio.saldoBancoHorasFormatado)">
                                            {{ relatorio.saldoBancoHorasFormatado }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 border-t border-gray-200 pt-1">
                                        <span class="text-gray-700 font-semibold">{{ $t('Total Balance') }}:</span>
                                        <span class="font-mono" :class="classSaldoTotal(relatorio.saldoTotalFormatado)">
                                            {{ relatorio.saldoTotalFormatado }}
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</template>
