<script setup lang="ts">
import Button from "./Button.vue";
import ButtonGroupSeparator from "./ButtonGroupSeparator.vue";

const emit = defineEmits<{
    boldPressed: [];
    italicPressed: [];
    headingPressed: [];
    quotePressed: [];
    ulListPressed: [];
    olListPressed: [];
    linkPressed: [];
    imagePressed: [];
    previewPressed: [];
    splitPressed: [];
    fullScreenPressed: [];
    helpPressed: [];
}>();
</script>

<template>
    <div class="toolbar">
        <Button @click="emit('boldPressed')">
            <i class="fa fa-w fa-bold fa-1_2x"></i>
        </Button>
        <Button @click="emit('italicPressed')">
            <i class="fa fa-w fa-italic fa-1_2x"></i>
        </Button>
        <Button @click="emit('headingPressed')">
            <i class="fa fa-w fa-heading fa-1_2x"></i>
        </Button>
        <ButtonGroupSeparator></ButtonGroupSeparator>
        <Button @click="emit('quotePressed')">
            <i class="fa fa-w fa-quote-left fa-1_2x"></i>
        </Button>
        <Button @click="emit('ulListPressed')">
            <i class="fa fa-w fa-list-ul fa-1_2x"></i>
        </Button>
        <Button @click="emit('olListPressed')">
            <i class="fa fa-w fa-list-ol fa-1_2x"></i>
        </Button>
        <ButtonGroupSeparator></ButtonGroupSeparator>
        <Button @click="emit('linkPressed')">
            <i class="fa fa-w fa-link fa-1_2x"></i>
        </Button>
        <Button @click="emit('imagePressed')">
            <i class="fa fa-w fa-image fa-1_2x"></i>
        </Button>
        <ButtonGroupSeparator></ButtonGroupSeparator>
        <Button @click="emit('helpPressed')">
            <i class="fa fa-w fa-question-circle fa-1_2x"></i>
        </Button>
    </div>
</template>
<style type="text/css">
.toolbar {
    display: flex;
    background-color: white;
    flex-direction: row;
    flex-wrap: wrap;
    flex-grow: 1;
    padding: 0.5em;
    border: 1px solid gray;
    border-bottom-style: hidden;
    border-radius: 4px;
    border-bottom-left-radius: 0%;
    border-bottom-right-radius: 0%;
    line-height: 1;
}
.fa-1_2x {
    font-size: 1.5em;
}
</style>
