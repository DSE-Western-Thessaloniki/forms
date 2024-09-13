<script setup lang="ts">
import {
    FieldType,
    type FormFieldOptions,
    type FormFieldOptionsShowCriteria,
} from "@/fieldtype";
import { type Ref, computed, ref } from "vue";

const props = defineProps<{
    id: number;
    field_id: number;
    cbselected: FieldType;
    fields: Array<App.Models.FormField>;
    field_options: Required<FormFieldOptions>;
}>();

const fieldOptions = ref(props.field_options);

const parseAdvancedOptionsCriteria = () => {
    if (props.field_options.show_when.length > 0) {
        return ref(
            props.field_options.show_when.map((criteria, index) => {
                return {
                    id: index,
                    check: criteria.visible,
                    active_field: criteria.active_field ?? "",
                    operator: criteria.operator ?? "and",
                    value: criteria.value ?? "",
                    value_is: criteria.value_is ?? "",
                };
            })
        );
    } else {
        return ref<
            {
                id: number;
                check: FormFieldOptionsShowCriteria["visible"];
                active_field: string;
                operator: string;
                value: string;
                value_is: string;
            }[]
        >([
            {
                id: 0,
                check: "always",
                active_field: "",
                operator: "",
                value: "",
                value_is: "",
            },
        ]);
    }
};

const advancedOptionsCriteria: Ref<
    {
        id: number;
        check: FormFieldOptionsShowCriteria["visible"];
        active_field: string;
        operator: string;
        value: string;
        value_is: string;
    }[]
> = parseAdvancedOptionsCriteria();

const addAdvancedOptionsCriteria = () => {
    const i = advancedOptionsCriteria.value.at(-1)!.id + 1;
    advancedOptionsCriteria.value.push({
        id: i,
        check: "always",
        active_field: "",
        operator: "",
        value: "",
        value_is: "",
    });
};

const removeAdvancedOptionsCriteria = () => {
    advancedOptionsCriteria.value.splice(-1);
};

const canAddAdvancedOptionsCriteria = (id: number) => {
    return advancedOptionsCriteria
        ? id == advancedOptionsCriteria.value.at(-1)!.id
        : true;
};

const canRemoveAdvancedOptionsCriteria = (id: number) => {
    return advancedOptionsCriteria
        ? id == advancedOptionsCriteria.value.at(-1)!.id &&
              advancedOptionsCriteria.value.length > 1
        : true;
};

const advancedId = computed(function () {
    return "advanced_f" + props.id;
});
const advancedTarget = computed(function () {
    return "#advanced_f" + props.id;
});
</script>

