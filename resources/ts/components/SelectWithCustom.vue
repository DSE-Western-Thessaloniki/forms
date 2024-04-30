<script setup lang="ts">
import { ref } from "vue";

const props = withDefaults(
    defineProps<{
        name: string;
        customLabel?: string;
        customValue?: string;
    }>(),
    {
        customValue: "999",
    }
);

const modelValue = defineModel<string>();

const update = (value: string) => {
    modelValue.value = value;
};
</script>

<template>
    <div>
        <div class="flex-row mb-2">
            <select
                :name="name + '[value]'"
                v-model="modelValue"
                class="form-select"
            >
                <slot />
                <option :value="customValue">
                    {{ customLabel ?? "Προσαρμοσμένη τιμή" }}
                </option>
            </select>
        </div>
        <div class="row mb-2">
            <label
                :for="name + '[custom_value]'"
                class="col-form-label col-auto"
                v-if="modelValue === customValue"
                >Προσαρμοσμένη τιμή:
            </label>
            <div class="col-8">
                <input
                    :name="name + '[custom_value]'"
                    class="form-control"
                    placeholder="Προσαρμοσμένη τιμή. πχ. *.jpg,*.doc"
                    v-if="modelValue === customValue"
                />
            </div>
        </div>
    </div>
</template>
