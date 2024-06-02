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
    <!-- Τηλέφωνο -->
    <div>
        <input
            type="tel"
            pattern="[0-9]{10}"
            class="form-control"
            :id="`f${field.id}`"
            :class="error ? 'is-invalid' : ''"
            :name="`f${field.id}`"
            :value="old ?? data"
            :disabled="disabled"
            :required="field.required ? 'true' : 'false'"
        />
        <small>Μορφή: 1234567890</small>
    </div>
</template>
