<script setup lang="ts">
import { FormFieldOptions } from "@/fieldtype";
import { useOptions } from "../../../composables/useOptions";
import { ref } from "vue";

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

const fieldOptions: FormFieldOptions = JSON.parse(props.field.options);

const options = useOptions(fieldOptions);

const fieldValue = ref(String(props.old_valid ? props.old : props.data));

const onKeyPress = (event: KeyboardEvent) => {
    const target = event.target as HTMLInputElement;

    if (options.valueMatch(target.value + event.key)) {
        fieldValue.value = target.value + event.key;
    }
};
</script>

<template>
    <!-- Αριθμός -->
    <input
        type="number"
        class="form-control"
        :id="`f${field.id}`"
        :class="error ? 'is-invalid' : ''"
        :name="`f${field.id}`"
        :value="fieldValue"
        :disabled="disabled"
        :required="field.required ? 'true' : undefined"
        @keypress.prevent="onKeyPress"
    />
</template>
