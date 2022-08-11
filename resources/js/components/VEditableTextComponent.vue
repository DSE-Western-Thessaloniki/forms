<template>
    <div
    class="editable-text-group"
    >
        <span
        v-if="!restricted"
        v-show="!text.edit"
        @click="toggleEdit(text)"
        class="editable-text-label"
        ><a>{{text.val}}</a><i class="fas fa-pencil-alt editable-text-icon"></i></span>
        <span v-if="restricted">{{text.val}}</span>

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

<script setup>
import { ref, nextTick } from "vue";

const emit = defineEmits(['update:edittext']);

const props = defineProps([
    'edittext',
    'fid',
    'restricted'
]);

let text = ref({
    val: props.edittext,
    edit: false
});

const input = ref(null);

const toggleEdit = (text) => {
    text.edit = !text.edit;

    // Focus input field
    if(text.edit){
        nextTick(function() {
            input.value.focus();
        })
    }
};

const saveEdit = (text) => {
    //save your changes
    if (text.val == '') {
        text.val = props.edittext
    }
    emit('update:edittext', text.val);
    toggleEdit(text);
};

const checkKey = (event, text) => {
    if (event.which == 13 || event.keyCode == 13) {
        event.preventDefault();
        event.target.blur();
        return false;
    }
    return true;
};
</script>
