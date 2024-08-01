<script setup lang="ts">
import type { FormFieldOptions } from "@/fieldtype";
import { useOptions } from "../../../composables/useOptions";
import { useFormStore } from "@/stores/formStore";
import { useTextInputEventHandlers } from "@/components/composables/useTextInputEventHandlers";
import { ref, type Ref } from "vue";

const props = withDefaults(
    defineProps<{
        field: App.Models.FormField;
        disabled?: boolean;
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

const errorMessages: Ref<Array<string>> = ref([]);

const eventHandlers = useTextInputEventHandlers(options, errorMessages);
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
            :class="error ? 'is-invalid' : ''"
            :name="`f${field.id}`"
            :disabled="disabled"
            :required="field.required ? 'true' : undefined"
            v-model="formStore.field[props.field.id]"
            @keydown="eventHandlers.onKeyDown"
            @keypress="eventHandlers.onKeyPress"
            @paste="eventHandlers.onPaste"
            @blur="errorMessages.splice(0, errorMessages.length)"
            autocomplete="off"
        />
    </div>
</template>
