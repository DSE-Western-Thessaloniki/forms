<template>
    <div>
        <div class="d-flex justify-content-between">
            <i v-if="!restricted && !no_drag" class="fas fa-align-justify handle mb-2" data-toggle="tooltip"
                data-placement="top" title="Μετακίνηση"></i>
            <i v-if="no_drag" class="mb-2"></i>
            <i v-if="!restricted" class="fas fa-times" data-toggle="tooltip" data-placement="top"
                title="Διαγραφή πεδίου" @click="emitDelete"></i>
        </div>
        <div>
            <div class="row">
                <label for="fieldtitleid" class="col-auto col-form-label">Τίτλος πεδίου:</label>
                <div class="col align-self-center">
                    <editable-text :edittext.sync="title" :fid="'field[' + field_id + '][title]'"
                        :restricted="restricted">
                    </editable-text>
                </div>
            </div>
            <div class="row">
                <label for="fieldtitleid" class="col-auto col-form-label">Τύπος πεδίου:</label>
                <div class="col align-self-center">
                    <select :name="'field[' + field_id + '][type]'" v-model="cbselected" v-if="!restricted">
                        <option v-for="option in options" :value="option.id" :key="option.id">
                            {{ option.value }}
                        </option>
                    </select>
                    <div v-if="restricted" v-text="optionText"></div>
                    <input type="text" v-if="restricted" hidden="true" :name="'field[' + field_id + '][type]'"
                        v-model="cbselected" />
                </div>
            </div>
            <div v-if="cbselected > 1 && cbselected < 5">
                <editable-list :edittext.sync="dataListValues" :restricted="restricted">
                </editable-list>

                <input type="hidden" :name="'field[' + field_id + '][values]'" :value="dataListValues" />
            </div>
            <input type="hidden" :name="'field[' + field_id + '][sort_id]'" :value="sort_id" />
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, withDefaults } from "vue";

export default defineComponent({
    name: "vformfieldcomponent"
})
</script>

<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { FieldType } from "@/fieldtype";

const emit = defineEmits(['update:value', 'deleteField']);

const props = withDefaults(defineProps<{
    id: number,
    value?: string,
    type: FieldType,
    listvalues?: string,
    restricted?: boolean,
    no_drag?: boolean,
    sort_id: number,
}>(), {
    value: "Νέο πεδίο",
    listvalues: ""
}
);

let title = ref(props.value);
let cbselected = ref(props.type);
let options = [
    {
        id: FieldType.Text,
        value: "Πεδίο κειμένου"
    },
    {
        id: FieldType.TextArea,
        value: "Περιοχή κειμένου"
    },
    {
        id: FieldType.RadioButtons,
        value: "Επιλογή ενός από πολλά"
    },
    {
        id: FieldType.CheckBoxes,
        value: "Πολλαπλή επιλογή"
    },
    {
        id: FieldType.SelectionList,
        value: "Λίστα επιλογών"
    },
    /*{
        id: 5,
        value: "Ανέβασμα αρχείου"
    },*/
    {
        id: FieldType.Date,
        value: "Ημερομηνία"
    },
    {
        id: FieldType.Number,
        value: "Αριθμός"
    },
    {
        id: FieldType.Telephone,
        value: "Τηλέφωνο"
    },
    {
        id: FieldType.Email,
        value: "E-mail"
    },
    {
        id: FieldType.WebPage,
        value: "Διεύθυνση ιστοσελίδας"
    },
];
let dataListValues = ref(props.listvalues);
let field_id = props.id;

watch(title, (value) => {
    emit('update:value', value);
});

const emitDelete = () => {
    emit('deleteField', props.id);
}

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
