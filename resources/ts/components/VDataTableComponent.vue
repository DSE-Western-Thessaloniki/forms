<template>
    <div class="table-responsive d-flex max-600">
        <table class="table table-striped table-bordered table-hover">
            <thead class="fixed-header">
                <tr>
                    <th v-for="column in columnsObj" :key="column.id">
                        {{ column.title }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="line in lines" :key="line.id">
                    <td>{{ line.name }}</td>
                    <td>{{ line.code }}</td>
                    <td v-for="column in columnsObj" :key="column.id">
                        <a
                            v-if="isLink(data, line, column)"
                            :href="getLink(data, line, column)"
                            >{{ echoData(data, line, column) }}</a
                        >
                        <div v-else>{{ echoData(data, line, column) }}</div>
                    </td>
                    <td>{{ created(line) }}</td>
                    <td>{{ updated(line) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";

type LineObject = { id: number; code: string; name: string; record: number };
type ColumnObject = { id: number; title: string };

const props = defineProps<{
    columns: Array<string>;
    data: App.Types.AssociativeArray<string>;
    schools: Array<App.Models.School>;
    teachers: Array<App.Models.Teacher>;
    other_teachers: Array<App.Models.OtherTeacher>;
    for_teachers: number;
    for_all_teachers: number;
}>();

/**
 * Συνάρτηση για τον υπολογισμό της ημερομηνίας δημιουργίας της εγγραφής.
 * Η συνάρτηση είναι απαραίτητη γιατί υπάρχει πιθανότητα τροποποίησης της φόρμας
 * μετά την αρχική δημιουργία της οπότε πρέπει να ελέγξουμε κάθε πεδίο για την
 * δημιουργία του.
 *
 * @param line Ένα αντικείμενο τύπου LineObject
 */
const created = (line: LineObject) => {
    let created_date: Date | undefined;

    columnsObj.value.forEach((column) => {
        if (
            typeof props.data[line.code] !== "undefined" &&
            typeof (
                props.data[line.code] as App.Types.AssociativeArray<string>
            )[column.title] !== "undefined" &&
            typeof (
                (props.data[line.code] as App.Types.AssociativeArray<string>)[
                    column.title
                ] as App.Types.AssociativeArray<string>
            )[line.record] !== "undefined"
        ) {
            const dataLine = (
                (props.data[line.code] as App.Types.AssociativeArray<string>)[
                    column.title
                ] as App.Types.AssociativeArray<string>
            )[line.record] as App.Types.AssociativeArray<string>;
            if (typeof created_date === "undefined") {
                created_date = new Date(dataLine["created"] as string);
            } else {
                var temp_date = new Date(dataLine["created"] as string);
                if (created_date > temp_date) {
                    created_date = temp_date;
                }
            }
        }
    });

    return typeof created_date === "undefined"
        ? ""
        : created_date.toLocaleString();
};

/**
 * Συνάρτηση για τον υπολογισμό της ημερομηνίας ενημέρωσης της εγγραφής.
 * Η συνάρτηση είναι απαραίτητη γιατί υπάρχει πιθανότητα τροποποίησης της φόρμας
 * μετά την αρχική δημιουργία της οπότε πρέπει να ελέγξουμε κάθε πεδίο για την
 * ενημέρωσή του.
 *
 * @param line Ένα αντικείμενο τύπου LineObject
 */
const updated = (line: LineObject) => {
    let updated_date: Date | undefined;

    columnsObj.value.forEach((column) => {
        if (
            typeof props.data[line.code] !== "undefined" &&
            typeof (
                props.data[line.code] as App.Types.AssociativeArray<string>
            )[column.title] !== "undefined" &&
            typeof (
                (props.data[line.code] as App.Types.AssociativeArray<string>)[
                    column.title
                ] as App.Types.AssociativeArray<string>
            )[line.record] !== "undefined"
        ) {
            const dataLine = (
                (props.data[line.code] as App.Types.AssociativeArray<string>)[
                    column.title
                ] as App.Types.AssociativeArray<string>
            )[line.record] as App.Types.AssociativeArray<string>;
            if (typeof updated_date === "undefined") {
                updated_date = new Date(dataLine["updated"] as string);
            } else {
                var temp_date = new Date(dataLine["updated"] as string);
                if (updated_date < temp_date) {
                    updated_date = temp_date;
                }
            }
        }
    });

    return typeof updated_date === "undefined"
        ? ""
        : updated_date.toLocaleString();
};

/**
 * Computed property που δημιουργεί ένα αντικείμενο από την λίστα των στηλών που
 * μας έδωσαν μέσω του property columns.
 */
const columnsObj = computed(() => {
    let columnsObj: Array<ColumnObject> = new Array();
    let i = 0;

    props.columns.forEach((column) => {
        columnsObj.push({ id: i, title: column });
        i += 1;
    });

    return columnsObj;
});

/**
 * Computed property για την μετατροπή των αρχικών δεδομένων που μας περάστηκαν
 * σε γραμμές του πίνακα.
 */
const lines = computed(() => {
    let i = 0;
    let lines: Array<LineObject> = Array();
    if (props.for_teachers) {
        props.teachers.forEach((teacher) => {
            let record = 0;
            let ok = false;
            let columns_filled = 0;
            do {
                ok = false;
                columnsObj.value.forEach((column) => {
                    if (
                        typeof props.data[teacher.am] !== "undefined" &&
                        typeof (
                            props.data[
                                teacher.am
                            ] as App.Types.AssociativeArray<string>
                        )[column.title] !== "undefined" &&
                        typeof (
                            (
                                props.data[
                                    teacher.am
                                ] as App.Types.AssociativeArray<string>
                            )[
                                column.title
                            ] as App.Types.AssociativeArray<string>
                        )[record] !== "undefined"
                    ) {
                        ok = true;
                        columns_filled++;
                    }
                });

                if (ok) {
                    lines.push({
                        id: i,
                        code: teacher.am,
                        name: teacher.surname + " " + teacher.name,
                        record: record,
                    });
                    i += 1;
                }
                record += 1;
            } while (ok);
        });
        props.other_teachers.forEach((other_teacher) => {
            let record = 0;
            let ok = false;
            let columns_filled = 0;
            do {
                ok = false;
                columnsObj.value.forEach((column) => {
                    if (
                        typeof props.data[other_teacher.employeenumber] !==
                            "undefined" &&
                        typeof (
                            props.data[
                                other_teacher.employeenumber
                            ] as App.Types.AssociativeArray<string>
                        )[column.title] !== "undefined" &&
                        typeof (
                            (
                                props.data[
                                    other_teacher.employeenumber
                                ] as App.Types.AssociativeArray<string>
                            )[
                                column.title
                            ] as App.Types.AssociativeArray<string>
                        )[record] !== "undefined"
                    ) {
                        ok = true;
                        columns_filled++;
                    }
                });

                if (ok) {
                    lines.push({
                        id: i,
                        code: other_teacher.employeenumber,
                        name: other_teacher.name,
                        record: record,
                    });
                    i += 1;
                }
                record += 1;
            } while (ok);
        });
    } else {
        props.schools.forEach((school) => {
            let record = 0;
            let ok = false;
            do {
                ok = false;
                columnsObj.value.forEach((column) => {
                    if (
                        typeof props.data[school.code] !== "undefined" &&
                        typeof (
                            props.data[
                                school.code
                            ] as App.Types.AssociativeArray<string>
                        )[column.title] !== "undefined" &&
                        typeof (
                            (
                                props.data[
                                    school.code
                                ] as App.Types.AssociativeArray<string>
                            )[
                                column.title
                            ] as App.Types.AssociativeArray<string>
                        )[record] !== "undefined"
                    ) {
                        ok = true;
                    }
                });

                if (ok || record == 0) {
                    lines.push({
                        id: i,
                        code: school.code,
                        name: school.name,
                        record: record,
                    });
                    i += 1;
                }
                record += 1;
            } while (ok);
        });
    }
    return lines;
});

const echoData = (
    data: App.Types.AssociativeArray<string>,
    line: LineObject,
    column: ColumnObject
) => {
    return typeof data[line.code] !== "undefined" &&
        typeof (data[line.code] as App.Types.AssociativeArray<string>)[
            column.title
        ] !== "undefined" &&
        typeof (
            (data[line.code] as App.Types.AssociativeArray<string>)[
                column.title
            ] as App.Types.AssociativeArray<string>
        )[line.record] !== "undefined" &&
        typeof (
            (
                (data[line.code] as App.Types.AssociativeArray<string>)[
                    column.title
                ] as App.Types.AssociativeArray<string>
            )[line.record] as App.Types.AssociativeArray<string>
        )["value"] !== "undefined"
        ? ((
              (
                  (data[line.code] as App.Types.AssociativeArray<string>)[
                      column.title
                  ] as App.Types.AssociativeArray<string>
              )[line.record] as App.Types.AssociativeArray<string>
          )["value"] as string)
        : "";
};

const isLink = (
    data: App.Types.AssociativeArray<string>,
    line: LineObject,
    column: ColumnObject
) => {
    return (
        typeof data[line.code] !== "undefined" &&
        typeof (data[line.code] as App.Types.AssociativeArray<string>)[
            column.title
        ] !== "undefined" &&
        typeof (
            (data[line.code] as App.Types.AssociativeArray<string>)[
                column.title
            ] as App.Types.AssociativeArray<string>
        )[line.record] !== "undefined" &&
        typeof (
            (
                (data[line.code] as App.Types.AssociativeArray<string>)[
                    column.title
                ] as App.Types.AssociativeArray<string>
            )[line.record] as App.Types.AssociativeArray<string>
        )["link"] !== "undefined"
    );
};

const getLink = (
    data: App.Types.AssociativeArray<string>,
    line: LineObject,
    column: ColumnObject
) => {
    return typeof (data[line.code] as App.Types.AssociativeArray<string>)[
        column.title
    ] !== "undefined" &&
        typeof (
            (data[line.code] as App.Types.AssociativeArray<string>)[
                column.title
            ] as App.Types.AssociativeArray<string>
        )[line.record] !== "undefined" &&
        typeof (
            (
                (data[line.code] as App.Types.AssociativeArray<string>)[
                    column.title
                ] as App.Types.AssociativeArray<string>
            )[line.record] as App.Types.AssociativeArray<string>
        )["link"] !== "undefined"
        ? ((
              (
                  (data[line.code] as App.Types.AssociativeArray<string>)[
                      column.title
                  ] as App.Types.AssociativeArray<string>
              )[line.record] as App.Types.AssociativeArray<string>
          )["link"] as string)
        : "";
};
</script>
