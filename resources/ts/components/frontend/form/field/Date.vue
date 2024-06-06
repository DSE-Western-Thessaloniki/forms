<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        field: App.Models.FormField;
        data: unknown;
        disabled?: boolean;
        old: unknown;
        old_valid: boolean;
        error: string;
    }>(),
    {
        disabled: false,
        error: "",
    }
);

const emit = defineEmits<{
    change: [value: string];
}>();

const emitValueChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    emit("change", target.value);
};
</script>

<template>
    <!-- Ημερομηνία -->
    <input
        type="date"
        class="form-control"
        :id="`f${field.id}`"
        :class="error ? 'is-invalid' : ''"
        :name="`f${field.id}`"
        :value="old_valid ? old : data"
        :disabled="disabled"
        :required="field.required ? 'true' : undefined"
        @input="emitValueChange"
        @change="emitValueChange"
    />
</template>
