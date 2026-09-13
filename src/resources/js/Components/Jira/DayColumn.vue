<script setup>
import WorklogItem from '@/Components/Jira/WorklogItem.vue';

const props = defineProps({
    dia: {
        type: Object,
        required: true,
        // Expected shape: { data, diaSemana, totalHoras, alerta, worklogs }
    },
});

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    try {
        const date = new Date(dateStr + 'T00:00:00');
        return date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
        });
    } catch {
        return dateStr;
    }
};

const formatHours = (hours) => {
    if (hours == null) return '0h';
    const h = Number(hours);
    return Number.isInteger(h) ? `${h}h` : `${h.toFixed(1)}h`;
};
</script>

<template>
    <div
        class="flex flex-col rounded-lg border bg-white shadow-sm transition-colors"
        :class="{
            'border-red-300 bg-red-50/50': dia.alerta,
            'border-gray-200': !dia.alerta,
        }"
    >
        <!-- Header: Day name and date -->
        <div
            class="flex items-center justify-between rounded-t-lg border-b px-3 py-2"
            :class="{
                'border-red-200 bg-red-50': dia.alerta,
                'border-gray-100 bg-gray-50': !dia.alerta,
            }"
        >
            <div class="flex items-center gap-2">
                <h3
                    class="text-sm font-semibold"
                    :class="{
                        'text-red-700': dia.alerta,
                        'text-gray-700': !dia.alerta,
                    }"
                >
                    {{ dia.diaSemana }}
                </h3>
                <span
                    class="text-xs"
                    :class="{
                        'text-red-500': dia.alerta,
                        'text-gray-400': !dia.alerta,
                    }"
                >
                    {{ formatDate(dia.data) }}
                </span>
            </div>

            <!-- Alert indicator -->
            <div v-if="dia.alerta" class="flex items-center" title="Menos de 8h registradas">
                <svg
                    class="h-4 w-4 text-red-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    aria-label="Alerta: horas insuficientes"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"
                    />
                </svg>
            </div>
        </div>

        <!-- Worklogs list -->
        <div class="flex-1 overflow-y-auto px-1 py-2">
            <template v-if="dia.worklogs && dia.worklogs.length > 0">
                <WorklogItem
                    v-for="worklog in dia.worklogs"
                    :key="worklog.id"
                    :worklog="worklog"
                />
            </template>

            <!-- Empty state -->
            <div v-else class="flex flex-col items-center justify-center py-6 text-center">
                <svg
                    class="mb-2 h-8 w-8 text-gray-300"
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
                <p class="text-xs text-gray-400">Nenhum registro</p>
            </div>
        </div>

        <!-- Footer: Total hours -->
        <div
            class="mt-auto rounded-b-lg border-t px-3 py-2"
            :class="{
                'border-red-200 bg-red-50': dia.alerta,
                'border-gray-100 bg-gray-50': !dia.alerta,
            }"
        >
            <div class="flex items-center justify-between">
                <span
                    class="text-xs font-medium"
                    :class="{
                        'text-red-600': dia.alerta,
                        'text-gray-500': !dia.alerta,
                    }"
                >
                    Total
                </span>
                <span
                    class="text-sm font-bold"
                    :class="{
                        'text-red-700': dia.alerta,
                        'text-gray-700': !dia.alerta,
                    }"
                >
                    {{ formatHours(dia.totalHoras) }}
                </span>
            </div>
        </div>
    </div>
</template>
