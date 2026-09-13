<script setup>
const props = defineProps({
    totalSemana: {
        type: Number,
        required: true,
    },
    alertaSemana: {
        type: Boolean,
        required: true,
    },
});

const META_SEMANAL = 40;

const formatHours = (hours) => {
    if (hours == null) return '0h';
    const h = Number(hours);
    return Number.isInteger(h) ? `${h}h` : `${h.toFixed(1)}h`;
};

const horasFaltantes = () => {
    const faltam = META_SEMANAL - props.totalSemana;
    return faltam > 0 ? faltam : 0;
};
</script>

<template>
    <div
        class="rounded-lg border p-4 shadow-sm transition-colors"
        :class="{
            'border-red-300 bg-red-50': alertaSemana,
            'border-green-300 bg-green-50': !alertaSemana,
        }"
    >
        <div class="flex items-center justify-between">
            <!-- Left: Icon and status -->
            <div class="flex items-center gap-3">
                <!-- Warning icon (alert active) -->
                <div
                    v-if="alertaSemana"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100"
                >
                    <svg
                        class="h-6 w-6 text-red-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        aria-label="Alerta: horas semanais insuficientes"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"
                        />
                    </svg>
                </div>

                <!-- Check icon (no alert) -->
                <div
                    v-else
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100"
                >
                    <svg
                        class="h-6 w-6 text-green-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        aria-label="Meta semanal atingida"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                        />
                    </svg>
                </div>

                <!-- Status text -->
                <div>
                    <p
                        class="text-sm font-semibold"
                        :class="{
                            'text-red-700': alertaSemana,
                            'text-green-700': !alertaSemana,
                        }"
                    >
                        {{ alertaSemana ? 'Horas insuficientes' : 'Meta atingida' }}
                    </p>
                    <p
                        v-if="alertaSemana"
                        class="text-xs text-red-500"
                    >
                        Faltam {{ formatHours(horasFaltantes()) }} para atingir 40h semanais
                    </p>
                    <p
                        v-else
                        class="text-xs text-green-500"
                    >
                        Meta de 40h semanais alcançada
                    </p>
                </div>
            </div>

            <!-- Right: Total hours -->
            <div class="text-right">
                <p
                    class="text-2xl font-bold"
                    :class="{
                        'text-red-700': alertaSemana,
                        'text-green-700': !alertaSemana,
                    }"
                >
                    {{ formatHours(totalSemana) }}
                </p>
                <p
                    class="text-xs"
                    :class="{
                        'text-red-500': alertaSemana,
                        'text-green-500': !alertaSemana,
                    }"
                >
                    Total semanal
                </p>
            </div>
        </div>
    </div>
</template>
