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

        console.log(typeof props.data);
        console.warn("RadioButton initialValue: data is not a number");
        return "";
    } else {
        console.log(typeof props.old);
        console.warn("RadioButton initialValue: old is not a number");
        return "";
    }
};

const formStore = useFormStore();
formStore.field[props.field.id] = initialValue();

const isChecked = (id: string) => {
    return formStore.field[props.field.id] === id;
};

const emitValueChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    formStore.field[props.field.id] = target.value;
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
                @change="emitValueChange"
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
