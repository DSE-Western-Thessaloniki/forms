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
            <div class="form-row foldable mt-3">
                <p>
                    <button
                        class="btn btn-primary btn-sm"
                        type="button"
                        data-bs-toggle="collapse"
                        :data-bs-target="advancedTarget"
                        aria-expanded="false"
                        aria-controls="advanced"
                    >
                        <i class="fas fa-tools"></i> Προχωρημένες επιλογές
                    </button>
                </p>
                <div class="collapse col-12" :id="advancedId">
                    <div class="card card-body">
                        <!-- Κριτήρια συμπλήρωσης -->
                        <div class="pb-3">Κριτήρια συμπλήρωσης πεδίου:</div>
                        <div class="px-4">
                            <div
                                class="input-group mb-1"
                                v-if="[0, 1].includes(cbselected)"
                            >
                                <div class="input-group-text">
                                    <input
                                        class="form-check-input mt-0"
                                        type="checkbox"
                                        id="uppercase"
                                        :name="
                                            'field[' +
                                            field_id +
                                            '][capitals_enabled]'
                                        "
                                    />
                                </div>
                                <label for="uppercase" class="form-control"
                                    >Μόνο κεφαλαία</label
                                >
                            </div>

                            <div
                                class="input-group mb-1"
                                v-if="cbselected == 7"
                            >
                                <div class="input-group-prepend">
                                    <div
                                        class="form-check col-12 input-group-text"
                                    >
                                        <input
                                            type="checkbox"
                                            id="positive"
                                            :name="
                                                'field[' +
                                                field_id +
                                                '][positive_enabled]'
                                            "
                                        />
                                    </div>
                                </div>
                                <label
                                    for="positive"
                                    class="flex-fill form-check-label input-group-text"
                                    >Μόνο θετικοί αριθμοί</label
                                >
                            </div>
                            <div
                                class="input-group mb-1"
                                v-if="[0, 1, 7, 8, 9, 10].includes(cbselected)"
                            >
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            id="field_width"
                                            v-model="field_width_enabled"
                                            :name="
                                                'field[' +
                                                field_id +
                                                '][field_width_enabled]'
                                            "
                                        />
                                    </div>
                                    <label
                                        for="field_width"
                                        class="form-control"
                                        >Πλάτος πεδίου</label
                                    >
                                    <input
                                        type="number"
                                        class="form-control"
                                        :disabled="!field_width_enabled"
                                        :name="
                                            'field[' +
                                            field_id +
                                            '][field_width]'
                                        "
                                    />
                                </div>
                            </div>
                            <div
                                class="input-group mb-1"
                                v-if="[0, 1, 7, 8, 9, 10].includes(cbselected)"
                            >
                                <div class="input-group-text">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="regex"
                                        v-model="regex_enabled"
                                        :name="
                                            'field[' +
                                            field_id +
                                            '][regex_enabled]'
                                        "
                                    />
                                </div>
                                <label
                                    for="regex"
                                    class="col-10 form-check-label"
                                    >Regex</label
                                >
                                <input
                                    type="text"
                                    class="flex-fill form-control"
                                    :disabled="!regex_enabled"
                                    :name="'field[' + field_id + '][regex]'"
                                />
                            </div>
                        </div>

                        <!-- Κριτήρια εμφάνισης -->
                        <div class="pt-4 pb-2">Εμφάνιση πεδίου όταν:</div>
                        <ul class="px-4">
                            <li
                                class="list-group-item col-12 d-flex"
                                v-for="option in advancedOptionsCriteria"
                                :key="option.id"
                            >
                                <select
                                    :disabled="option.id == 0"
                                    :name="
                                        'field[' +
                                        field_id +
                                        '][' +
                                        option.id +
                                        '][operator]'
                                    "
                                >
                                    <option value="and">Και</option>
                                    <option value="or">Ή</option>
                                </select>

                                <select
                                    v-model="option.check"
                                    :name="
                                        'field[' +
                                        field_id +
                                        '][' +
                                        option.id +
                                        '][visible]'
                                    "
                                >
                                    <option value="always" selected>
                                        Πάντα
                                    </option>
                                    <option value="when_field_is_active">
                                        Είναι ενεργό το πεδίο
                                    </option>
                                    <option value="when_value">
                                        Η τιμή του πεδίου
                                    </option>
                                </select>

                                <select
                                    v-if="
                                        option.check ==
                                            'when_field_is_active' ||
                                        option.check == 'when_value'
                                    "
                                    :name="
                                        'field[' +
                                        field_id +
                                        '][' +
                                        option.id +
                                        '][active_field]'
                                    "
                                >
                                    <option>Πεδίο 1</option>
                                    <option>Πεδίο 2</option>
                                </select>

                                <div
                                    v-if="option.check == 'when_value'"
                                    class="d-flex flex-fill"
                                >
                                    <div class="px-1 pt-1">είναι</div>
                                    <select
                                        :name="
                                            'field[' +
                                            field_id +
                                            '][' +
                                            option.id +
                                            '][value_is]'
                                        "
                                    >
                                        <option value="gt">
                                            μεγαλύτερη από
                                        </option>
                                        <option value="ge">
                                            μεγαλύτερη ή ίση από
                                        </option>
                                        <option value="lt">
                                            μικρότερη από
                                        </option>
                                        <option value="le">
                                            μικρότερη ή ίση από
                                        </option>
                                        <option value="eq">ίση με</option>
                                        <option value="ne">
                                            διαφορετική από
                                        </option>
                                    </select>
                                    <input
                                        type="text"
                                        class="flex-fill"
                                        :name="
                                            'field[' +
                                            field_id +
                                            '][' +
                                            option.id +
                                            '][value]'
                                        "
                                    />
                                </div>
                                <button
                                    class="btn btn-primary btn-sm px-1 mx-1"
                                    type="button"
                                    @click="addAdvancedOptionsCriteria"
                                    v-if="
                                        canAddAdvancedOptionsCriteria(option.id)
                                    "
                                >
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button
                                    class="btn btn-danger btn-sm px-1"
                                    type="button"
                                    @click="removeAdvancedOptionsCriteria"
                                    v-if="
                                        canRemoveAdvancedOptionsCriteria(
                                            option.id
                                        )
                                    "
                                >
                                    <i class="fas fa-minus"></i>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, withDefaults } from "vue";
import AcceptedFiletypeSelect from "./AcceptedFiletypeSelect.vue";

export default defineComponent({
    name: "vformfieldcomponent",
});
</script>

<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { FieldType, FieldTypeOptions, FormFieldOptions } from "@/fieldtype";

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

const advancedId = computed(function () {
    return "advanced_f" + props.id;
});
const advancedTarget = computed(function () {
    return "#advanced_f" + props.id;
});
</script>
