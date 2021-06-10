<template>
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>Σχολική Μονάδα</th>
                    <th>Κωδικός</th>
                    <th v-for="column in columnsObj" :key="column.id">{{ column.title }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="school in schools" :key="school.id">
                    <td>{{ school.name }}</td>
                    <td>{{ school.code }}</td>
                    <td v-for="column in columnsObj" :key="column.id">
                        {{
                            ((typeof(data[school.code]) != "undefined") &&
                             (typeof(data[school.code][column.title]) != "undefined")) ?
                            data[school.code][column.title] :
                            ''
                        }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>

    export default {
        props: {
            columns: Array,
            data: Object,
            schools: Array,
        },
        mounted() {
            console.log("DataTable is mounted.");
            console.log(this.columns);
            console.log(this.data);
            console.log(this.schools);
        },
        data: function() {
            var columnsObj = Array();
            var i=0;
            this.columns.forEach(column => {
                columnsObj.push({id: i, title: column});
            });

            return {
                columnsObj: columnsObj,
            }
        },
        methods: {
        },
        computed: {
        }
    }
</script>
