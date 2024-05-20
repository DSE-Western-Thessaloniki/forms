<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        name: string;
        customOptionLabel?: string;
        customOptionValue?: string;
        customValue?: string;
    }>(),
    {
        customOptionValue: "-1",
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
                <option :value="customOptionValue">
                    {{ customOptionLabel ?? "Προσαρμοσμένη τιμή" }}
                </option>
            </select>
        </div>
        <div class="row mb-2">
            <label
                :for="name + '[custom_value]'"
                class="col-form-label col-auto"
                v-if="modelValue === customOptionValue"
                >Προσαρμοσμένη τιμή:
            </label>
            <div class="col-8">
                <input
                    :name="name + '[custom_value]'"
                    class="form-control"
                    placeholder="Προσαρμοσμένη τιμή. πχ. *.jpg,*.doc"
                    :value="customValue"
                    v-if="modelValue === customOptionValue"
                />
            </div>
        </div>
    </div>
</template>
