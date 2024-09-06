<script setup lang="ts">
import { useFormStore } from "@/stores/formStore";

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

let listValues = JSON.parse(props.field.listvalues);
if (!Array.isArray(listValues)) {
    listValues = [];
}

const formStore = useFormStore();

const isChecked = (id: string) => {
    return formStore.field[props.field.id] === id;
};

// Κάνε έναν έλεγχο μήπως δεν υπάρχει ήδη τιμή στη βάση και επέλεξε την πρώτη διαθέσιμη
const optionFound = listValues.find((item: { id: string }) => {
    if (
        formStore.field[props.field.id] !== "" &&
        item.id == formStore.field[props.field.id]
    ) {
        return true;
    }
});

if (!optionFound && listValues.length > 0) {
    formStore.field[props.field.id] = listValues[0].id; // Set default value if not found
}
</script>

<template>
    <div>
        <!-- Λίστα επιλογών -->
        <select
            class="form-select"
            :class="error ? 'is-invalid' : ''"
            :id="`f${field.id}`"
            :name="`f${field.id}`"
            :disabled="disabled"
            v-model="formStore.field[props.field.id]"
        >
            <option
                v-for="listValue in listValues"
                :key="listValue.id"
                :value="listValue.id"
                :selected="isChecked(`${listValue.id}`)"
            >
                {{ listValue.value }}
            </option>
        </select>
    </div>
</template>
