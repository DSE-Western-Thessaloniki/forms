<template>
    <div class="editable-data-table">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th v-for="column in columns" :key="column.id">{{ column.title }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows">
                        <td v-for="column in columns" :key="column.id" v-html="getData(column, row)"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button type="button" id="addRow" class="btn btn-primary" @click="addRow()"><i class="fa fa-plus"></i> Προσθήκη γραμμής δεδομένων</button>
    </div>
</template>

<script>

    export default {
        props: {
            columns: Array,
            data: Object,
        },
        mounted() {
            console.log("EditableDataTable is mounted.");
        },
        data: function() {
            var row_count = 0;
            var data = this.data;
            this.columns.forEach((column) => {
                if (row_count < data[column.title]?.length) {
                    row_count = data[column.title]?.length;
                }
            });

            return {
                rows: row_count,
            }
        },
        methods: {
            getData: function(column, row) {
                var value = '';
                if (this.data[column.title] && this.data[column.title][row-1]) {
                    value = this.data[column.title][row-1];
                }
                console.log(column)
                switch (column.type) {
                    case 0: // Πεδίο κειμένου
                    case 1: // Περιοχή κειμένου
                    case 6: // Ημερομηνία
                    case 7: // Αριθμός
                    case 9: // E-mail
                    case 10: // Url
                        var el = document.querySelector('#table_template #f'+column.id).cloneNode(true);
                        if (column.type == 1) {
                            el.innerHTML = value;
                        }
                        else {
                            el.setAttribute("value", value);
                        }
                        el.setAttribute("id", "f"+column.id+"-r"+row);
                        el.setAttribute("name", "f"+column.id+"-r"+row);
                        var item = el.outerHTML;
                        el.remove();
                        return item;
                    case 2: // Επιλογή ενός από πολλά
                    case 3: // Πολλαπλή επιλογή
                        var el = document.querySelector('#table_template input[name=f'+column.id+']').parentElement.parentElement.cloneNode(true);
                        item="";
                        if (value) {
                            el.querySelector("[value="+value+"]").setAttribute("checked", "checked");
                        }
                        el.querySelectorAll("[name=f"+column.id+"]").forEach(function(radio) {
                            radio.setAttribute("name", "f"+column.id+"-r"+row);
                        });
                        var item = el.outerHTML;
                        el.remove();
                        return item;
                    case 4: // Λίστα επιλογών
                        var el = document.querySelector('#table_template #f'+column.id).cloneNode(true);
                        item="";
                        if (value) {
                            el.querySelector("[value="+value+"]").setAttribute("selected", "selected");
                        }
                        el.setAttribute("id", "f"+column.id+"-r"+row);
                        el.setAttribute("name", "f"+column.id+"-r"+row);
                        var item = el.outerHTML;
                        el.remove();
                        return item;
                    case 8: // Τηλέφωνο
                        var el = document.querySelector('#table_template #f'+column.id).parentElement.cloneNode(true);
                        var input=el.querySelector('#f'+column.id);
                        input.setAttribute("value", value);
                        input.setAttribute("id", "f"+column.id+"-r"+row);
                        input.setAttribute("name", "f"+column.id+"-r"+row);
                        var item = el.outerHTML;
                        el.remove();
                        return item;
                }
            },
            addRow: function() {
                this.rows += 1;
            }
        },
        computed: {
        }
    }
</script>
