<script setup>
import { ref, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';

const props = defineProps({
    registroId: {
        type: Number,
        required: true,
    },
    tipo: {
        type: String,
        required: true,
    },
    value: {
        type: String,
        default: null,
    },
    isEditable: {
        type: Boolean,
        default: false,
    },
    isEditing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['edit-start', 'edit-cancel', 'edit-saved']);

const inputValue = ref(props.value ? props.value.substring(0, 5) : '');
const errorMessage = ref('');
const processing = ref(false);
const timeInput = ref(null);

watch(() => props.isEditing, (editing) => {
    if (editing) {
        inputValue.value = props.value ? props.value.substring(0, 5) : '';
        errorMessage.value = '';
        processing.value = false;
        nextTick(() => {
            timeInput.value?.focus();
        });
    }
});

watch(inputValue, () => {
    if (errorMessage.value) {
        errorMessage.value = '';
    }
});

const onCellClick = () => {
    if (!props.isEditable || props.isEditing) return;
    emit('edit-start', { registroId: props.registroId, tipo: props.tipo });
};

const onCancel = () => {
    emit('edit-cancel');
};

const onSave = () => {
    if (processing.value) return;
    processing.value = true;
    errorMessage.value = '';

    router.put(`/ponto/${props.registroId}`, {
        tipo: props.tipo,
        horario: inputValue.value,
    }, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            processing.value = false;
            emit('edit-saved');
        },
        onError: (errors) => {
            processing.value = false;
            if (errors.horario) {
                errorMessage.value = errors.horario;
            } else if (errors.tipo) {
                errorMessage.value = errors.tipo;
            } else {
                errorMessage.value = trans('An error occurred. Please try again.');
            }
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const onKeydown = (event) => {
    if (event.key === 'Escape') {
        emit('edit-cancel');
    }
};

const displayValue = () => {
    if (!props.value) return '---';
    return props.value.substring(0, 5);
};
</script>

<template>
    <!-- Edit mode -->
    <div
        v-if="isEditing"
        class="rounded ring-2 ring-indigo-500 px-2 py-1"
    >
        <div class="flex items-center gap-1">
            <input
                ref="timeInput"
                type="time"
                v-model="inputValue"
                class="rounded-md border-gray-300 px-2 py-1 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                @keydown="onKeydown"
            />
            <button
                @click="onSave"
                :disabled="processing"
                class="rounded bg-indigo-600 p-1 text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                :aria-label="$t('Save')"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </button>
            <button
                @click="onCancel"
                :disabled="processing"
                class="rounded bg-gray-200 p-1 text-gray-700 transition hover:bg-gray-300 disabled:cursor-not-allowed disabled:opacity-50"
                :aria-label="$t('Cancel')"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <p v-if="errorMessage" class="mt-1 text-xs text-red-600">
            {{ errorMessage }}
        </p>
    </div>

    <!-- Display mode -->
    <span
        v-else
        class="inline-block px-1 py-0.5"
        :class="{
            'cursor-pointer rounded hover:bg-gray-100': isEditable,
        }"
        @click="onCellClick"
    >
        {{ displayValue() }}
    </span>
</template>
