<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    worklog: {
        type: Object,
        required: true,
    },
    issueKey: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['updated', 'removed']);

// Edit state
const editing = ref(false);
const editDescription = ref('');
const editProcessing = ref(false);
const editError = ref('');

// Remove state
const confirmingRemoval = ref(false);
const removeProcessing = ref(false);

// Edit functions
const startEditing = () => {
    editDescription.value = props.worklog.description || '';
    editError.value = '';
    editing.value = true;
};

const cancelEditing = () => {
    editing.value = false;
    editDescription.value = '';
    editError.value = '';
};

const saveDescription = () => {
    if (!editDescription.value.trim()) {
        editError.value = 'A descrição é obrigatória.';
        return;
    }

    if (editDescription.value.length > 255) {
        editError.value = 'A descrição deve ter no máximo 255 caracteres.';
        return;
    }

    editProcessing.value = true;
    editError.value = '';

    router.put(`/jira/worklogs/${props.issueKey}/${props.worklog.id}`, {
        description: editDescription.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            editing.value = false;
            editProcessing.value = false;
            emit('updated');
        },
        onError: (errors) => {
            editProcessing.value = false;
            editError.value = errors.description || errors.worklog || 'Erro ao atualizar o worklog.';
        },
    });
};

// Remove functions
const confirmRemoval = () => {
    confirmingRemoval.value = true;
};

const cancelRemoval = () => {
    confirmingRemoval.value = false;
};

const removeWorklog = () => {
    removeProcessing.value = true;

    router.delete(`/jira/worklogs/${props.issueKey}/${props.worklog.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            confirmingRemoval.value = false;
            removeProcessing.value = false;
            emit('removed');
        },
        onError: () => {
            confirmingRemoval.value = false;
            removeProcessing.value = false;
        },
    });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    try {
        const date = new Date(dateStr);
        return date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <div class="flex items-start justify-between gap-3 px-4 py-2 text-sm">
        <!-- Worklog info -->
        <div class="min-w-0 flex-1">
            <!-- Display mode -->
            <template v-if="!editing">
                <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-700">{{ worklog.hours }}h</span>
                    <span class="text-gray-400">·</span>
                    <span class="text-xs text-gray-500">{{ formatDate(worklog.started) }}</span>
                    <span v-if="worklog.author" class="text-gray-400">·</span>
                    <span v-if="worklog.author" class="text-xs text-gray-500">{{ worklog.author }}</span>
                </div>
                <p class="mt-0.5 truncate text-gray-600">{{ worklog.description || '(sem descrição)' }}</p>
            </template>

            <!-- Edit mode -->
            <template v-else>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-medium text-gray-700">{{ worklog.hours }}h</span>
                    <span class="text-gray-400">·</span>
                    <span class="text-xs text-gray-500">{{ formatDate(worklog.started) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <input
                        v-model="editDescription"
                        type="text"
                        maxlength="255"
                        class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Descrição do worklog"
                        @keyup.enter="saveDescription"
                        @keyup.escape="cancelEditing"
                    />
                    <button
                        type="button"
                        :disabled="editProcessing"
                        @click="saveDescription"
                        class="inline-flex shrink-0 items-center rounded-md border border-transparent bg-indigo-600 px-2 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25"
                    >
                        <svg
                            v-if="editProcessing"
                            class="-ml-0.5 mr-1 h-3 w-3 animate-spin text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        Salvar
                    </button>
                    <button
                        type="button"
                        :disabled="editProcessing"
                        @click="cancelEditing"
                        class="inline-flex shrink-0 items-center rounded-md border border-gray-300 bg-white px-2 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25"
                    >
                        Cancelar
                    </button>
                </div>
                <InputError :message="editError" class="mt-1" />
            </template>
        </div>

        <!-- Action buttons (only in display mode) -->
        <div v-if="!editing" class="flex shrink-0 items-center gap-1">
            <button
                type="button"
                @click="startEditing"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-2 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Editar
            </button>
            <button
                type="button"
                @click="confirmRemoval"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 hover:border-red-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
            >
                <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
                Remover
            </button>
        </div>

        <!-- Remove confirmation modal -->
        <Modal :show="confirmingRemoval" max-width="sm" @close="cancelRemoval">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Remover Worklog
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Tem certeza que deseja remover este worklog?
                    <span class="font-medium text-gray-900">{{ worklog.hours }}h</span>
                    de <span class="font-medium text-gray-900">{{ issueKey }}</span>
                    será removido permanentemente.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="cancelRemoval">
                        Cancelar
                    </SecondaryButton>

                    <DangerButton
                        :disabled="removeProcessing"
                        :class="{ 'opacity-25': removeProcessing }"
                        @click="removeWorklog"
                    >
                        <svg
                            v-if="removeProcessing"
                            class="-ml-1 mr-2 h-4 w-4 animate-spin text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        Sim, remover
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </div>
</template>
