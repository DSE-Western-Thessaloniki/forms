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
                <tr v-for="line in lines" :key="line.id">
                    <td>{{ line.name }}</td>
                    <td>{{ line.code }}</td>
                    <td v-for="column in columnsObj" :key="column.id">
                        {{
                            ((typeof(data[line.code]) != "undefined") &&
                             (typeof(data[line.code][column.title]) != "undefined") &&
                             (typeof(data[line.code][column.title][line.record]) != "undefined")) ?
                            data[line.code][column.title][line.record] :
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
            console.log(this.lines);
        },
        methods: {
        },
        computed: {
            columnsObj: function() {
                var columnsObj = Array();
                var i=0;
                this.columns.forEach(column => {
                    columnsObj.push({id: i, title: column});
                    i += 1
                });

                return columnsObj;
            },
            lines: function() {
                var i = 0;
                var lines = Array();
                this.schools.forEach(school => {
                    var record = 0;
                    do {
                        var ok = false;
                        this.columnsObj.forEach(column => {
                            if ((typeof(this.data[school.code]) != "undefined") &&
                                (typeof(this.data[school.code][column.title]) != "undefined") &&
                                (typeof(this.data[school.code][column.title][record]) != "undefined"))
                                {
                                    ok = true;
                                }
                        });

                        if (ok || record == 0) {
                            lines.push({id: i, code: school.code, name: school.name, record: record});
                            i += 1;
                        }
                        record += 1;
                    } while (ok);
                });

                return lines;
            }
        }
    }
</script>
