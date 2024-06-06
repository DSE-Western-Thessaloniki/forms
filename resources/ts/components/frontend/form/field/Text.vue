<script setup lang="ts">
import { FormFieldOptions } from "@/fieldtype";
import { useOptions } from "../../../composables/useOptions";
import { ref } from "vue";
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

const fieldOptions: FormFieldOptions = JSON.parse(props.field.options);

const options = useOptions(fieldOptions);

const fieldValue = ref(String(props.old_valid ? props.old : props.data));

const formStore = useFormStore();
formStore.field[props.field.id] = fieldValue.value;

const onKeyPress = (event: KeyboardEvent) => {
    const target = event.target as HTMLInputElement;

    if (options.valueMatch(target.value + event.key)) {
        formStore.field[props.field.id] = target.value + event.key;
    }
};
</script>

<template>
    <!-- Πεδίο κειμένου -->
    <input
        type="text"
        class="form-control"
        :id="`f${field.id}`"
        :class="error ? 'is-invalid' : ''"
        :name="`f${field.id}`"
        :value="fieldValue"
        :disabled="disabled"
        :required="field.required ? 'true' : undefined"
        v-model="formStore.field[props.field.id]"
        @keypress.prevent="onKeyPress"
    />
</template>
