<template>
    <div class="vpillbox__template">
        <div class="vpillbox__component form-group">
            <div class="vpillbox__vpills">
                <span v-for="vpill in vpills" :key="vpill.id" :id="vpill.id" class="badge badge-primary m-1 pl-2">
                    {{
                            vpill.value
                    }}
                    <button type="button" class="btn btn-primary mx-1" @click="removePill(vpill.id)">
                        <i class="fas fa-times"></i>
                    </button>
                </span>
            </div>

            <select class="form-control" @change="optionChanged">
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

<script setup>
import { ref, onMounted, nextTick } from 'vue';

const props = defineProps({
    options: Array,
    value: String,
    name: String,
    placeholder: String,
});

let selectedOptions = ref([]);
let vpills = ref([]);

onMounted(() => {
    nextTick(() => {
        if (typeof props.value === "string" || props.value instanceof String) {
            let values = props.value.split(',');
            values.forEach(val => addPill(val));
        }
        else if (typeof props.value == "number" || props.value instanceof Number) {
            addPill(props.value.toString());
        }
    });
});

const addPill = (value) => {
    if (value != "") {
        selectedOptions.value.push(value);
        var name = "";
        props.options.forEach(option => {
            if (option.id == value)
                name = option.name;
        });
        vpills.value.push({ id: value, value: name });
    }
}

const optionChanged = (event) => {
    console.log(event.target.value)
    if (!selectedOptions.value.includes(event.target.value)) {
        addPill(event.target.value);
    }
    event.target.value = -1;
}

const removePill = (option) => {
    vpills.value = vpills.value.filter(pill => pill.id != option)
    selectedOptions.value = selectedOptions.value.filter(op => op != option);
}
</script>
