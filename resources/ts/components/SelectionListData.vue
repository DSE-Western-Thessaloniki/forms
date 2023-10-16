<script setup lang="ts">
import { Ref, ref } from "vue";
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
    title: string;
    value: string;
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
        title: "",
        value: "1",
    });
}

let lastIndex = Math.max(
    0,
    ...selectionListData.value.map((item) => parseInt(item.value))
);

const addRow = () => {
    lastIndex++;
    selectionListData.value.push({
        title: "",
        value: `${lastIndex}`,
    });
};

const delRow = (item: number) => {};
</script>

<template>
    <div class="form-group row text-center my-4">
        <div>Δεδομένα</div>
        <div v-for="item in selectionListData" :key="item.value">
            <SelectionListDataItem
                :title="item.title"
                :value="item.value"
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
