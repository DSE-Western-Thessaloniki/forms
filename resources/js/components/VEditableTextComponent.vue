<template>
    <div
    class="editable-text-group"
    >
        <span
        v-show="!text.edit"
        @click="toggleEdit(text)"
        class="editable-text-label"
        ><a>{{text.val}}</a><i class="fas fa-pencil-alt editable-text-icon"></i></span>

        <input
            type="text"
            ref="input"
            class="editable-text-input col-12"
            :name="this.fid"
            v-model="text.val"
            v-show="text.edit"
            @keypress="checkKey($event, text)"
            @blur="saveEdit(text)"
        />
        <br />
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.')
        },
        data: function() {
            return {
                text: {
                    val: this.edittext,
                    edit: false
                }
            }
        },
        props: ['edittext', 'fid'],
        methods: {
            toggleEdit: function(text) {
                text.edit = !text.edit;

                // Focus input field
                if(text.edit){
                    this.$nextTick(function() {
                        this.$refs.input.focus();
                    })
                }
            },

            saveEdit: function(text) {
                //save your changes
                if (text.val == '') {
                    text.val = 'New Form'
                }
                this.$emit('update:edittext', text.val);
                this.toggleEdit(text);
            },

            checkKey: function(e, text) {
                if (e.which == 13 || e.keyCode == 13) {
                    e.preventDefault();
                    e.target.blur();
                    return false;
                }
                return true;
            }
        }
    }
</script>
