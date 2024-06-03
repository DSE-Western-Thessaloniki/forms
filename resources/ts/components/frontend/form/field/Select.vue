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

let listValues = JSON.parse(props.field.listvalues);
if (!Array.isArray(listValues)) {
    listValues = [];
}

// TODO: Κάνε έλεγχο αν η τιμή είναι πάντα αριθμός ή μπορεί να μας επιστραφεί και κείμενο
const isChecked = (id: number) => {
    if (props.old_valid && typeof props.old === "string") {
        return id === props.old;
    } else if (!props.old_valid || props.old === undefined) {
        // Κάνε έλεγχο την τιμή που ήρθε από τη βάση
        if (props.data === undefined) {
            return false;
        }

        if (typeof props.data === "string") {
            return id === props.data;
        }

        console.warn("Select isChecked: data is not a string");
        return false;
    } else {
        console.warn("Select isChecked: old is not a string");
        return false;
    }
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
        >
            <option
                v-for="listValue in listValues"
                :key="listValue.id"
                :value="listValue.id"
                :selected="isChecked(listValue.id)"
            >
                {{ listValue.value }}
            </option>
        </select>
    </div>
</template>
