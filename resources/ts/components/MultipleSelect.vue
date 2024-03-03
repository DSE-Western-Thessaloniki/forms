<script setup lang="ts">
const props = defineProps<{
    modelValue?: { id: string; name: string }[];
}>();
</script>

<template>
    <div>
        <div v-for="value in modelValue">
            {{ value.name }}
        </div>
    </div>
</template>
