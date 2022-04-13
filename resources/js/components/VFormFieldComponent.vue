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
                <i
                v-if="no_drag"
                class="mb-2"
                ></i>
                <i
                v-if="!restricted"
                class="fas fa-times"
                data-toggle="tooltip"
                data-placement="top"
                title="Διαγραφή πεδίου"
                @click="emitDelete"></i>
        </div>
        <div>
            <div class="form-row">
                <label for="fieldtitleid" class="col-3 col-form-label">Τίτλος πεδίου:</label>
                <div class="col-9 align-self-center">
                    <editable-text :edittext.sync="title" :fid="'field['+field_id+'][title]'" :restricted="restricted"></editable-text>
                </div>
            </div>
            <div class="form-row">
                <label for="fieldtitleid" class="col-3 col-form-label">Τύπος πεδίου:</label>
                <div class="col-9 align-self-center">
                    <select
                    :name="'field['+field_id+'][type]'"
                    v-model="cbselected"
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
                    <input type="text" v-if="restricted" hidden="true" :name="'field['+field_id+'][type]'" v-model="cbselected"/>
                </div>
            </div>
            <div v-if="this.cbselected > 1 && this.cbselected < 5">
                <editable-list
                :cbselected="cbselected"
                :edittext.sync="dlistvalues"
                :restricted="restricted">
                </editable-list>

                <input type="hidden" :name="'field['+field_id+'][values]'" :value="dlistvalues"/>
            </div>
            <div class="form-row foldable">
                <p>
                    <button class="btn btn-primary btn-sm" type="button" data-toggle="collapse" :data-target="advancedTarget" aria-expanded="false" aria-controls="advanced">
                            <i class='fas fa-tools'></i> Προχωρημένες επιλογές
                    </button>
                </p>
                <div class="collapse col-12" :id="advancedId">
                    <div class="card card-body">
                        <!-- Κριτήρια συμπλήρωσης -->
                        <div class="pb-3">Κριτήρια συμπλήρωσης πεδίου:</div>
                        <div class="px-4">
                            <div class="input-group mb-1" v-if="[0,1].includes(cbselected)">
                                <div class="input-group-prepend">
                                    <div class="form-check col-12 input-group-text">
                                        <input type="checkbox" id="uppercase" :name="'field['+field_id+'][capitals_enabled]'">
                                    </div>
                                </div>
                                <label for="uppercase" class="flex-fill form-check-label input-group-text">Μόνο κεφαλαία</label>
                            </div>
                            <div class="input-group mb-1" v-if="cbselected==7">
                                <div class="input-group-prepend">
                                    <div class="form-check col-12 input-group-text">
                                        <input type="checkbox" id="positive" :name="'field['+field_id+'][positive_enabled]'">
                                    </div>
                                </div>
                                <label for="positive" class="flex-fill form-check-label input-group-text">Μόνο θετικοί αριθμοί</label>
                            </div>
                            <div class="input-group mb-1" v-if="[0,1,7,8,9,10].includes(cbselected)">
                                <div class="input-group-prepend">
                                    <div class="form-check col-12 input-group-text">
                                        <input type="checkbox" id="field_width" v-model="field_width_enabled"  :name="'field['+field_id+'][field_width_enabled]'">
                                        <label for="field_width" class="col-10 form-check-label">Πλάτος πεδίου</label>
                                    </div>
                                </div>
                                <input type="number" class="flex-fill form-control" :disabled="!field_width_enabled" :name="'field['+field_id+'][field_width]'">
                            </div>
                            <div class="input-group mb-1" v-if="[0,1,7,8,9,10].includes(cbselected)">
                                <div class="input-group-prepend">
                                    <div class="form-check col-12 input-group-text">
                                        <input type="checkbox" id="regex" v-model="regex_enabled" :name="'field['+field_id+'][regex_enabled]'">
                                        <label for="regex" class="col-10 form-check-label">Regex</label>
                                    </div>
                                </div>
                                <input type="text" class="flex-fill form-control" :disabled="!regex_enabled" :name="'field['+field_id+'][regex]'">
                            </div>
                        </div>

                        <!-- Κριτήρια εμφάνισης -->
                        <div class="pt-4 pb-2">Εμφάνιση πεδίου όταν:</div>
                        <ul class="px-4">
                            <li class="list-group-item col-12 d-flex" v-for="option in advancedOptionsCriteria" :key="option.id">
                                <select :disabled="option.id==0" :name="'field['+field_id+']['+option.id+'][operator]'">
                                    <option value="and">Και</option>
                                    <option value="or">Ή</option>
                                </select>

                                <select v-model="option.check" :name="'field['+field_id+']['+option.id+'][visible]'">
                                    <option value="always" selected>Πάντα</option>
                                    <option value="when_field_is_active">Είναι ενεργό το πεδίο</option>
                                    <option value="when_value">Η τιμή του πεδίου</option>
                                </select>

                                <select v-if="option.check=='when_field_is_active' || option.check=='when_value'" :name="'field['+field_id+']['+option.id+'][active_field]'">
                                    <option>Πεδίο 1</option>
                                    <option>Πεδίο 2</option>
                                </select>

                                <div v-if="option.check=='when_value'" class="d-flex flex-fill">
                                    <div class="px-1 pt-1">είναι</div>
                                    <select :name="'field['+field_id+']['+option.id+'][value_is]'">
                                        <option value="gt">μεγαλύτερη από</option>
                                        <option value="ge">μεγαλύτερη ή ίση από</option>
                                        <option value="lt">μικρότερη από</option>
                                        <option value="le">μικρότερη ή ίση από</option>
                                        <option value="eq">ίση με</option>
                                        <option value="ne">διαφορετική από</option>
                                    </select>
                                    <input type="text" class="flex-fill" :name="'field['+field_id+']['+option.id+'][value]'">
                                </div>
                                <button class="btn btn-primary btn-sm px-1 mx-1" type="button" @click="addAdvancedOptionsCriteria" v-if="canAddAdvancedOptionsCriteria(option.id)">
                                    <i class='fas fa-plus'></i>
                                </button>
                                <button class="btn btn-danger btn-sm px-1" type="button" @click="removeAdvancedOptionsCriteria" v-if="canRemoveAdvancedOptionsCriteria(option.id)">
                                    <i class='fas fa-minus'></i>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['value', 'id', 'type', 'listvalues', 'restricted', 'no_drag'],
        watch: {
            title: function(value) {
                this.$emit('update:value', value);
            }
        },
        mounted() {
        },
        data: function() {
            return {
                title: this.value ? this.value : "Νέο πεδίο",
                cbselected: this.type,
                options: [
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

                ],
                dlistvalues: this.listvalues,
                field_id: this.id,
                advancedOptionsCriteria: [{id: 0, check: "always"}],
                regex_enabled: false,
                field_width_enabled: false
            }
        },
        methods: {
            emitDelete: function() {
                this.$emit('delfield', this.id);
            },
            addAdvancedOptionsCriteria: function() {
                const i = this.advancedOptionsCriteria.at(-1).id + 1;
                this.advancedOptionsCriteria.push({id: i, check: 0});
            },
            removeAdvancedOptionsCriteria: function() {
                this.advancedOptionsCriteria.splice(-1);
            },
            canAddAdvancedOptionsCriteria: function(id) {
                return this.advancedOptionsCriteria ? id == this.advancedOptionsCriteria.at(-1).id : true;
            },
            canRemoveAdvancedOptionsCriteria: function(id) {
                return this.advancedOptionsCriteria ? id == this.advancedOptionsCriteria.at(-1).id && this.advancedOptionsCriteria.length > 1 : true;
            }

        },
        computed: {
            optionText: function() {
                var text;

                var vueobj = this;
                this.options.forEach(function(item) {
                    if (item.id == vueobj.cbselected) {
                        text = item.value;
                    }
                });
                return text;
            },
            advancedId: function() {
                return 'advanced_f'+this.id;
            },
            advancedTarget: function() {
                return '#advanced_f'+this.id;
            }
        }
    }
</script>
