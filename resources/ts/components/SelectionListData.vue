<script setup lang="ts">
import { Ref, computed, nextTick, ref } from "vue";
import SelectionListDataItem from "./SelectionListDataItem.vue";

const props = withDefaults(
    defineProps<{
        data?: string;
        readonly?: boolean;
        update?: boolean;
    }>(),
    {
        data: "[]",
        readonly: false,
        update: false,
    }
);

type SelectionListRow = {
    value: string;
    id: string;
};

let selectionListData: Ref<SelectionListRow[]>;

try {
    selectionListData = ref(JSON.parse(props.data));
} catch (e) {
    console.error(e);
    selectionListData = ref([]);
}

if (selectionListData.value.length === 0) {
    selectionListData.value.push({
        value: "",
        id: "1",
    });
}

let lastIndex = computed(() =>
    Math.max(0, ...selectionListData.value.map((item) => parseInt(item.id)))
);

const addRow = () => {
    let nextIndex = lastIndex.value + 1;
    selectionListData.value.push({
        value: "",
        id: `${nextIndex}`,
    });
};

const delRow = (id: string) => {
    if (selectionListData.value.length > 1) {
        selectionListData.value = selectionListData.value.filter(
            (item) => item.id !== id
        );
    }
};

const numItems = computed(() => selectionListData.value.length);
</script>

<template>
    <div class="form-group row text-center my-4">
        <div>Δεδομένα</div>
        <div v-for="item in selectionListData" :key="item.id">
            <SelectionListDataItem
                :value="item.value"
                :id="item.id"
                :num-items="numItems"
                :last-index="lastIndex"
                @add-row="addRow"
                @del-row="delRow"
            ></SelectionListDataItem>
        </div>
        <div class="mt-3">
            <button class="btn btn-success" @click="addRow" type="button">
                + Προσθήκη επιπλέον επιλογής
            </button>
        </div>
    </div>
</template>
