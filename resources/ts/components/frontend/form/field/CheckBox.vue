<script setup lang="ts">
import { useFormStore } from "@/stores/formStore";
import { ref, watch } from "vue";

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

const selected = (data: unknown): Array<string | number> => {
    return JSON.parse(typeof data === "string" && data !== "" ? data : "[]");
};

const formStore = useFormStore();

const isChecked = (id: number) => {
    const dataSelected = selected(formStore.field[props.field.id]);
    return dataSelected.includes(`${id}`) || dataSelected.includes(id);
};

const state = ref(
    listValues
        .map((listValue) => {
            return {
                [`${listValue.id}`]: isChecked(listValue.id),
            };
        })
        .reduce((a, b) => ({ ...a, ...b }), {})
);

const getValuesFromState = () => {
    const newValues: Array<string> = [];
    Object.keys(state.value).forEach((key: string) => {
        const keyValue = state.value[key];
        if (keyValue !== false) {
            newValues.push(listValues[parseInt(key, 10)].id.toString());
        }
    });

    return JSON.stringify(newValues);
};

const stateChanged = () => {
    formStore.field[props.field.id] = getValuesFromState();
};

watch(state, stateChanged, { deep: true });
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
                v-model="state[listValue.id]"
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
