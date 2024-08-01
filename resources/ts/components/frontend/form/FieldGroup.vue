<script setup lang="ts">
import { FieldType, type FormFieldOptions } from "@/fieldtype";
import Field from "./Field.vue";
import { useOptions } from "@/components/composables/useOptions";

const props = defineProps<{
    field: App.Models.FormField;
    disabled: boolean;
    error: string;
    accept: string;
    route: string;
}>();

const fieldOptions: FormFieldOptions = JSON.parse(props.field.options);

const options = useOptions(fieldOptions, true);
</script>

<template>
    <div
        class="form-group row mb-3"
        :name="`f${field.id}-group`"
        v-if="options.fieldVisible.value"
    >
        <label :for="`f${field.id}`" class="col-md-3 col-form-label">
            {{ field.title }}
            <span v-if="field.required" class="text-danger">*</span>
        </label>
        <div v-if="field.type === FieldType.Number" class="col-md-3">
            <Field :field :disabled :error />
        </div>
        <div v-else class="col-md-9">
            <Field :field :disabled :error :accept :route />
            <div v-if="error" class="text-danger small">{{ error }}</div>
        </div>
    </div>
</template>
