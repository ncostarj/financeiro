<script setup>
import { watch } from 'vue';
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
    issueKey: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['close']);

const today = new Date().toISOString().split('T')[0];

const form = useForm({
    issue_key: props.issueKey,
    time_spent: 1,
    started: today,
    description: '',
});

watch(() => props.issueKey, (newKey) => {
    form.issue_key = newKey;
});

watch(() => props.show, (visible) => {
    if (visible) {
        form.started = new Date().toISOString().split('T')[0];
    }
});

const submit = () => {
    form.post(`/jira/cards/${props.issueKey}/worklogs`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('time_spent', 'description');
            form.time_spent = 1;
            form.started = new Date().toISOString().split('T')[0];
            emit('close');
        },
    });
};

const close = () => {
    form.reset('time_spent', 'description');
    form.time_spent = 1;
    form.started = new Date().toISOString().split('T')[0];
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <Modal :show="show" max-width="md" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Adicionar Horas
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Registre horas trabalhadas no card {{ issueKey }}.
            </p>

            <form @submit.prevent="submit" class="mt-6 space-y-4">
                <div>
                    <InputLabel for="worklog-time-spent" value="Horas" />
                    <TextInput
                        id="worklog-time-spent"
                        v-model.number="form.time_spent"
                        type="number"
                        step="0.25"
                        min="0.25"
                        max="24"
                        class="mt-1 block w-full"
                        placeholder="1.0"
                        autofocus
                    />
                    <InputError :message="form.errors.time_spent" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="worklog-started" value="Data" />
                    <TextInput
                        id="worklog-started"
                        v-model="form.started"
                        type="date"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.started" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="worklog-description" value="Descrição" />
                    <TextInput
                        id="worklog-description"
                        v-model="form.description"
                        type="text"
                        maxlength="255"
                        class="mt-1 block w-full"
                        placeholder="Descrição do trabalho realizado"
                    />
                    <InputError :message="form.errors.description" class="mt-2" />
                </div>

                <InputError :message="form.errors.issue_key" class="mt-2" />

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
                        Salvar Horas
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
