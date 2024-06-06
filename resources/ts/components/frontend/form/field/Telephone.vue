<script setup lang="ts">
import { FormFieldOptions } from "@/fieldtype";
import { useOptions } from "../../../composables/useOptions";
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

const formStore = useFormStore();
formStore.field[props.field.id] = String(
    props.old_valid ? props.old : props.data
);

const onKeyPress = (event: KeyboardEvent) => {
    const target = event.target as HTMLInputElement;

    if (options.valueMatch(target.value + event.key)) {
        formStore.field[props.field.id] = target.value + event.key;
    }
};
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
            :disabled="disabled"
            :required="field.required ? 'true' : undefined"
            @keypress.prevent="onKeyPress"
            v-model="formStore.field[props.field.id]"
        />
        <small>Μορφή: 1234567890</small>
    </div>
</template>
