<script setup lang="ts">
import { useFormStore } from "@/stores/formStore";

const props = withDefaults(
    defineProps<{
        field: App.Models.FormField;
        data: unknown;
        disabled?: boolean;
        old: unknown;
        old_valid: boolean;
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

const initialValue = () => {
    if (props.old_valid && typeof props.old === "string") {
        return props.old;
    } else if (!props.old_valid || props.old === undefined) {
        // Κάνε έλεγχο την τιμή που ήρθε από τη βάση
        if (props.data === undefined) {
            return "";
        }

        if (typeof props.data === "string") {
            return props.data;
        }

        console.warn("Select initialValue: data is not a string");
        return "";
    } else {
        console.warn("Select initialValue: old is not a string");
        return "";
    }
};

const formStore = useFormStore();
formStore.field[props.field.id] = initialValue();

const isChecked = (id: string) => {
    return formStore.field[props.field.id] === id;
};
</script>

<template>
    <div>
        <!-- Λίστα επιλογών -->
        <select
            class="form-select"
            :class="error ? 'is-invalid' : ''"
            :id="`f${field.id}`"
            name="`f${field.id}`"
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
