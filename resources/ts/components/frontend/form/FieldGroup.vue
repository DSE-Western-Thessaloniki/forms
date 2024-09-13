<script setup lang="ts">
import { FieldType, type FormFieldOptions } from "@/fieldtype";
import Field from "./Field.vue";
import { onDeactivated, onUnmounted, ref, watch, type Ref } from "vue";
import { useFormStore } from "@/stores/formStore";
import { useOptions } from "@/components/composables/useOptions";

const props = defineProps<{
    field: App.Models.FormField;
    disabled: boolean;
    errors: Array<string>;
    accept: string;
    route: string;
}>();

const backendErrors = ref(props.errors);

console.log(backendErrors);

const fieldOptions: FormFieldOptions = JSON.parse(props.field.options);

const formStore = useFormStore();

if (
    !Object.keys(formStore.fieldOptions).length ||
    typeof formStore.fieldOptions[props.field.id] === "undefined"
) {
    formStore.fieldOptions[`${props.field.id}`] = useOptions(
        fieldOptions,
        true
    );
}

const emit = defineEmits<{
    validationChanged: [HTMLDivElement | null, number];
    clearValidation: [number];
}>();

const validationErrors: Ref<Array<string>> = ref([]);

const setValidationErrors = (messages: Array<string>) => {
    console.log("FieldGroup.setValidationErrors:", messages);
    validationErrors.value = messages;
    emit("validationChanged", divRef.value, messages.length ? 1 : 0);
};

const divRef: Ref<HTMLDivElement | null> = ref(null);

// Παρακολούθησε αν το πεδίο δεν είναι πλέον ορατό και αφαίρεσε την κατάσταση
// του validation
watch([() => formStore.fieldOptions[props.field.id].fieldVisible], () => {
    console.log(
        "FieldGroup.watch.fieldVisible",
        props.field.id,
        formStore.fieldOptions[props.field.id].fieldVisible
    );
    if (!formStore.fieldOptions[props.field.id].fieldVisible) {
        emit("validationChanged", divRef.value, 0);
        validationErrors.value = [];
    }
});

const clearError = () => {
    backendErrors.value = [];
};
</script>

<template>
    <div
        class="form-group row mb-3"
        :name="`f${field.id}-group`"
        v-if="formStore.fieldOptions[props.field.id].fieldVisible"
        ref="divRef"
    >
        <label :for="`f${field.id}`" class="col-md-3 col-form-label">
            {{ field.title }}
            <span v-if="field.required" class="text-danger">*</span>
        </label>
        <div v-if="field.type === FieldType.Number" class="col-md-3">
            <Field
                :field
                :disabled
                :errors
                @validationErrors="setValidationErrors"
            />
            <div
                v-for="validationError in validationErrors"
                class="text-danger small"
            >
                {{ validationError }}
            </div>
        </div>
        <div v-else class="col-md-9">
            <Field
                :field
                :disabled
                :errors
                :accept
                :route
                @validationErrors="setValidationErrors"
                @clearError="clearError"
            />
            <div v-for="error in backendErrors" class="text-danger small">
                {{ error }}
            </div>
            <div
                v-for="validationError in validationErrors"
                class="text-danger small"
            >
                {{ validationError }}
            </div>
        </div>
    </div>
</template>
