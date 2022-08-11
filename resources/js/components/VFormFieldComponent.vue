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
            <div class="form-row">
                <label for="fieldtitleid" class="col-3 col-form-label">Τίτλος πεδίου:</label>
                <div class="col-9 align-self-center">
                    <editable-text :edittext.sync="title" :fid="'field[' + field_id + '][title]'"
                        :restricted="restricted">
                    </editable-text>
                </div>
            </div>
            <div class="form-row">
                <label for="fieldtitleid" class="col-3 col-form-label">Τύπος πεδίου:</label>
                <div class="col-9 align-self-center">
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
                <editable-list :cbselected="cbselected" :edittext.sync="dataListValues" :restricted="restricted">
                </editable-list>

                <input type="hidden" :name="'field[' + field_id + '][values]'" :value="dataListValues" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed } from "vue";

const emit = defineEmits(['update:value', 'deleteField']);

const props = defineProps(
    [
        'value',
        'id',
        'type',
        'listvalues',
        'restricted',
        'no_drag'
    ]
);

let title = ref(props.value ? props.value : "Νέο πεδίο");
let cbselected = ref(props.type);
let options = [
    {
        id: 0,
        value: "Πεδίο κειμένου"
    },
    {
        id: 1,
        value: "Περιοχή κειμένου"
    },
    {
        id: 2,
        value: "Επιλογή ενός από πολλά"
    },
    {
        id: 3,
        value: "Πολλαπλή επιλογή"
    },
    {
        id: 4,
        value: "Λίστα επιλογών"
    },
    /*{
        id: 5,
        value: "Ανέβασμα αρχείου"
    },*/
    {
        id: 6,
        value: "Ημερομηνία"
    },
    {
        id: 7,
        value: "Αριθμός"
    },
    {
        id: 8,
        value: "Τηλέφωνο"
    },
    {
        id: 9,
        value: "E-mail"
    },
    {
        id: 10,
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
        if (item.id == cbselected) {
            text = item.value;
        }
    });
    return text;

});
</script>
