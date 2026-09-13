<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    projectKey: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    project_key: props.projectKey,
    summary: '',
});

watch(() => props.projectKey, (newKey) => {
    form.project_key = newKey;
});

const submit = () => {
    form.post('/jira/cards', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('summary');
            emit('close');
        },
    });
};

const close = () => {
    form.reset('summary');
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <Modal :show="show" max-width="md" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Adicionar Card
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Crie um novo card no projeto {{ projectKey }}.
            </p>

            <form @submit.prevent="submit" class="mt-6">
                <div>
                    <InputLabel for="card-summary" value="Resumo" />
                    <TextInput
                        id="card-summary"
                        v-model="form.summary"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Resumo do card (ex: Implementar login)"
                        autofocus
                    />
                    <InputError :message="form.errors.summary" class="mt-2" />
                    <InputError :message="form.errors.card" class="mt-2" />
                    <InputError :message="form.errors.project_key" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="close">
                        Cancelar
                    </SecondaryButton>

                    <PrimaryButton
                        :disabled="form.processing"
                        :class="{ 'opacity-25': form.processing }"
                    >
                        <svg
                            v-if="form.processing"
                            class="-ml-1 mr-2 h-4 w-4 animate-spin text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        Criar Card
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
