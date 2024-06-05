<script setup lang="ts">
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

const emit = defineEmits<{
    change: [value: string];
}>();

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
    const oldSelected = selected(props.old);
    if (props.old_valid && props.old !== undefined) {
        return id in oldSelected;
    } else if (!props.old_valid || props.old === undefined) {
        // Κάνε έλεγχο την τιμή που ήρθε από τη βάση
        if (props.data === undefined) {
            return false;
        }

        const dataSelected = selected(props.data);
        return id in dataSelected;
    }
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

const stateChanged = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const id = parseInt(input.id.replace(`f${props.field.id}l`, ""));
    const checked = input.checked;
    state[id] = checked ? input.value : null;

    const newValues: Array<string> = [];
    Object.keys(state).forEach((key: string) => {
        const keyValue = state[key];
        if (keyValue !== null) {
            newValues.push(keyValue);
        }
    });

    emit("change", JSON.stringify(newValues));
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
