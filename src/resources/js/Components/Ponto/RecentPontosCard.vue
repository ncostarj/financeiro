<script setup>
import { Link } from '@inertiajs/vue3';
import { classeCorSaldoFormatado } from '@/utils/pontoBalanceColor.js';

defineProps({
    recentPontos: {
        type: Array,
        default: () => [],
    },
});

const formatarData = (dataStr) => {
    const datePart = dataStr.substring(0, 10);
    const [ano, mes, dia] = datePart.split('-');
    return `${dia}/${mes}/${ano}`;
};

const formatarTotalHoras = (totalHoras) => {
    if (totalHoras === null || totalHoras === undefined) return '\u2014';
    const horas = Math.floor(totalHoras);
    const minutos = Math.round((totalHoras - horas) * 60);
    return `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}`;
};
</script>

<template>
    <div
        class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition hover:border-emerald-300 hover:shadow-md"
    >
        <!-- Card Header -->
        <div class="flex items-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50">
                <svg
                    class="h-6 w-6 text-emerald-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                    />
                </svg>
            </div>
            <div class="ml-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ $t('Time Clock') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $t('Recent time entries') }}
                </p>
            </div>
        </div>

        <!-- Records List -->
        <div v-if="recentPontos.length > 0" class="mt-4">
            <!-- Column Headers -->
            <div class="flex items-center justify-between border-b border-gray-100 pb-2 text-xs font-medium text-gray-500">
                <span class="w-24">{{ $t('Date') }}</span>
                <span class="w-14 text-center">{{ $t('Hours') }}</span>
                <span class="w-16 text-right">{{ $t('Balance') }}</span>
            </div>

            <!-- Record Rows -->
            <div
                v-for="registro in recentPontos"
                :key="registro.id"
                class="flex items-center justify-between border-b border-gray-50 py-2 text-sm"
            >
                <span class="w-24 text-gray-700">{{ formatarData(registro.data) }}</span>
                <span class="w-14 text-center font-mono" :class="classeCorSaldoFormatado(registro.saldo_formatado)">{{ formatarTotalHoras(registro.total_horas) }}</span>
                <span
                    class="w-16 text-right font-mono text-sm"
                    :class="classeCorSaldoFormatado(registro.saldo_formatado)"
                >
                    {{ registro.saldo_formatado ?? '\u2014' }}
                </span>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="mt-4 py-4 text-center text-sm text-gray-500">
            {{ $t('No time clock records yet.') }}
        </div>

        <!-- Footer Link -->
        <div class="mt-4 border-t border-gray-100 pt-3">
            <Link
                href="/ponto"
                class="inline-flex items-center text-sm font-medium text-emerald-600 transition hover:text-emerald-700"
            >
                {{ $t('View all records') }}
                <svg
                    class="ml-1 h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </Link>
        </div>
    </div>
</template>
