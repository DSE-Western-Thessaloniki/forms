<script setup lang="ts">
import type { FormFieldOptions } from "@/fieldtype";
import { useOptions } from "@/components/composables/useOptions";
import { useFormStore } from "@/stores/formStore";
import { useTextInputEventHandlers } from "@/components/composables/useTextInputEventHandlers";
import { onMounted, onUnmounted, ref, type Ref } from "vue";
import { matchesRegex } from "@/validation";

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

const errorMessages: Ref<Array<string>> = ref([]);

const eventHandlers = useTextInputEventHandlers(
    formStore.fieldOptions[props.field.id],
    errorMessages
);

const validationErrors: Ref<Array<string>> = ref([]);

const validationCheck = () => {
    errorMessages.value = [];

    // Αν το πεδίο είναι κενό τότε δεν έχει νόημα να γίνει validation check
    if ((formStore.field[props.field.id] ?? "") == "") {
        emit("validationErrors", validationErrors.value);
        return;
    }

    const result = formStore.fieldOptions[props.field.id].validationCheck(
        formStore.field[props.field.id] ?? ""
    );

    validationErrors.value = result.errorMessages;

    emit("validationErrors", validationErrors.value);
};

onMounted(validationCheck);
</script>

<template>
    <!-- Πεδίο κειμένου -->
    <div class="position-relative">
        <div v-if="errorMessages.length" class="form-tip">
            <div class="px-2 align-items-center d-flex">
                <i class="fas fa-circle-exclamation fa-2x"></i>
                <p class="m-0 h-full p-2">
                    Δεν έγινε εισαγωγή του χαρακτήρα γιατί:
                </p>
            </div>
            <ul class="m-0 pe-3">
                <li v-for="message in errorMessages">{{ message }}</li>
            </ul>
        </div>
        <input
            type="text"
            class="form-control"
            :id="`f${field.id}`"
            :class="
                errors.length || validationErrors.length ? 'is-invalid' : ''
            "
            :name="`f${field.id}`"
            :disabled="disabled"
            :required="field.required ? 'true' : undefined"
            v-model="formStore.field[props.field.id]"
            @keydown="eventHandlers.onKeyDown"
            @keypress="eventHandlers.onKeyPress"
            @paste="eventHandlers.onPaste"
            @blur="validationCheck"
            autocomplete="off"
        />
    </div>
</template>
