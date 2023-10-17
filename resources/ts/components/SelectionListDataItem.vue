<script setup lang="ts">
import { nextTick, onMounted, ref } from "vue";

const props = withDefaults(
    defineProps<{
        value?: string;
        id: string;
        numItems: number;
        lastIndex: number;
    }>(),
    {
        value: "",
    }
);

const itemValue = ref(props.id);
const itemTitle = ref(props.value);

const emit = defineEmits(["addRow", "delRow"]);

function onKeyDown(e: KeyboardEvent) {
    if (
        e.shiftKey === false &&
        e.key == "Tab" &&
        `${props.lastIndex}` === props.id
    ) {
        e.preventDefault();
        emit("addRow");
    }
}

const inputRef = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (props.numItems === 1) return;
    inputRef.value?.focus();
});
</script>

<template>
    <div class="row justify-content-center mt-2">
        <div class="col-auto">
            <button
                v-if="numItems > 1"
                class="btn btn-danger"
                @click="$emit('delRow', id)"
                type="button"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                data-bs-title="Διαγραφή τιμής"
            >
                -
            </button>
        </div>
        <div class="col-auto justify-content-center d-flex">
            <div class="my-auto">{{ id }}.</div>
            <input
                class="form-control"
                name="id[]"
                type="text"
                hidden
                placeholder="Τιμή"
                v-model="itemValue"
            />
        </div>
        <div class="col-auto">
            <input
                class="form-control"
                name="value[]"
                type="text"
                ref="inputRef"
                placeholder="Κείμενο"
                v-model="itemTitle"
                @keydown="onKeyDown"
            />
        </div>
    </div>
</template>
