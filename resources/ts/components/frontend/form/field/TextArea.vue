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
    <!-- Περιοχή κειμένου -->
    <textarea
        class="form-control"
        :id="`f${field.id}`"
        :class="error ? 'is-invalid' : ''"
        :name="`f${field.id}`"
        rows="4"
        :disabled="disabled"
        :required="field.required ? 'true' : 'false'"
    >
        {{ old ?? data }}
    </textarea>
</template>
