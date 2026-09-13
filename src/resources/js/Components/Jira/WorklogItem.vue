<script setup>
defineProps({
    worklog: {
        type: Object,
        required: true,
        // Expected shape: { id, ticketId, resumoTarefa, descricao, horas }
    },
});
</script>

<template>
    <div class="rounded-md border border-gray-100 bg-white px-3 py-2 mb-1.5 hover:bg-gray-50 transition-colors">
        <div class="flex items-center justify-between gap-2">
            <span class="text-xs font-medium text-indigo-600 shrink-0">{{ worklog.ticketId }}</span>
            <span class="text-xs font-semibold text-gray-700">{{ worklog.horas }}h</span>
        </div>
        <p class="mt-0.5 text-xs text-gray-600 truncate" :title="worklog.resumoTarefa">
            {{ worklog.resumoTarefa }}
        </p>
        <p v-if="worklog.descricao" class="mt-0.5 text-xs text-gray-400 truncate" :title="worklog.descricao">
            {{ worklog.descricao }}
        </p>
    </div>
</template>
