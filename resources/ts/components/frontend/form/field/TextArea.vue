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

const emit = defineEmits<{
    change: [value: string];
}>();

const fieldOptions: FormFieldOptions = JSON.parse(props.field.options);

const options = useOptions(fieldOptions);

const fieldValue = ref(String(props.old_valid ? props.old : props.data));

const onKeyPress = (event: KeyboardEvent) => {
    const target = event.target as HTMLInputElement;

    if (options.valueMatch(target.value + event.key)) {
        fieldValue.value = target.value + event.key;
    }
};

const emitValueChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    emit("change", target.value);
};
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
        :required="field.required ? 'true' : undefined"
        @keypress.prevent="onKeyPress"
        @change="emitValueChange"
        >{{ fieldValue }}</textarea
    >
</template>
