<template>
    <div class="editable-list-group form-row justify-content-center">
        <div class="card col-8">
            <div class="card-header" v-if="!restricted" @click="toggleEdit(list)" v-show="!list.edit">
                <div class="d-flex justify-content-end">
                    <i class="fas fa-edit">
                    </i>
                </div>
            </div>
            <div class="card-body" v-show="!list.edit">
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
                    <li v-for="(item, index) in list_array" :key="item.id">
                        <input type="text" v-model="item.value" placeholder="Νέα επιλογή"
                            @keypress="checkKey($event, index)" @blur="checkList(index)" />
                    </li>
                </ol>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, nextTick, computed, ref } from 'vue';

const emit = defineEmits(['update:edittext']);

const props = defineProps([
    'cbselected',
    'edittext',
    'fid',
    'restricted'
]);

let list = ref({
    val: props.edittext,
    edit: false
});

let list_array = ref(props.edittext.length ? JSON.parse(props.edittext) : []);


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

const toggleEdit = (list) => {
    list.edit = !list.edit;
    if (!list_array.value.length) {
        list_array.value.push({
            id: 0,
            value: ""
        })
    }
};

const saveEdit = (list) => {
    //save your changes
    var new_list = JSON.parse(JSON.stringify(list_array.value))
    new_list.pop();
    list.val = JSON.stringify(new_list);
    emit('update:edittext', list.val);
    toggleEdit(list);
};

const checkKey = (event, index) => {
    if (event.which == 13 || event.keyCode == 13) {
        event.preventDefault();
        return false;
    }
    if (list_array.length == (index + 1)) {
        list_array.push({
            id: index + 1,
            value: ""
        })
    }
    return true;
}

const checkList = (index) => {
    // If it isn't the last item and it is empty
    if ((list_array.length != (index + 1)) &&
        (list_array[index].length == 0)) {
        list_array.splice(index, 1);
        for (var i = index; i < list_array.length; i++) {
            list_array[i].id = i;
        }
    }
}

const validListItems = computed(() => {
    var new_list = [];
    if (list.value.val.length)
        new_list = JSON.parse(list.value.val);
    return new_list;
});
</script>
