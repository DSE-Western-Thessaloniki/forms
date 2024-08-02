<script setup lang="ts">
const props = defineProps<{
    modelValue?: boolean;
}>();
</script>

<template>
    <button type="button" class="toolbar-button">
        <slot />
    </button>
</template>
<style lang="css">
.toolbar-button {
    /* Add your custom styles here */
    transition: background-color 0.3s ease-in-out;
    background-color: lightgray;
    padding: 0.25em 0.75em;
    border: 1px solid gray;
    cursor: pointer;
}

.toolbar-button:hover {
    background-color: darkgray;
}
</style>
