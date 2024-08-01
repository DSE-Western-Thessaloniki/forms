<template>
    <div>
        <div class="d-flex justify-content-between">
            <i
                v-if="!restricted && !no_drag"
                class="fas fa-align-justify handle mb-2"
                data-toggle="tooltip"
                data-placement="top"
                title="Μετακίνηση"
            ></i>
            <i v-if="no_drag" class="mb-2"></i>
            <i
                v-if="!restricted && !single_item"
                class="fas fa-times"
                data-toggle="tooltip"
                data-placement="top"
                test-data-id="closeButton"
                title="Διαγραφή πεδίου"
                @click="emitDelete"
            ></i>
        </div>
        <div>
            <div class="row">
                <label class="col-auto col-form-label">Τίτλος πεδίου:</label>
                <div class="col align-self-center">
                    <editable-text
                        v-model:edittext="title"
                        :fid="'field[' + field_id + '][title]'"
                        :restricted="restricted"
                        test-data-id="editableText"
                    >
                    </editable-text>
                </div>
            </div>
            <div class="row">
                <label class="col-auto col-form-label">
                    Υποχρεωτικό πεδίο:
                </label>
                <div class="col align-self-center">
                    <input
                        type="hidden"
                        :name="'field[' + field_id + '][required]'"
                        v-model="is_required"
                    />
                    <div
                        v-if="restricted"
                        v-text="is_required ? 'Ναι' : 'Όχι'"
                    ></div>
                    <div v-if="!restricted">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            value="1"
                            v-model="is_required"
                        />
                    </div>
                </div>
            </div>
            <div class="row">
                <label class="col-auto col-form-label">Τύπος πεδίου:</label>
                <div class="col align-self-center">
                    <select
                        :name="'field[' + field_id + '][type]'"
                        v-model="cbselected"
                        class="form-select"
                        v-if="!restricted"
                    >
                        <option
                            v-for="option in options"
                            :value="option.id"
                            :key="option.id"
                        >
                            {{ option.value }}
                        </option>
                    </select>
                    <div v-if="restricted" v-text="optionText"></div>
                    <input
                        type="text"
                        v-if="restricted"
                        hidden="true"
                        :name="'field[' + field_id + '][type]'"
                        v-model="cbselected"
                    />
                </div>
            </div>
            <div v-if="cbselected === FieldType.File">
                <div class="row my-2">
                    <label
                        class="col-auto col-form-label"
                        :for="'field[' + field_id + '][filetype]'"
                    >
                        Αποδεκτά αρχεία:
                    </label>
                    <div class="col align-self-center">
                        <AcceptedFiletypeSelect
                            :name="'field[' + field_id + '][options][filetype]'"
                            :accepted_filetypes="accepted_filetypes"
                            :selected="field_options?.filetype?.value"
                            :field_for_filename="
                                field_options?.filetype?.field_for_filename ??
                                ''
                            "
                            :custom_value="
                                field_options?.filetype?.custom_value ?? ''
                            "
                            :fields
                        />
                    </div>
                </div>
            </div>
            <div v-if="cbselected > 1 && cbselected < 5">
                <editable-list
                    v-model:edittext="dataListValues"
                    :restricted="restricted"
                    test-data-id="editableList"
                    class="mt-3"
                >
                </editable-list>

                <input
                    type="hidden"
                    :name="'field[' + field_id + '][values]'"
                    :value="dataListValues"
                />
            </div>
            <div v-if="cbselected === FieldType.List" class="row">
                <label class="col-auto col-form-label">Όνομα λίστας:</label>
                <div class="col align-self-center">
                    <select
                        :name="'field[' + field_id + '][selection_list]'"
                        v-model="selection_list_selected"
                        v-if="!restricted"
                    >
                        <option
                            v-for="selection_list in selection_lists"
                            :value="selection_list.id"
                            :key="selection_list.id"
                        >
                            {{ selection_list.name }}
                        </option>
                    </select>
                </div>
            </div>
            <input
                type="hidden"
                :name="'field[' + field_id + '][sort_id]'"
                :value="sort_id"
            />

            <FormFieldAdvancedOptions
                :id
                :field_id
                :cbselected
                :fields
                :field_options="createFormFieldOptions(field_options)"
                class="form-row foldable mt-3"
            />
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import AcceptedFiletypeSelect from "./AcceptedFiletypeSelect.vue";

export default defineComponent({
    name: "vformfieldcomponent",
});
</script>

<script setup lang="ts">
import { ref, watch, computed } from "vue";
import {
    FieldType,
    FieldTypeOptions,
    type FormFieldOptions,
    createFormFieldOptions,
} from "@/fieldtype";
import FormFieldAdvancedOptions from "@/components/FormFieldAdvancedOptions.vue";

const emit = defineEmits(["update:value", "deleteField"]);

const props = withDefaults(
    defineProps<{
        id: number;
        value?: string;
        type: FieldType;
        listvalues?: string;
        restricted?: boolean;
        no_drag?: boolean;
        sort_id: number;
        required?: boolean;
        selection_lists: Array<Pick<App.Models.SelectionList, "id" | "name">>;
        single_item: boolean;
        field_options?: FormFieldOptions;
        accepted_filetypes?: Array<App.Models.AcceptedFiletype>;
        fields: Array<App.Models.FormField>;
    }>(),
    {
        value: "Νέο πεδίο",
        listvalues: "",
        required: true,
    }
);

const title = ref(props.value);
const cbselected = ref(props.type);
const selection_list_selected = ref(
    props.selection_lists.length ? props.selection_lists[0].id : 0
);
const options = FieldTypeOptions;
const dataListValues = ref(props.listvalues);
const field_id = props.id;
const is_required = ref(props.required);

watch(title, (value) => {
    emit("update:value", value);
});

const emitDelete = () => {
    emit("deleteField", props.id);
};

const optionText = computed(() => {
    let text;

    options.forEach(function (item) {
        if (item.id == cbselected.value) {
            text = item.value;
        }
    });
    return text;
});
</script>
