<template>
    <div class="editable-text-group">
        <span v-if="!restricted" v-show="!text.edit" @click="toggleEdit(text)" class="editable-text-label"><a>{{
                text.val
        }}</a><i class="fas fa-pencil-alt editable-text-icon"></i></span>
        <span v-if="restricted">{{ text.val }}</span>

        <input type="text" ref="input" class="editable-text-input col-12" :name="fid" v-model="text.val"
            v-show="text.edit" @keypress="checkKey($event, text)" @blur="saveEdit(text)" />
        <br />
    </div>
</template>

<script setup lang="ts">
import { ref, Ref, nextTick } from "vue";

const emit = defineEmits(['update:edittext']);

const props = defineProps<{
    edittext: string,
    fid: string,
    restricted?: boolean,
}>();

type TextObject = { val: string, edit: boolean };

let text: Ref<TextObject> = ref({
    val: props.edittext,
    edit: false
});

const input: Ref<HTMLElement | null> = ref(null);

const toggleEdit = (text: TextObject) => {
    text.edit = !text.edit;

    // Focus input field
    if (text.edit) {
        nextTick(function () {
            if (input.value) {
                input.value.focus();
            }
        })
    }
};

const saveEdit = (text: TextObject) => {
    //save your changes
    if (text.val == '') {
        text.val = props.edittext
    }
    emit('update:edittext', text.val);
    toggleEdit(text);
};

const checkKey = (event: KeyboardEvent, text: TextObject) => {
    if (event.which == 13 || event.keyCode == 13) {
        event.preventDefault();
        if (event.target instanceof HTMLElement) {
            event.target.blur();
        }
        return false;
    }
    return true;
};
</script>
