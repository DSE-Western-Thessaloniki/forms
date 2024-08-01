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

let listValues = JSON.parse(props.field.listvalues);
if (!Array.isArray(listValues)) {
    listValues = [];
}

const formStore = useFormStore();

const value = ref(formStore.field[props.field.id]);

watch(value, () => {
    formStore.field[props.field.id] = value.value;
});
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
                :value="`${listValue.id}`"
                :disabled="disabled"
                v-model="value"
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
