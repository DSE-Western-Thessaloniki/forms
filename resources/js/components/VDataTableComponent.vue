<template>
    <div class="table-responsive d-flex max-600">
        <table class="table table-striped table-bordered table-hover">
            <thead class="fixed-header">
                <tr>
                    <th>Σχολική Μονάδα</th>
                    <th>Κωδικός</th>
                    <th v-for="column in columnsObj" :key="column.id">{{ column.title }}</th>
                    <th>Δημιουργήθηκε</th>
                    <th>Ενημερώθηκε</th>
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
                             (typeof(data[line.code][column.title][line.record]) != "undefined") &&
                             (typeof(data[line.code][column.title][line.record]['value']) != "undefined")) ?
                            data[line.code][column.title][line.record]['value'] :
                            ''
                        }}
                    </td>
                    <td>{{ created(line) }}</td>
                    <td>{{ updated(line) }}</td>
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
        },
        methods: {
            created: function(line) {
                var created_date;
                this.columnsObj.forEach(column => {
                    if ((typeof(this.data[line.code]) != "undefined") &&
                        (typeof(this.data[line.code][column.title]) != "undefined") &&
                        (typeof(this.data[line.code][column.title][line.record]) != "undefined")) {

                        if (typeof(created_date) === "undefined") {
                            created_date = new Date(this.data[line.code][column.title][line.record]['created']);
                        } else {
                            var temp_date = new Date(this.data[line.code][column.title][line.record]['created'])
                            if (created_date > temp_date) {
                                created_date = temp_date;
                            }
                        }
                    }
                });

                return typeof(created_date) === "undefined" ? '' : created_date.toLocaleString();
            },
            updated: function(line) {
                var updated_date;
                this.columnsObj.forEach(column => {
                    if ((typeof(this.data[line.code]) != "undefined") &&
                        (typeof(this.data[line.code][column.title]) != "undefined") &&
                        (typeof(this.data[line.code][column.title][line.record]) != "undefined")) {

                        if (typeof(updated_date) === "undefined") {
                            updated_date = new Date(this.data[line.code][column.title][line.record]['created']);
                        } else {
                            var temp_date = new Date(this.data[line.code][column.title][line.record]['created'])
                            if (updated_date < temp_date) {
                                updated_date = temp_date;
                            }
                        }
                    }
                });

                return typeof(updated_date) === "undefined" ? '' : updated_date.toLocaleString();
            }
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
