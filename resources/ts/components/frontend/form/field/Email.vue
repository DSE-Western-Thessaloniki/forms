<script setup lang="ts">
import { FormFieldOptions } from "@/fieldtype";
import { useOptions } from "../../../composables/useOptions";
import { useFormStore } from "@/stores/formStore";

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

const onKeyPress = (event: KeyboardEvent) => {
    const target = event.target as HTMLInputElement;
    const cursorPos = target.selectionStart ?? 0;
    const inputText = target.value;

    const proposedNewText = `${inputText.slice(0, cursorPos)}${
        event.key
    }${inputText.slice(cursorPos, inputText.length)}`;

    if (!options.valueMatch(proposedNewText)) {
        event.preventDefault();
    }
};
</script>

<template>
    <!-- E-mail -->
    <input
        type="email"
        class="form-control"
        :id="`f${field.id}`"
        :class="error ? 'is-invalid' : ''"
        :name="`f${field.id}`"
        :disabled="disabled"
        :required="field.required ? 'true' : undefined"
        @keypress="onKeyPress"
        v-model="formStore.field[props.field.id]"
    />
</template>
