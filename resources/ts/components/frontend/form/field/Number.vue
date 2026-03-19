<script setup lang="ts">
import { useFormStore } from "@/stores/formStore";
import { useTextInputEventHandlers } from "@/components/composables/useTextInputEventHandlers";
import { computed, onMounted, ref, type Ref } from "vue";

const props = withDefaults(
    defineProps<{
        field: App.Models.FormField;
        disabled?: boolean;
        errors: Array<string>;
        step?: "any" | number;
    }>(),
    {
        disabled: false,
    }
);

const emit = defineEmits<{
    validationErrors: [Array<string>];
}>();

const formStore = useFormStore();

const step = computed(() => {
    const options = formStore.fieldOptions[props.field.id]?.options as any;
    if (!options) {
        return undefined;
    }

    if (options.number_type === "float") {
        const decimals = Number(options.decimal_places);
        if (Number.isInteger(decimals) && decimals >= 0) {
            return 1 / Math.pow(10, decimals);
        }

        return "any";
    }

    const stepValue = options.step;
    if (typeof stepValue === "string" && stepValue !== "") {
        const parsed = Number(stepValue);
        if (!Number.isNaN(parsed)) {
            return parsed;
        }
        return stepValue;
    }

    return undefined;
});

const errorMessages: Ref<Array<string>> = ref([]);

const eventHandlers = useTextInputEventHandlers(
    formStore.fieldOptions[props.field.id],
    errorMessages
);

const validationErrors: Ref<Array<string>> = ref([]);

const validationCheck = () => {
    errorMessages.value = [];

    // Αν το πεδίο είναι κενό τότε δεν έχει νόημα να γίνει validation check
    // if ((formStore.field[props.field.id] ?? "") == "") {
    //     emit("validationErrors", validationErrors.value);
    //     return;
    // }

    const result = formStore.fieldOptions[props.field.id].validationCheck(
        `${formStore.field[props.field.id]}`
    );
    validationErrors.value = result.errorMessages;

    emit("validationErrors", validationErrors.value);
};

onMounted(validationCheck);
</script>

<template>
    <!-- Αριθμός -->
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
            type="number"
            class="form-control"
            :id="`f${field.id}`"
            :class="errors.length ? 'is-invalid' : ''"
            :name="`f${field.id}`"
            :disabled="disabled"
            :step="step"
            :required="field.required ? 'true' : undefined"
            @beforeinput="eventHandlers.onBeforeInput"
            @paste="eventHandlers.onPaste"
            @blur="validationCheck"
            v-model="formStore.field[props.field.id]"
            autocomplete="off"
        />
    </div>
</template>
