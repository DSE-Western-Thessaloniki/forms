<template>
    <div
    class="editable-list-group form-row justify-content-center"
    >
        <div
        class="card col-8"
        >
            <div
            class="card-header"
            v-if="!restricted"
            @click="toggleEdit(list)"
            v-show="!list.edit"
            >
                <div class="d-flex justify-content-end">
                    <i class="fas fa-edit">
                    </i>
                </div>
            </div>
            <div class="card-body" v-show="!list.edit">
                Τιμές:
                <ol>
                    <li v-for="item in validlistitems" :key="item.id">
                        {{ item.value }}
                    </li>
                </ol>
            </div>
            <div
            class="card-header"
            @click="saveEdit(list)"
            v-show="list.edit"
            >
                <div class="d-flex justify-content-end">
                    <i class="fas fa-save">
                    </i>
                </div>
            </div>
            <div
            class="card-body"
            v-show="list.edit"
            >
                <ol>
                    <li v-for="(item, index) in listarray" :key="item.id">
                        <input
                        type="text"
                        v-model="item.value"
                        placeholder="Νέα επιλογή"
                        @keypress="checkKey($event, index)"
                        @blur="checkList(index)"
                        />
                    </li>
                </ol>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            this.$nextTick(function() {
            if (this.listarray.length &&
            (this.listarray[this.listarray.length - 1].value != ''))
                this.listarray.push({
                    id: this.listarray.length,
                    value: ''
                });
            })
        },
        data: function() {
            return {
                list: {
                    val: this.edittext,
                    edit: false
                },
                listarray: this.edittext.length ? JSON.parse(this.edittext) : [],
            }
        },
        props: ['cbselected', 'edittext', 'fid', 'restricted'],
        methods: {
            toggleEdit: function(list) {
                list.edit = !list.edit;
                if (!this.listarray.length) {
                    this.listarray.push({
                        id: 0,
                        value: ""
                    })
                }

                // Focus input field
                // if(list.edit){
                //     this.$nextTick(function() {
                //         this.$refs.input.focus();
                //     })
                // }
            },

            saveEdit: function(list) {
                //save your changes
                var newlist = JSON.parse(JSON.stringify(this.listarray))
                newlist.pop();
                list.val = JSON.stringify(newlist);
                this.$emit('update:edittext', list.val);
                this.toggleEdit(list);
                console.log('Save!')
            },

            checkKey: function(e, index) {
                if (e.which == 13 || e.keyCode == 13) {
                    e.preventDefault();
                    return false;
                }
                if (this.listarray.length == (index+1)) {
                    this.listarray.push({
                        id: index+1,
                        value: ""
                    })
                }
                return true;
            },

            checkList: function(index) {
                // If it isn't the last item and it is empty
                if ((this.listarray.length != (index + 1)) &&
                    (this.listarray[index].value.length == 0))
                {
                    this.listarray.splice(index, 1);
                    for (var i=index; i<this.listarray.length; i++) {
                        this.listarray[i].id = i;
                    }
                }
            }
        },
        computed: {
            validlistitems: function() {
                var newlist = [];
                if (this.list.val.length)
                    newlist = JSON.parse(this.list.val);
                return newlist;
            }
        }
    }
</script>
