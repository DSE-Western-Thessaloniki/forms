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
                                ((typeof (data[line.code]) !== "undefined") &&
                                    (typeof ((data[line.code] as AssociativeArray<string>)[column.title]) !== "undefined") &&
                                    (typeof (((data[line.code] as AssociativeArray<string>)[column.title] as
                                        AssociativeArray<string>)[line.record]) !== "undefined") &&
                                    (typeof ((((data[line.code] as AssociativeArray<string>)[column.title] as
                                        AssociativeArray<string>)[line.record] as AssociativeArray<string>)['value'])
                                        !== "undefined")) ?
                                    (((data[line.code] as AssociativeArray<string>)[column.title] as
                                        AssociativeArray<string>)[line.record] as AssociativeArray<string>
                                    )['value'] as string :
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

<script setup lang="ts">
import { computed } from 'vue';

type AssociativeArray<T = unknown> = { [key: string | number]: AssociativeArray<T> | T | undefined };
type LineObject = { id: number, code: string, name: string, record: number }
type ColumnObject = { id: number, title: string }

const props = defineProps<{
    columns: Array<string>,
    data: AssociativeArray<string>,
    schools: Array<App.Models.School>,
}>();

const created = (line: LineObject) => {
    let created_date: Date | undefined;

    columnsObj.value.forEach(column => {
        if ((typeof (props.data[line.code]) !== "undefined") &&
            (typeof ((props.data[line.code] as AssociativeArray<string>)[column.title]) !== "undefined") &&
            (typeof (((props.data[line.code] as AssociativeArray<string>)[column.title] as AssociativeArray<string>)[line.record]) !== "undefined")) {

            const dataLine = ((props.data[line.code] as AssociativeArray<string>)[column.title] as AssociativeArray<string>)[line.record] as AssociativeArray<string>
            if (typeof (created_date) === "undefined") {
                created_date = new Date(dataLine['created'] as string);
            } else {
                var temp_date = new Date(dataLine['created'] as string)
                if (created_date > temp_date) {
                    created_date = temp_date;
                }
            }
        }
    });

    return typeof (created_date) === "undefined" ? '' : created_date.toLocaleString();
}

const updated = (line: LineObject) => {
    let updated_date: Date | undefined;

    columnsObj.value.forEach(column => {
        if ((typeof (props.data[line.code]) !== "undefined") &&
            (typeof ((props.data[line.code] as AssociativeArray<string>)[column.title]) !== "undefined") &&
            (typeof (((props.data[line.code] as AssociativeArray<string>)[column.title] as AssociativeArray<string>)[line.record]) !== "undefined")) {

            const dataLine = ((props.data[line.code] as AssociativeArray<string>)[column.title] as AssociativeArray<string>)[line.record] as AssociativeArray<string>
            if (typeof (updated_date) === "undefined") {
                updated_date = new Date(dataLine['created'] as string);
            } else {
                var temp_date = new Date(dataLine['created'] as string)
                if (updated_date < temp_date) {
                    updated_date = temp_date;
                }
            }
        }
    });

    return typeof (updated_date) === "undefined" ? '' : updated_date.toLocaleString();
};

const columnsObj = computed(() => {
    let columnsObj: Array<ColumnObject> = new Array();
    let i = 0;

    props.columns.forEach(column => {
        columnsObj.push({ id: i, title: column });
        i += 1
    });

    return columnsObj;
})

const lines = computed(() => {
    var i = 0;
    var lines: Array<LineObject> = Array();
    props.schools.forEach(school => {
        var record = 0;
        do {
            var ok = false;
            columnsObj.value.forEach(column => {
                if ((typeof (props.data[school.code]) !== "undefined") &&
                    (typeof ((props.data[school.code] as AssociativeArray<string>)[column.title]) !== "undefined") &&
                    (typeof (((props.data[school.code] as AssociativeArray<string>)[column.title] as AssociativeArray<string>)[record]) !== "undefined")) {
                    ok = true;
                }
            });

            if (ok || record == 0) {
                lines.push({ id: i, code: school.code, name: school.name, record: record });
                i += 1;
            }
            record += 1;
        } while (ok);
    });

    return lines;
});
</script>