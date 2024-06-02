<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        field: App.Models.FormField;
        data: unknown;
        disabled?: boolean;
        old?: unknown;
        error: string;
    }>(),
    {
        disabled: false,
        error: "",
    }
);
</script>

<template>
    <!-- E-mail -->
    <input
        type="email"
        class="form-control"
        :id="`f${field.id}`"
        :class="error ? 'is-invalid' : ''"
        :name="`f${field.id}`"
        :value="old ?? data"
        :disabled="disabled"
        :required="field.required ? 'true' : 'false'"
    />
</template>
