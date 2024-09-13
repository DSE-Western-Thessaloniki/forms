<script setup lang="ts">
import { useOptions } from "@/components/composables/useOptions";
import type { FormFieldOptions } from "@/fieldtype";
import { useFormStore } from "@/stores/formStore";
import { ref, type Ref } from "vue";

const props = withDefaults(
    defineProps<{
        field: App.Models.FormField;
        disabled?: boolean;
        errors: Array<string>;
    }>(),
    {
        disabled: false,
    }
);

const emit = defineEmits<{
    validationErrors: [Array<string>];
}>();

const formStore = useFormStore();

const validationErrors: Ref<Array<string>> = ref([]);

const onBlur = () => {
    const result = formStore.fieldOptions[props.field.id].validationCheck(
        formStore.field[props.field.id] ?? ""
    );
    if (!result.result) {
        validationErrors.value = result.errorMessages;
    } else {
        validationErrors.value = [];
    }

    emit("validationErrors", validationErrors.value);
};
</script>

<template>
    <!-- Ημερομηνία -->
    <input
        type="date"
        class="form-control"
        :id="`f${field.id}`"
        :class="errors.length ? 'is-invalid' : ''"
        :name="`f${field.id}`"
        :disabled="disabled"
        :required="field.required ? 'true' : undefined"
        v-model="formStore.field[props.field.id]"
        @blur="onBlur"
        autocomplete="off"
    />
</template>
