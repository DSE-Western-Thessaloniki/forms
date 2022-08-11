<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <editable-text :edittext="title" fid="title">
                        </editable-text>
                    </div>

                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="form-row">
                                    <label for="notes" class="col-3 col-form-label">Σημειώσεις:</label>
                                    <div class="col-9 align-self-center">
                                        <textarea id="notes" name="notes" class="col-12" v-model="notes">
                                        </textarea>
                                    </div>
                                </div>
                                <div class="form-row form-check">
                                    <input v-if="restricted" type="checkbox" class="form-check-input"
                                        id="multiple_input" name="multiple_input" value=1 v-model="allow_multiple"
                                        hidden>
                                    <input v-else type="checkbox" class="form-check-input" id="multiple_input"
                                        name="multiple_input" value=1 v-model="allow_multiple">
                                    <label v-if="restricted" class="form-check-label d-none"
                                        for="multiple_input">Πολλαπλή συμπλήρωση στοιχείων φόρμας</label>
                                    <label v-else class="form-check-label" for="multiple_input">Πολλαπλή συμπλήρωση
                                        στοιχείων φόρμας</label>
                                </div>
                            </li>
                            <draggable v-model="fields" handle=".handle" @end="dragEnded">
                                <li class="list-group-item" v-for="field in fields" :key="field.id">
                                    <vform-field-component :value.sync="field.title" :id="field.id" :type="field.type"
                                        :listvalues="field.listvalues" v-on:deleteField="delField" ref='vform_fields'
                                        :restricted="restricted && !field.new_field" :no_drag="restricted">
                                    </vform-field-component>
                                </li>
                            </draggable>
                            <li class="list-group-item">
                                <button type="button" class="btn btn-primary" @click="addField">
                                    <i class="fas fa-plus-circle"></i> Προσθήκη πεδίου
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card mt-5">
                    <div class="card-header">
                        Διαθέσιμη σε
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="form-row">
                                    <label for="categories" class="m-2">Κατηγορίες:</label>
                                    <pillbox class="flex-fill" :value="category_selected_values"
                                        :options="props.categories" name="categories">
                                    </pillbox>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="form-row">
                                    <label for="schools" class="m-2">Σχολικές μονάδες:</label>
                                    <pillbox class="flex-fill" :value="school_selected_values" :options="props.schools"
                                        placeholder="Επιλέξτε σχολική μονάδα" name="schools">
                                    </pillbox>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import draggable from 'vuedraggable';
import { ref } from "vue";

const props = defineProps({
    parse: {
        type: Boolean,
        default: false,
    },
    parsetitle: {
        type: String,
        default: "Νέα φόρμα",
    },
    parsenotes: String,
    parseobj: Array,
    schools: Array,
    categories: Array,
    multiple: Boolean,
    restricted: Boolean,
    category_selected_values: String,
    school_selected_values: String,
});

let vform_fields = ref(null);

let fieldObj = {
    id: 0,
    title: "Νέο πεδίο",
    type: 0,
    validators: [],
    listvalues: "",
    sort_id: 0,
    new_field: false,
}

let title = ref("Νέα φόρμα");
let notes = ref("");
let fields = ref([JSON.parse(JSON.stringify(fieldObj))]);
let cur_id = 0;
let cur_sort_id = 0;
let allow_multiple = ref(props.multiple);

if (props.parse) {
    let max_id = 0
    let max_sort_id = 0

    for (const [key, value] of Object.entries(props.parseobj)) {
        if (max_id < value.id) {
            max_id = value.id
        }
        if (max_sort_id < value.sort_id) {
            max_sort_id = value.sort_id
        }
    }

    title.value = props.parsetitle;
    notes.value = props.parsenotes;
    fields.value = props.parseobj;
    cur_id = max_id;
    cur_sort_id = max_sort_id;
}

const addField = () => {
    cur_id++;
    cur_sort_id++;
    fieldObj.id = cur_id;
    fieldObj.sort_id = cur_sort_id;
    fieldObj.new_field = true;
    fields.value.push(JSON.parse(JSON.stringify(fieldObj)));
}

const delField = (id) => {
    let removeIndex = fields.value.map(function (item) { return item.id; })
        .indexOf(id);

    ~removeIndex && fields.value.splice(removeIndex, 1);
};

const dragEnded = (event) => {
    [vform_fields.value[event.oldIndex].field_id,
    vform_fields.value[event.newIndex].field_id] = [vform_fields.value[event.newIndex].field_id,
    vform_fields.value[event.oldIndex].field_id];
};

</script>
