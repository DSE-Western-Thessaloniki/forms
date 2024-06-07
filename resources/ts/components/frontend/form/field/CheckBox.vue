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

let listValues: Array<{ id: number; value: string }> = JSON.parse(
    props.field.listvalues
);
if (!Array.isArray(listValues)) {
    listValues = [];
}

const selected = (data: unknown): Array<number> =>
    JSON.parse(typeof data === "string" && data !== "" ? data : "[]");

// TODO: Κάνε έλεγχο αν η τιμή είναι πάντα αριθμός ή μπορεί να μας επιστραφεί και κείμενο
const isChecked = (id: number) => {
    const dataSelected = selected(formStore.field[props.field.id]);
    return id in dataSelected;
};

const state = listValues
    .map((listValue) => {
        return {
            [`${listValue.id}`]: isChecked(listValue.id)
                ? listValue.value
                : null,
        };
    })
    .reduce((a, b) => ({ ...a, ...b }), {});

const getValuesFromState = () => {
    const newValues: Array<string> = [];
    Object.keys(state).forEach((key: string) => {
        const keyValue = state[key];
        if (keyValue !== null) {
            newValues.push(keyValue);
        }
    });

    return JSON.stringify(newValues);
};

const formStore = useFormStore();
formStore.field[props.field.id] = JSON.stringify(getValuesFromState());

const stateChanged = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const id = parseInt(input.id.replace(`f${props.field.id}l`, ""));
    const checked = input.checked;
    state[id] = checked ? input.value : null;

    formStore.field[props.field.id] = JSON.stringify(getValuesFromState());
};
</script>

<template>
    <div>
        <!-- Πολλαπλή επιλογή -->
        <div
            v-for="listValue in listValues"
            :key="listValue.id"
            class="form-check"
        >
            <input
                type="checkbox"
                class="form-check-input"
                :class="error ? 'is-invalid' : ''"
                :name="`f${field.id}[]`"
                :id="`f${field.id}l${listValue.id}`"
                :value="listValue.id"
                :checked="isChecked(listValue.id)"
                :disabled="disabled"
                @change="stateChanged"
            />
            <label
                class="form-check-label"
                :for="`f${field.id}l${listValue.id}`"
            >
                {{ listValue.value }}
            </label>
        </div>
    </div>
</template>
