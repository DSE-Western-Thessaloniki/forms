<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <editable-text :edittext="title" fid="title" test-data-id="form-title">
                        </editable-text>
                    </div>

                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="row">
                                    <label for="notes" class="col-auto col-form-label">Σημειώσεις:</label>
                                    <div class="col align-self-center">
                                        <textarea id="notes" name="notes" class="col-12" v-model="notes">
                                        </textarea>
                                    </div>
                                </div>
                                <div class="form-check">
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
                            <draggable v-model="fields" handle=".handle" item-key="id" @end="dragEnded">
                                <template
                                    #item="{ element }: { element: App.Models.FormField & { new_field: boolean } }">
                                    <li class="list-group-item" test-data-id="field-item">
                                        <vform-field-component v-model:value="element.title" :id="element.id"
                                            :type="element.type" :listvalues="element.listvalues"
                                            v-on:deleteField="delField" ref='vform_fields'
                                            :restricted="restricted && !element.new_field" :no_drag="restricted"
                                            :sort_id="element.sort_id">
                                        </vform-field-component>
                                    </li>
                                </template>
                            </draggable>
                            <li class="list-group-item">
                                <button type="button" class="btn btn-primary" @click="addField" test-data-id="addField">
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
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="for_teachers" id="radio_for_teachers1"
                                value="1" v-model="for_teachers">
                            <label class="form-check-label" for="radio_for_teachers1">
                                Εκπαιδευτικούς
                            </label>
                        </div>
                        <div class="form-check ms-4" v-if="for_teachers == 1" test-data-id="ul_for_teachers">
                            <input class=" form-check-input" type="checkbox" name="for_all_teachers"
                                id="for_all_teachers" value="1" v-model="for_all_teachers">
                            <label class="form-check-label" for="for_all_teachers">
                                Επέτρεψε είσοδο από όλες τις Διευθύνσεις
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="for_teachers" id="radio_for_teachers2"
                                value="0" v-model="for_teachers">
                            <label class="form-check-label" for="radio_for_teachers2">
                                Σχολικές μονάδες
                            </label>
                        </div>
                        <ul class="list-group list-group-flush" v-if="for_teachers == 0" test-data-id="ul_for_schools">
                            <li class="list-group-item">
                                <div class="row">
                                    <label for="categories" class="m-2">Κατηγορίες:</label>
                                    <pillbox class="flex-fill" :value="category_selected_values"
                                        :options="props.categories" name="categories" test-data-id="category-pillbox">
                                    </pillbox>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="row">
                                    <label for="schools" class="m-2">Σχολικές μονάδες:</label>
                                    <pillbox class="flex-fill" :value="school_selected_values" :options="props.schools"
                                        placeholder="Επιλέξτε σχολική μονάδα" name="schools"
                                        test-data-id="school-pillbox">
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

<script setup lang="ts">
import draggable from 'vuedraggable';
import { ref, withDefaults } from "vue";
import type { Ref } from 'vue';

const props = withDefaults(defineProps<{
    parse?: boolean,
    parsetitle?: string,
    parsenotes?: string,
    parseobj?: Array<App.Models.FormField>,
    schools: Array<App.Models.School>,
    categories: Array<App.Models.SchoolCategory>,
    multiple?: boolean,
    restricted?: boolean,
    category_selected_values?: string,
    school_selected_values?: string,
    for_teachers?: 0 | 1 | "0" | "1",
    for_all_teachers?: boolean,
}>(), {
    parse: false,
    parsetitle: "Νέα φόρμα",
    for_teachers: "0",
    for_all_teachers: false,
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
let fields: Ref<Array<App.Models.FormField>> = ref([JSON.parse(JSON.stringify(fieldObj))]);
let cur_id = 0;
let cur_sort_id = 0;
let allow_multiple = ref(props.multiple);
let for_teachers = ref(props.for_teachers);
let for_all_teachers = ref(props.for_all_teachers);

if (props.parse) {
    let max_id = 0
    let max_sort_id = 0

    if (typeof props.parseobj !== "undefined") {
        for (const [key, value] of Object.entries(props.parseobj)) {
            if (max_id < value.id) {
                max_id = value.id
            }
            if (max_sort_id < value.sort_id) {
                max_sort_id = value.sort_id
            }
        }
    }

    title.value = props.parsetitle;
    notes.value = props.parsenotes ?? '';
    fields.value = props.parseobj ?? Array<App.Models.FormField>();
    cur_id = max_id;
    cur_sort_id = max_sort_id;
}

// Ταξινόμηση των πεδίων βάση του sort_id
fields.value.sort((a, b) => a.sort_id - b.sort_id);

const addField = () => {
    cur_id++;
    cur_sort_id++;
    fieldObj.id = cur_id;
    fieldObj.sort_id = cur_sort_id;
    fieldObj.new_field = true;
    fields.value.push(JSON.parse(JSON.stringify(fieldObj)));
}

const delField = (id: number) => {
    let removeIndex = fields.value.map(function (item) { return item.id; })
        .indexOf(id);

    ~removeIndex && fields.value.splice(removeIndex, 1);
};

const dragEnded = (event: MouseEvent) => {
    let sort_id = 1;
    fields.value.forEach(function (item) {
        item.sort_id = sort_id;
        sort_id++;
    })
};

</script>
