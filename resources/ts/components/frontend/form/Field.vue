<script setup lang="ts">
import { FieldType } from "@/fieldtype";
import { defineAsyncComponent, onUnmounted, ref, type Ref } from "vue";

const props = withDefaults(
    defineProps<{
        field: App.Models.FormField;
        disabled?: boolean;
        errors: Array<string>;
        accept?: string;
        route?: string;
    }>(),
    {
        disabled: false,
    }
);

const emit = defineEmits<{
    validationErrors: [Array<string>];
    clearError: [];
}>();

const f = new Map<FieldType, any>([
    [
        FieldType.Text,
        defineAsyncComponent(
            () => import("@/components/frontend/form/field/Text.vue")
        ),
    ],
    [
        FieldType.TextArea,
        defineAsyncComponent(
            () => import("@/components/frontend/form/field/TextArea.vue")
        ),
    ],
    [
        FieldType.RadioButtons,
        defineAsyncComponent(
            () => import("@/components/frontend/form/field/RadioButton.vue")
        ),
    ],
    [
        FieldType.CheckBoxes,
        defineAsyncComponent(
            () => import("@/components/frontend/form/field/CheckBox.vue")
        ),
    ],
    [
        FieldType.SelectionList,
        defineAsyncComponent(
            () => import("@/components/frontend/form/field/Select.vue")
        ),
    ],
    [
        FieldType.File,
        defineAsyncComponent(
            () => import("@/components/frontend/form/field/File.vue")
        ),
    ],
    [
        FieldType.Date,
        defineAsyncComponent(
            () => import("@/components/frontend/form/field/Date.vue")
        ),
    ],
    [
        FieldType.Number,
        defineAsyncComponent(
            () => import("@/components/frontend/form/field/Number.vue")
        ),
    ],
    [
        FieldType.Telephone,
        defineAsyncComponent(
            () => import("@/components/frontend/form/field/Telephone.vue")
        ),
    ],
    [
        FieldType.Email,
        defineAsyncComponent(
            () => import("@/components/frontend/form/field/Email.vue")
        ),
    ],
    [
        FieldType.WebPage,
        defineAsyncComponent(
            () => import("@/components/frontend/form/field/Url.vue")
        ),
    ],
]);

const validationErrors: Ref<Array<string>> = ref([]);
const setValidationErrors = (messages: Array<string>) => {
    console.log("Field.setValidationErrors:", messages);
    validationErrors.value = messages;
    emit("validationErrors", messages);
};
</script>

<template>
    <component
        :is="f.get(field.type)"
        :field="field"
        :disabled="disabled"
        :errors
        :accept
        :route
        :class="validationErrors ? 'validation-error' : ''"
        @validationErrors="setValidationErrors"
        @clearError="emit('clearError')"
    />
</template>