<template>
    <div>
        <div class="d-flex flex-row-reverse">
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
        </div>
        <div class="collapse col-12" :id="advancedId">
            <div class="card card-body bg-primary-subtle text-primary-emphasis">
                <!-- Κριτήρια συμπλήρωσης -->
                <div v-if="cbselected !== FieldType.File" class="pb-4">
                    <div class="pb-3">Κριτήρια συμπλήρωσης πεδίου:</div>
                    <div class="px-4">
                        Έλεγχοι κατά τη πληκτρολόγηση:
                        <div
                            class="input-group mb-1"
                            v-if="
                                [FieldType.Text, FieldType.TextArea].includes(
                                    cbselected
                                )
                            "
                        >
                            <div class="input-group-text">
                                <input
                                    class="form-check-input mt-0"
                                    type="checkbox"
                                    id="uppercase"
                                    value="true"
                                    :name="
                                        'field[' +
                                        field_id +
                                        '][options][capitals_enabled]'
                                    "
                                    v-model="fieldOptions.capitals_enabled"
                                />
                            </div>
                            <label for="uppercase" class="form-control"
                                >Μόνο κεφαλαία</label
                            >
                        </div>

                        <div
                            class="input-group mb-1"
                            v-if="
                                [FieldType.Text, FieldType.TextArea].includes(
                                    cbselected
                                )
                            "
                        >
                            <div class="input-group-text">
                                <input
                                    class="form-check-input mt-0"
                                    type="checkbox"
                                    id="greek"
                                    value="true"
                                    :name="
                                        'field[' +
                                        field_id +
                                        '][options][greek_enabled]'
                                    "
                                    v-model="fieldOptions.greek_enabled"
                                />
                            </div>
                            <label for="greek" class="form-control"
                                >Μόνο Ελληνικά</label
                            >
                        </div>

                        <div
                            class="input-group mb-1"
                            v-if="cbselected == FieldType.Number"
                        >
                            <div class="input-group-text">
                                <input
                                    class="form-check-input mt-0"
                                    type="checkbox"
                                    id="positive"
                                    value="true"
                                    :name="
                                        'field[' +
                                        field_id +
                                        '][options][positive]'
                                    "
                                    v-model="fieldOptions.positive"
                                />
                            </div>
                            <label for="positive" class="form-control"
                                >Μόνο θετικοί αριθμοί</label
                            >
                        </div>
                        <div
                            class="input-group mb-1"
                            v-if="
                                [
                                    FieldType.Text,
                                    FieldType.TextArea,
                                    FieldType.RadioButtons,
                                    FieldType.CheckBoxes,
                                    FieldType.SelectionList,
                                    FieldType.Date,
                                    FieldType.Number,
                                    FieldType.Telephone,
                                    FieldType.Email,
                                    FieldType.WebPage,
                                    FieldType.List,
                                ].includes(cbselected)
                            "
                        >
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="field_width"
                                        v-model="
                                            fieldOptions.field_width_enabled
                                        "
                                        value="true"
                                        :name="
                                            'field[' +
                                            field_id +
                                            '][options][field_width_enabled]'
                                        "
                                    />
                                </div>
                                <label
                                    for="field_width"
                                    class="form-control flex-shrink-1"
                                    >Πλάτος πεδίου</label
                                >
                                <input
                                    type="number"
                                    class="form-control w-auto"
                                    :disabled="
                                        !fieldOptions.field_width_enabled
                                    "
                                    :name="
                                        'field[' +
                                        field_id +
                                        '][options][field_width]'
                                    "
                                    v-model="fieldOptions.field_width"
                                />
                            </div>
                        </div>
                        <div class="mt-4">Έλεγχοι μετά τη πληκτρολόγηση:</div>
                        <div
                            class="input-group mb-1"
                            v-if="
                                [
                                    FieldType.Text,
                                    FieldType.TextArea,
                                    FieldType.RadioButtons,
                                    FieldType.CheckBoxes,
                                    FieldType.SelectionList,
                                    FieldType.Date,
                                    FieldType.Number,
                                    FieldType.Telephone,
                                    FieldType.Email,
                                    FieldType.WebPage,
                                    FieldType.List,
                                ].includes(cbselected)
                            "
                        >
                            <div class="input-group-text">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="regex"
                                    value="true"
                                    v-model="fieldOptions.regex_enabled"
                                    :name="
                                        'field[' +
                                        field_id +
                                        '][options][regex_enabled]'
                                    "
                                />
                            </div>
                            <label
                                for="regex"
                                class="form-control flex-shrink-1 align-content-center"
                                >Regex</label
                            >
                            <input
                                type="text"
                                class="form-control w-auto"
                                :disabled="!fieldOptions.regex_enabled"
                                :name="
                                    'field[' + field_id + '][options][regex]'
                                "
                                v-model="fieldOptions.regex"
                            />

                            <label
                                for="regex_description"
                                class="form-control flex-shrink-1 align-content-center"
                                >Περιγραφή Regex για τους χρήστες</label
                            >
                            <input
                                type="text"
                                id="regex_description"
                                class="form-control w-auto"
                                :disabled="!fieldOptions.regex_enabled"
                                :name="
                                    'field[' +
                                    field_id +
                                    '][options][regex_description]'
                                "
                                placeholder="Επιτρέπονται οι χαρακτήρες..."
                                v-model="fieldOptions.regex_description"
                            />
                        </div>
                    </div>
                </div>

                <!-- Κριτήρια εμφάνισης -->
                <div class="pb-2">Εμφάνιση πεδίου όταν:</div>
                <div class="px-4">
                    <ul class="list-group">
                        <li
                            class="row list-group-item d-flex"
                            v-for="option in advancedOptionsCriteria"
                            :key="option.id"
                        >
                            <select
                                class="form-select col-auto w-auto"
                                :disabled="option.id == 0"
                                :name="
                                    'field[' +
                                    field_id +
                                    '][options][show_when][' +
                                    option.id +
                                    '][operator]'
                                "
                            >
                                <option value="and">Και</option>
                                <option value="or">Ή</option>
                            </select>
                            <select
                                class="form-select col-auto w-auto"
                                v-model="option.check"
                                :name="
                                    'field[' +
                                    field_id +
                                    '][options][show_when][' +
                                    option.id +
                                    '][visible]'
                                "
                            >
                                <option value="always" selected>Πάντα</option>
                                <option value="when_field_is_active">
                                    Είναι ενεργό το πεδίο
                                </option>
                                <option value="when_value">
                                    Η τιμή του πεδίου
                                </option>
                            </select>
                            <select
                                v-if="
                                    option.check == 'when_field_is_active' ||
                                    option.check == 'when_value'
                                "
                                class="form-select col-auto w-auto"
                                :name="
                                    'field[' +
                                    field_id +
                                    '][options][show_when][' +
                                    option.id +
                                    '][active_field]'
                                "
                                v-model="option.active_field"
                            >
                                <option
                                    v-for="field in fields"
                                    :value="`${field.id}`"
                                    :key="field.id"
                                >
                                    {{ field.title }}
                                </option>
                            </select>
                            <div
                                v-if="option.check == 'when_value'"
                                class="col-auto row"
                            >
                                <div
                                    class="px-1 pt-1 col-auto align-content-center"
                                >
                                    είναι
                                </div>
                                <select
                                    class="form-select col-auto w-auto"
                                    :name="
                                        'field[' +
                                        field_id +
                                        '][options][show_when][' +
                                        option.id +
                                        '][value_is]'
                                    "
                                    v-model="option.value_is"
                                >
                                    <option value="gt">μεγαλύτερη από</option>
                                    <option value="ge">
                                        μεγαλύτερη ή ίση από
                                    </option>
                                    <option value="lt">μικρότερη από</option>
                                    <option value="le">
                                        μικρότερη ή ίση από
                                    </option>
                                    <option value="eq">ίση με</option>
                                    <option value="ne">διαφορετική από</option>
                                </select>
                                <input
                                    type="text"
                                    class="form-control col-auto w-auto"
                                    :name="
                                        'field[' +
                                        field_id +
                                        '][options][show_when][' +
                                        option.id +
                                        '][value]'
                                    "
                                    v-model="option.value"
                                />
                            </div>
                            <button
                                class="btn btn-primary btn-sm px-1 mx-1 col-auto"
                                type="button"
                                @click="addAdvancedOptionsCriteria"
                                v-if="canAddAdvancedOptionsCriteria(option.id)"
                            >
                                <i class="fas fa-plus"></i>
                            </button>
                            <button
                                class="btn btn-danger btn-sm px-1 col-auto"
                                type="button"
                                @click="removeAdvancedOptionsCriteria"
                                v-if="
                                    canRemoveAdvancedOptionsCriteria(option.id)
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
</template>
