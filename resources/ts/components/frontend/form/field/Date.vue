<script setup lang="ts">
import { useFormStore } from "@/stores/formStore";

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

const formStore = useFormStore();
formStore.field[props.field.id] = String(
    props.old_valid ? props.old : props.data
);
</script>

<template>
    <!-- Ημερομηνία -->
    <input
        type="date"
        class="form-control"
        :id="`f${field.id}`"
        :class="error ? 'is-invalid' : ''"
        :name="`f${field.id}`"
        :disabled="disabled"
        :required="field.required ? 'true' : undefined"
        v-model="formStore.field[props.field.id]"
    />
</template>
