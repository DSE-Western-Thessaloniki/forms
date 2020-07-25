<template>
    <div>
        <div class="d-flex justify-content-end">
            <i
            class="fas fa-times"
            data-toggle="tooltip"
            data-placement="top"
            title="Delete field"
            @click="emitDelete"></i>
        </div>
        <div>
            <div class="form-row">
                <label for="fieldtitleid" class="col-3 col-form-label">Field title:</label>
                <div class="col-9 align-self-center">
                    <editable-text :edittext.sync="title" :fid="'field['+this.id+'][title]'"></editable-text>
                </div>
            </div>
            <div class="form-row">
                <label for="fieldtitleid" class="col-3 col-form-label">Field title:</label>
                <div class="col-9 align-self-center">
                    <select
                    id="fieldtype"
                    :name="'field['+this.id+'][type]'"
                    v-model="cbselected"
                    >
                        <option
                        v-for="option in options"
                        :value="option.id"
                        :key="option.id"
                        >
                            {{ option.value }}
                        </option>
                    </select>
                </div>
            </div>
            <div v-if="this.cbselected > 1 && this.cbselected < 5">
                <editable-list
                :cbselected="cbselected"
                :edittext.sync="listvalues">
                </editable-list>

                <input type="hidden" :name="'field['+this.id+'][values]'" :value="listvalues"/>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['value', 'id', 'type', 'listvalues'],
        watch: {
            title: function(value) {
                this.$emit('update:value', value);
            }
        },
        mounted() {
            console.log('Component mounted.')
        },
        data: function() {
            return {
                title: this.value ? this.value : "New field",
                cbselected: this.type,
                options: [
                    {
                        id: 0,
                        value: "Text field"
                    },
                    {
                        id: 1,
                        value: "Text area"
                    },
                    {
                        id: 2,
                        value: "Multiple choice"
                    },
                    {
                        id: 3,
                        value: "Checkboxes"
                    },
                    {
                        id: 4,
                        value: "Drop-down list"
                    },
                    {
                        id: 5,
                        value: "File upload"
                    }
                ],
                listvalues: this.listvalues,
            }
        },
        methods: {
            emitDelete: function() {
                this.$emit('delfield', this.id);
            },
        }
    }
</script>
