<template>
    <div class="vpillbox__template">
        <div class="vpillbox__component form-group">
            <div class="vpillbox__vpills">
                <span v-for="vpill in vpills" :key="vpill.id" :id="vpill.id" class="badge badge-primary m-1 pl-2">{{ vpill.value }}
                    <button type="button" class="btn btn-primary mx-1" @click="removePill(vpill.id)">
                        <i class="fas fa-times"></i>
                    </button>
                </span>
            </div>

            <select class="form-control" @change="optionChanged">
                <option value="-1" disabled selected>{{ this.placeholder ? this.placeholder : "Επιλέξτε κατηγορία/ες" }}</option>
                <option v-for="option in this.options"
                        :key="option.id"
                        :value="option.id">
                            {{ option.name }}
                </option>
            </select>
            <input type="text" :name="this.name" hidden v-model="selectedOptions"/>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.');
            var vueobj = this;
            this.$nextTick(() => {
                if (typeof vueobj.value == "string" || vueobj.value instanceof String) {
                    var values = vueobj.value.split(',');
                    values.forEach(val => vueobj.addPill(val));
                }
                else if (typeof vueobj.value == "number" || vueobj.value instanceof Number) {
                    vueobj.addPill(vueobj.value.toString());
                }
            });
        },
        data: function() {
            return {
                optionsVisible: false,
                selectedOptions: [],
                vpills : [],
            }
        },
        props: {
            options: Array,
            value: [String, Number],
            name: String,
            placeholder: String,
        },
        methods: {
            addPill: function(value) {
                this.selectedOptions.push(value);
                var name = "";
                this.options.forEach(option => {
                    if (option.id == value)
                        name = option.name;
                });
                this.vpills.push({id: value, value: name});
            },
            optionChanged: function(event) {
                if (!this.selectedOptions.includes(event.target.value)) {
                    this.addPill(event.target.value);
                }
                event.target.value = -1;
            },
            removePill: function(option) {
                this.vpills = this.vpills.filter(pill => pill.id != option)
                this.selectedOptions = this.selectedOptions.filter(op => op != option);
            },
        }
    }
</script>
