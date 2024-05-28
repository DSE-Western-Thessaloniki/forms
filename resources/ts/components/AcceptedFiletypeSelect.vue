<script setup lang="ts">
import { ref } from "vue";
import SelectWithCustom from "./SelectWithCustom.vue";

const props = withDefaults(
    defineProps<{
        name: string;
        accepted_filetypes?: Array<App.Models.AcceptedFiletype>;
        selected?: string;
        field_for_filename?: string;
        custom_value?: string;
        fields: Array<App.Models.FormField>;
    }>(),
    {
        field_for_filename: "-1",
    }
);

const selected = ref(
    typeof props.accepted_filetypes === "undefined"
        ? ""
        : typeof props.selected === "undefined"
        ? `${props.accepted_filetypes[0].id}`
        : props.selected
);
const fieldName = ref("");
</script>

<template>
    <div>
        <SelectWithCustom
            :name="name"
            v-model="selected"
            :customValue="custom_value"
        >
            <option
                v-for="filetype in accepted_filetypes"
                :value="`${filetype.id}`"
                :key="`${filetype.id}`"
            >
                {{ filetype.description }} ({{ filetype.extension }})
            </option>
        </SelectWithCustom>
        <div class="row flex-nowrap">
            <label
                :for="name + '[field_for_filename]'"
                class="col-form-label col-auto"
                >Όνομα πεδίου (προαιρετικό):
            </label>
            <div class="flex-fill">
                <select
                    :name="name + '[field_for_filename]'"
                    class="form-select flex-fill"
                    :v-model="field_for_filename"
                >
                    <option value="-1">Παρακαλώ επιλέξτε</option>
                    <option
                        v-for="field in fields"
                        :key="field.id"
                        :value="field.id"
                    >
                        {{ field.title }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</template>
