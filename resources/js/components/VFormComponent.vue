<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <editable-text edittext="New Form" fid="title">
                        </editable-text>
                    </div>

                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="form-row">
                                    <label for="notes" class="col-3 col-form-label">Notes:</label>
                                    <div class="col-9 align-self-center">
                                        <textarea id="notes" name="notes" class="col-12">
                                        </textarea>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item" v-for="field in fields" :key="field.id">
                                <vform-field-component
                                :value.sync="field.title"
                                :id="field.id"
                                v-on:delfield="delField"
                                ></vform-field-component>
                            </li>
                            <li class="list-group-item">
                                <button
                                type="button"
                                class="btn btn-primary"
                                @click="addField"
                                >
                                    <i class="fas fa-plus-circle"></i>__('Add field')
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
    var fieldObj = {
        id: 0,
        title: "New field",
        type: "integer",
        validators: [],
    }

    export default {
        mounted() {
            console.log('Component mounted.')
        },
        data: function() {
            return {
                title: "New form",
                fields: [JSON.parse( JSON.stringify( fieldObj ) )]
            }
        },
        methods: {
            addField: function() {
                fieldObj.id = fieldObj.id + 1;
                this.fields.push(JSON.parse( JSON.stringify( fieldObj ) ));
            },

            delField: function(id) {
                var removeIndex = this.fields.map(function(item) { return item.id; })
                       .indexOf(id);

                ~removeIndex && this.fields.splice(removeIndex, 1);
            }
        }
    }
</script>
