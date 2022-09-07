<template>
    <div class="vpillbox__template">
        <div class="vpillbox__component form-group">
            <div class="vpillbox__vpills">
                <span v-for="pill in pills" :key="pill.id" :id="pill.id" class="badge bg-primary m-1 pl-2">
                    {{
                            pill.value
                    }}
                    <button type="button" class="btn btn-primary mx-1" @click="removePill(pill.id)">
                        <i class="fas fa-times"></i>
                    </button>
                </span>
            </div>

            <select class="form-select" @change="optionChanged">
                <option value="-1" disabled selected>
                    {{
                            props.placeholder ? props.placeholder : "Επιλέξτε κατηγορία/ες"
                    }}
                </option>
                <option v-for="option in props.options" :key="option.id" :value="option.id">
                    {{ option.name }}
                </option>
            </select>
            <input type="text" :name="props.name" hidden v-model="selectedOptions" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';

const props = defineProps<{
    options: Array<App.Models.SchoolCategory>,
    value: string | number,
    name: string,
    placeholder?: string,
}>();

type Pill = { id: string, value: string };

let selectedOptions = ref(Array<string>());
let pills = ref(Array<Pill>());

onMounted(() => {
    nextTick(() => {
        if (typeof props.value === "string") {
            let values = props.value.split(',');
            values.forEach(val => addPill(val));
        }
        else if (typeof props.value == "number") {
            addPill(props.value.toString());
        }
    });
});

const addPill = (value: string) => {
    if (value != "") {
        selectedOptions.value.push(value);
        var name = "";
        props.options.forEach(option => {
            if (option.id.toString() == value)
                name = option.name;
        });
        pills.value.push({ id: value, value: name });
    }
}

const optionChanged = (event: Event) => {
    const target = event.target;

    if (target instanceof HTMLSelectElement) {
        let value = target.value;
        if (!selectedOptions.value.includes(value)) {
            addPill(value);
        }
        target.value = "-1";
    }
}

const removePill = (option: string) => {
    pills.value = pills.value.filter(pill => pill.id != option)
    selectedOptions.value = selectedOptions.value.filter(op => op != option);
}
</script>
