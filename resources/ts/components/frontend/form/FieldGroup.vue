<script setup lang="ts">
import { FieldType } from "@/fieldtype";
import Field from "./Field.vue";
import { Ref } from "vue";

const props = defineProps<{
    field: App.Models.FormField;
    old: unknown;
    old_valid: boolean;
    data: unknown;
    disabled: boolean;
    error: string;
    accept: string;
    route: string;
    field_values: Record<number, Ref<String>>;
}>();

const updateValue = (value: string) => {
    props.field_values[props.field.id].value = value;
};
</script>

<template>
    <div class="form-group row mb-3" :name="`f${field.id}-group`">
        <label :for="`f${field.id}`" class="col-md-3 col-form-label">
            {{ field.title }}
            <span v-if="field.required" class="text-danger">*</span>
        </label>
        <div v-if="field.type === FieldType.Number" class="col-md-3">
            <Field :field :old :old_valid :data :disabled :error />
        </div>
        <div v-else class="col-md-9">
            <Field
                :field
                :old
                :old_valid
                :data
                :disabled
                :error
                :accept
                :route
                @change="updateValue"
            />
            <div v-if="error" class="text-danger small">{{ error }}</div>
        </div>
    </div>
</template>
