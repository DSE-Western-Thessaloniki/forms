<script setup lang="ts">
import type { FormFieldOptions } from "@/fieldtype";
import { useOptions } from "../../../composables/useOptions";
import { useFormStore } from "@/stores/formStore";
import { useTextInputEventHandlers } from "@/components/composables/useTextInputEventHandlers";

const props = withDefaults(
    defineProps<{
        field: App.Models.FormField;
        disabled?: boolean;
        error: string;
    }>(),
    {
        disabled: false,
        error: "",
    }
);

const fieldOptions: FormFieldOptions = JSON.parse(props.field.options);

const options = useOptions(fieldOptions);

const formStore = useFormStore();

const eventHandlers = useTextInputEventHandlers(options);
</script>

<template>
    <!-- Πεδίο κειμένου -->
    <input
        type="text"
        class="form-control"
        :id="`f${field.id}`"
        :class="error ? 'is-invalid' : ''"
        :name="`f${field.id}`"
        :disabled="disabled"
        :required="field.required ? 'true' : undefined"
        v-model="formStore.field[props.field.id]"
        @keypress="eventHandlers.onKeyPress"
        @paste="eventHandlers.onPaste"
        autocomplete="off"
    />
</template>
