<template>
    <div class="editable-list-group row justify-content-center">
        <div class="card col-8">
            <div class="card-header" v-if="!restricted" @click="toggleEdit(list)" v-show="!list.edit">
                <div class="d-flex justify-content-end">
                    <i class="fas fa-edit">
                    </i>
                </div>
            </div>
            <div class="card-body" v-show="!list.edit" @click="startEdit()">
                Τιμές:
                <ol>
                    <li v-for="item in validListItems" :key="item.id">
                        {{ item.value }}
                    </li>
                </ol>
            </div>
            <div class="card-header" @click="saveEdit(list)" v-show="list.edit">
                <div class="d-flex justify-content-end">
                    <i class="fas fa-save">
                    </i>
                </div>
            </div>
            <div class="card-body" v-show="list.edit">
                <ol>
                    <li v-for="(item, index) in list_array" :key="item.id" ref="items">
                        <input type="text" v-model="item.value" placeholder="Νέα επιλογή"
                            @paste="checkPaste($event, index)" @keypress="checkKey($event, index)"
                            @blur="checkList($event, index)" />
                    </li>
                </ol>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, nextTick, computed, ref } from 'vue';
import type { Ref } from 'vue';

const emit = defineEmits(['update:edittext']);

const props = defineProps<{
    cbselected: number,
    edittext: string,
    restricted: boolean,
}>();

type List = { val: string, edit: boolean };
type ValidListItem = { id: number, value: string }

let list: Ref<List> = ref({
    val: props.edittext,
    edit: false
});

let list_array = ref(props.edittext.length ? JSON.parse(props.edittext) : []);

let items = ref<Array<HTMLInputElement | null> | null>(null);

onMounted(() => {
    nextTick(function () {
        if (list_array.value.length &&
            (list_array.value[list_array.value.length - 1] != ''))
            list_array.value.push({
                id: list_array.length,
                value: ''
            });
    });
});

const toggleEdit = (list: List) => {
    list.edit = !list.edit;
    if (!list_array.value.length) {
        list_array.value.push({
            id: 0,
            value: ""
        })
    }

    if (list.edit) {
        nextTick(function () {
            if (items.value) {
                console.log(items.value[0]?.children[0]);
                (items.value[0]?.children[0] as HTMLInputElement)?.focus();
            }
        });
    }
};

const startEdit = () => {
    if (!list.value.edit) {
        toggleEdit(list.value);
    }
}

const saveEdit = (list: List) => {
    //save your changes
    var new_list = JSON.parse(JSON.stringify(list_array.value))
    new_list.pop();
    list.val = JSON.stringify(new_list);
    emit('update:edittext', list.val);
    toggleEdit(list);
};

const checkPaste = (event: ClipboardEvent, index: number) => {
    checkForNewListItem(index);

    return true;
}

const checkKey = (event: KeyboardEvent, index: number) => {
    if (event.which == 13 || event.keyCode == 13) {
        event.preventDefault();
        return false;
    }
    checkForNewListItem(index);

    return true;
}

const checkForNewListItem = (index: number) => {
    if (list_array.value.length == (index + 1)) {
        list_array.value.push({
            id: index + 1,
            value: ""
        })
    }
}

const checkList = (event: FocusEvent, index: number) => {
    // If it isn't the last item and it is empty
    if ((list_array.value.length != (index + 1)) &&
        (list_array.value[index].value.length == 0)) {
        list_array.value.splice(index, 1);
        for (var i = index; i < list_array.value.length; i++) {
            list_array.value[i].id = i;
        }
    }

    if (event.target instanceof HTMLElement) {
        const grandparent = event.target.parentElement?.parentElement;

        const focusedItem = event.relatedTarget;

        if (!(focusedItem instanceof HTMLInputElement)) {
            saveEdit(list.value);
            return;
        }

        let allUnfocused = true;
        if (grandparent) {
            grandparent.childNodes.forEach(child => {
                child.childNodes.forEach(child => {
                    if (child.isSameNode(focusedItem)) {
                        allUnfocused = false;
                    }
                })
            })
        }

        if (allUnfocused) {
            saveEdit(list.value);
        }
    }
}

const validListItems = computed(() => {
    var new_list = Array<ValidListItem>();
    if (list.value.val.length)
        new_list = JSON.parse(list.value.val);
    return new_list;
});
</script>
