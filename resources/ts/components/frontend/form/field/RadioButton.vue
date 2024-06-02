<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        field: App.Models.FormField;
        data: unknown;
        disabled?: boolean;
        old?: unknown;
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
const isChecked = (old: unknown, data: unknown) => {
    if (Number.isInteger(old)) {
        return old !== 0;
    } else if (old === undefined) {
        // Κάνε έλεγχο την τιμή που ήρθε από τη βάση
        if (data === undefined) {
            return false;
        }

        if (Number.isInteger(data)) {
            return data !== 0;
        }

        console.log(typeof props.data);
        console.warn("RadioButton isChecked: data is not a number");
        return false;
    } else {
        console.log(typeof props.old);
        console.warn("RadioButton isChecked: old is not a number");
        return false;
    }
};
</script>

<template>
    <div>
        <!-- Επιλογή ενός από πολλά -->
        <div
            v-for="listValue in listValues"
            :key="listValue.id"
            class="form-check"
        >
            <input
                type="radio"
                class="form-check-input"
                :class="error ? 'is-invalid' : ''"
                :name="`f${field.id}`"
                :id="`f${field.id}l${listValue.id}`"
                :value="listValue.id"
                :checked="isChecked(old, data)"
                :disabled="disabled"
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
