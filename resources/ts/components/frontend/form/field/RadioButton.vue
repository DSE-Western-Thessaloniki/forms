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
const isChecked = (id: string) => {
    if (props.old_valid && typeof props.old === "string") {
        return props.old == id;
    } else if (!props.old_valid || props.old === undefined) {
        // Κάνε έλεγχο την τιμή που ήρθε από τη βάση
        if (props.data === undefined) {
            return false;
        }

        if (typeof props.data === "string") {
            return props.data == id;
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
                :checked="isChecked(listValue.id)"
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
