<script setup lang="ts">
import { route } from "ziggy-js";
import FieldGroup from "./FieldGroup.vue";
import { FieldType } from "@/fieldtype";
import { computed, ref, watch, watchEffect, type Ref } from "vue";
import { useFormStore } from "@/stores/formStore";
import { useTemplateRefsList } from "@vueuse/core";
import { useOptions } from "@/components/composables/useOptions";

const props = withDefaults(
    defineProps<{
        action: string;
        form: App.Models.Form & {
            form_fields: Array<App.Models.FormField & { accepted?: string }>;
        };
        acting_as: string;
        method?: "get" | "post" | "put" | "delete" | "patch";
        record?: number;
        total_records?: number;
        form_data?: Record<string, string | null>;
        save: boolean;
        disabled?: boolean;
        old: Record<string, string>;
        errors: string;
    }>(),
    {
        record: 0,
        total_records: 1,
        disabled: false,
        errors: "{}",
    }
);

const real_method = props.method?.toLowerCase() === "get" ? "get" : "post";

// Δημιούργησε έναν πίνακα που θα χρησιμοποιηθεί για την παρακολούθηση των αλλαγών
// των τιμών των πεδίων ώστε να μπορέσουμε να εφαρμόσουμε τα κριτήρια εμφάνισης
// πεδίου στη φόρμα.
const field_values = props.form.form_fields
    .map((field) => {
        return {
            [field.id]: props.form_data ? props.form_data[field.id] ?? "" : "",
        };
    })
    .reduce((a, b) => ({ ...a, ...b }), {});

const formStore = useFormStore();
Object.entries(field_values).forEach(([key, value]) => {
    formStore.field[key] = value;
});

// Πέρασε και τους τύπους για επιπλέον ελέγχους
const field_types = props.form.form_fields
    .map((field) => {
        return {
            [field.id]: field.type,
        };
    })
    .reduce((a, b) => ({ ...a, ...b }), {});

Object.entries(field_types).forEach(([key, value]) => {
    formStore.fieldType[key] = value;
});

const fieldErrors = JSON.parse(props.errors);
console.log("Errors:", props.errors);
console.log("FieldErrors:", fieldErrors);

const fieldGroupRefs = useTemplateRefsList();

// Η κατάσταση του κάθε πεδίου της φόρμας (0 κανένα λάθος, 1 υπάρχει σφάλμα)
const validationStatus = ref(
    props.form.form_fields.map(() => {
        return 0;
    })
);

const validation_errors = computed(() => {
    return validationStatus.value.reduce(
        (previous, current) => previous + current,
        0
    );
});

const changeValidationStatus = (el: HTMLDivElement | null, value: number) => {
    fieldGroupRefs.value.forEach((field, index) => {
        if (field.contains(el)) {
            validationStatus.value[index] = value;
        }
    });
    console.log("Form.changeValidationStatus:", value, el);
};

const clearValidationStatus = (id: number) => {
    console.log("Here!");
    const idx = props.form.form_fields.findIndex((field) => field.id == id);
    console.log(`Index: ${idx}`);
    validationStatus.value[idx] = 0;
    checkSaveStatus();
};

formStore.fieldOptions = props.form.form_fields
    .map((field) => {
        const fieldOptions = JSON.parse(field.options);
        return {
            [`${field.id}`]: useOptions(fieldOptions, true),
        };
    })
    .reduce((a, b) => ({ ...a, ...b }), {});

const saveDisabled = ref(false);

const checkSaveStatus = () => {
    if (validation_errors.value > 0) {
        console.log(
            "Save disabled due to validation errors: ",
            validation_errors.value,
            validationStatus.value
        );
        saveDisabled.value = true;
        return;
    }

    let result = false;
    props.form.form_fields.forEach((field) => {
        if (
            formStore.fieldOptions[field.id].fieldVisible &&
            props.form.form_fields.find((item) => item.id == field.id)
                ?.required &&
            ((field.type !== FieldType.CheckBoxes &&
                !`${formStore.field[field.id]}`.length) ||
                // Πρόσθεσε έλεγχο για υποχρεωτική επιλογή checkboxes
                (field.type === FieldType.CheckBoxes &&
                    (`${formStore.field[field.id]}` === "[]" ||
                        !`${formStore.field[field.id]}`.length)))
        ) {
            console.log("Save disabled due to visible field: " + field.id);
            result = true;
        }
    });

    saveDisabled.value = result;
};

checkSaveStatus();

const watchers = Object.entries(formStore.fieldOptions).map((item) => item[1]);

watch([validation_errors, ...watchers], checkSaveStatus, { deep: true });

