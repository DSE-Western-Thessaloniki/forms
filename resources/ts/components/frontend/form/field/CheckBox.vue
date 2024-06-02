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

const selected = (data: unknown) =>
    JSON.parse(typeof data === "string" ? data : "[]");

// TODO: Κάνε έλεγχο αν η τιμή είναι πάντα αριθμός ή μπορεί να μας επιστραφεί και κείμενο
const isChecked = (id: number) => {
    const oldSelected = selected(props.old);
    if (props.old !== undefined) {
        return id in oldSelected;
    } else if (props.old === undefined) {
        // Κάνε έλεγχο την τιμή που ήρθε από τη βάση
        if (props.data === undefined) {
            return false;
        }

        const dataSelected = selected(props.data);
        return id in dataSelected;
    }
    console.log(props.old);
    console.warn("CheckBox isChecked: old is not a number");
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
