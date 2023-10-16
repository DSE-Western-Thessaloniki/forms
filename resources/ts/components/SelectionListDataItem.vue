<script setup lang="ts">
import { ref } from "vue";

const props = withDefaults(
    defineProps<{
        title?: string;
        value: string;
    }>(),
    {
        title: "",
    }
);

const itemValue = ref(props.value);
const itemTitle = ref(props.title);

const emit = defineEmits(["addRow", "delRow"]);

function onKeyDown(e: KeyboardEvent) {
    console.log(e);
    if (e.key == "Tab") {
        emit("addRow");
    }
}
</script>

<template>
    <div class="row justify-content-center mt-2">
        <div class="col-auto">
            <button
                class="btn btn-danger"
                @click="$emit('delRow')"
                type="button"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                data-bs-title="Διαγραφή τιμής"
            >
                -
            </button>
        </div>
        <div class="col-auto">
            <input
                class="form-control"
                name="value"
                type="text"
                placeholder="Τιμή"
                v-model="itemValue"
            />
        </div>
        <div class="col-auto">
            <input
                class="form-control"
                name="title"
                type="text"
                placeholder="Κείμενο"
                v-model="itemTitle"
                @keydown="onKeyDown"
            />
        </div>
    </div>
</template>
