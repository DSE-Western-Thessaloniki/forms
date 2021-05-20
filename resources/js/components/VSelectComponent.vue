<template>
    <div>
        <!--<v-select :options="values" :pushTags="true"></v-select>-->
        <input type="text" hidden :value="selectedValue">
    </div>
</template>

<script>
    export default {
        props: {
            options: String,
            name: String,
        },
        mounted() {
            console.log('Component mounted.')
        },
        data: function() {
            return {
                values: JSON.parse('['+this.options+']'),
                selectedValue: '',
            }
        },
        methods: {
            selected: function(text) {
                this.selectedValue = text;
            },
        }
    }
</script>
