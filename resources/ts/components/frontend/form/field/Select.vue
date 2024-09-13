<script setup lang="ts">
import { useFormStore } from "@/stores/formStore";
import { ref } from "vue";

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
    clearError: [];
}>();

const backendErrors = ref(props.errors);

let listValues = JSON.parse(props.field.listvalues);
if (!Array.isArray(listValues)) {
    listValues = [];
}

const formStore = useFormStore();

// Κάνε έναν έλεγχο μήπως δεν υπάρχει ήδη τιμή στη βάση και επέλεξε την πρώτη διαθέσιμη
const optionFound = listValues.find((item: { id: string }) => {
    if (
        formStore.field[props.field.id] !== "" &&
        item.id == formStore.field[props.field.id]
    ) {
        return true;
    }
});

if (!optionFound) {
    formStore.field[props.field.id] = "-1";
}

const onChange = () => {
    backendErrors.value = [];
    emit("clearError");
};
</script>

<template>
    <div>
        <!-- Λίστα επιλογών -->
        <select
            class="form-select"
            :class="backendErrors.length ? 'is-invalid' : ''"
            :id="`f${field.id}`"
            :name="`f${field.id}`"
            :disabled="disabled"
            v-model="formStore.field[props.field.id]"
            @change="onChange"
        >
            <option value="-1">Παρακαλώ επιλέξτε μια τιμή</option>
            <option
                v-for="listValue in listValues"
                :key="listValue.id"
                :value="listValue.id"
            >
                {{ listValue.value }}
            </option>
        </select>
    </div>
</template>
