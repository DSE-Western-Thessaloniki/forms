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

    if (options.valueMatch(target.value + event.key)) {
        formStore.field[props.field.id] = target.value + event.key;
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
        @keypress.prevent="onKeyPress"
        v-model="formStore.field[props.field.id]"
    />
</template>
