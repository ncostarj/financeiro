<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    issueKey: {
        type: String,
        required: true,
    },
});

const confirming = ref(false);
const processing = ref(false);

const confirmRemoval = () => {
    confirming.value = true;
};

const cancel = () => {
    confirming.value = false;
};

const remove = () => {
    processing.value = true;
    router.delete(`/jira/cards/${props.issueKey}`, {
        preserveScroll: true,
        onSuccess: () => {
            confirming.value = false;
            processing.value = false;
        },
        onError: () => {
            confirming.value = false;
            processing.value = false;
        },
    });
};
</script>

<template>
    <div>
        <button
            type="button"
            @click="confirmRemoval"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 hover:border-red-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
        >
            <svg class="mr-1 h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
            Remover
        </button>

        <Modal :show="confirming" max-width="sm" @close="cancel">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Remover Card
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Tem certeza que deseja remover este card?
                    <span class="font-medium text-gray-900">{{ issueKey }}</span>
                    será removido permanentemente.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="cancel">
                        Cancelar
                    </SecondaryButton>

                    <DangerButton
                        :disabled="processing"
                        :class="{ 'opacity-25': processing }"
                        @click="remove"
                    >
                        <svg
                            v-if="processing"
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
