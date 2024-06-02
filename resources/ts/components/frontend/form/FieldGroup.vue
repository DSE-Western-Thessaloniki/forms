<script setup lang="ts">
import { FieldType } from "@/fieldtype";
import Field from "./Field.vue";

const props = defineProps<{
    field: App.Models.FormField;
    data: unknown;
    disabled: boolean;
    error: string;
    accept: string;
    route: string;
}>();
</script>

<template>
    <div class="form-group row mb-3" :name="`f${field.id}-group`">
        <label :for="`f${field.id}`" class="col-md-3 col-form-label">
            {{ field.title }}
            <span v-if="field.required" class="text-danger">*</span>
        </label>
        <div v-if="field.type === FieldType.Number" class="col-md-3">
            <Field :field="field" :data="data" :disabled="disabled" :error />
        </div>
        <div v-else class="col-md-9">
            <Field
                :field="field"
                :data="data"
                :disabled="disabled"
                :error
                :accept
                :route
            />
        </div>
    </div>
</template>
