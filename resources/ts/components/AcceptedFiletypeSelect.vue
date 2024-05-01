<script setup lang="ts">
import { ref } from "vue";
import SelectWithCustom from "./SelectWithCustom.vue";

const props = defineProps<{
    name: string;
    accepted_filetypes?: Array<App.Models.AcceptedFiletype>;
    selected?: string;
    field_for_filename?: string;
    custom_value?: string;
}>();

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
        <div class="row">
            <label
                :for="name + '[field_for_filename]'"
                class="col-form-label col-auto"
                >Όνομα πεδίου (προαιρετικό):
            </label>
            <div class="col-8">
                <input
                    :name="name + '[field_for_filename]'"
                    class="form-control"
                    placeholder="Πεδίο από το οποίο θα δοθεί το όνομα στο αρχείο κατά τη λήψη"
                    :value="field_for_filename ?? ''"
                />
            </div>
        </div>
    </div>
</template>