console.log("Old:", props.old);
props.form.form_fields.forEach((field) => {
    if (typeof props.old[`f${field.id}`] !== "undefined") {
        console.log("Setting old value for field:", field.id);
        formStore.field[field.id] = props.old[`f${field.id}`];
    }
});
</script>

<template>
    <form
        class="container"
        :method="real_method"
        enctype="multipart/form-data"
        :action="action"
    >
        <h1>{{ form.title }}</h1>
        <h3><slot name="description"></slot></h3>
        <hr />
        <div class="card">
            <div class="card-header">
                Συμπλήρωση φόρμας ως
                <span class="h5 fw-bold">{{ acting_as }}</span>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    To <span class="text-danger">*</span> σηματοδοτεί
                    υποχρεωτικά πεδία.
                </div>

                <div
                    v-for="field in form.form_fields"
                    :key="field.id"
                    :ref="fieldGroupRefs.set"
                >
                    <FieldGroup
                        :key="field.id"
                        :field="field"
                        :errors="
                            `f${field.id}` in fieldErrors
                                ? fieldErrors[`f${field.id}`]
                                : []
                        "
                        :disabled="disabled"
                        :accept="
                            field.type === FieldType.File
                                ? field.accepted ?? ''
                                : ''
                        "
                        :route="
                            field.type === FieldType.File
                                ? route('report.download', [
                                      form.id,
                                      field.id,
                                      record ?? 0,
                                  ])
                                : ''
                        "
                        @validationChanged="changeValidationStatus"
                        @clearValidation="clearValidationStatus"
                    />
                </div>

                <!-- Αν επιτρέπονται πολλαπλές εγγραφές -->
                <nav v-if="form.multiple">
                    <ul class="pagination justify-content-center">
                        <li
                            class="page-item"
                            :class="record > 0 ? '' : 'disabled'"
                        >
                            <button
                                class="page-link"
                                type="submit"
                                :formaction="
                                    route('report.edit.record.update', [
                                        form.id,
                                        record,
                                        record > 0 ? record - 1 : 0,
                                    ])
                                "
                                formmethod="post"
                                :tabindex="record > 0 ? -1 : undefined"
                                :aria-disabled="record > 0 ? true : undefined"
                            >
                                <i class="fas fa-fw fa-fas fa-chevron-left"></i>
                            </button>
                        </li>
                        <!-- prettier-ignore-attribute :class -->
                        <li
                            v-for="i in total_records"
                            class="page-item"
                            :class="((i - 1) == record) ? 'active' : ''"
                        >
                            <!-- prettier-ignore-attribute v-if -->
                            <button
                                v-if="i == (record + 1)"
                                type="button"
                                class="page-link"
                            >
                                {{ i }}
                            </button>
                            <button
                                v-else
                                class="page-link"
                                type="submit"
                                :formaction="
                                    route('report.edit.record.update', [
                                        form.id,
                                        record,
                                        i - 1,
                                    ])
                                "
                                formmethod="post"
                            >
                                {{ i }}
                            </button>
                        </li>
                        <li
                            class="page-item"
                            :class="
                                record < total_records - 1 ? '' : 'disabled'
                            "
                        >
                            <button
                                class="page-link"
                                type="submit"
                                :formaction="
                                    route('report.edit.record.update', [
                                        form.id,
                                        record,
                                        record < total_records
                                            ? record + 1
                                            : total_records,
                                    ])
                                "
                                formmethod="post"
                                :tabindex="
                                    record >= total_records ? -1 : undefined
                                "
                                :aria-disabled="
                                    record >= total_records ? true : undefined
                                "
                            >
                                <i class="fas fa-fw fa-fas fa-chevron-right">
                                </i>
                            </button>
                        </li>
                        <li class="page-item">
                            <button
                                class="page-link"
                                type="submit"
                                :formaction="
                                    route('report.edit.record.update', [
                                        form.id,
                                        record,
                                        'new',
                                    ])
                                "
                            >
                                <i class="fas fa-fw fa-fas fa-asterisk"></i> Νέα
                                εγγραφή
                            </button>
                        </li>
                    </ul>
                </nav>
                <hr />
            </div>
        </div>
        <hr />
        <div class="form-group row mb-3">
            <div class="col-2">
                <a class="btn btn-danger" :href="route('report.index')"
                    >Ακύρωση</a
                >
            </div>
            <div class="col d-flex justify-content-end">
                <button
                    v-if="save"
                    class="btn btn-primary"
                    type="submit"
                    :disabled="saveDisabled"
                >
                    Αποθήκευση
                </button>
            </div>
        </div>
        <input
            v-if="
                typeof method != 'undefined' &&
                ['post', 'put', 'patch'].includes(method)
            "
            type="hidden"
            name="_method"
            :value="method"
        />
        <slot name="csrf_token"></slot>
    </form>
</template>
