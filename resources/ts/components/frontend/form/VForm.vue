<script setup lang="ts">
import { route } from "ziggy-js";
import FieldGroup from "./FieldGroup.vue";
import { FieldType } from "@/fieldtype";
import { ref } from "vue";

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
        errors: Record<string, string>;
    }>(),
    {
        record: 0,
        total_records: 1,
        disabled: false,
    }
);

const real_method = props.method?.toLowerCase() === "get" ? "get" : "post";

// Δημιούργησε έναν πίνακα που θα χρησιμοποιηθεί για την παρακολούθηση των αλλαγών
// των τιμών των πεδίων ώστε να μπορέσουμε να εφαρμόσουμε τα κριτήρια εμφάνισης
// πεδίου στη φόρμα.
const field_values = props.form.form_fields
    .map((field) => {
        return {
            [field.id]: ref<string>(
                props.form_data ? props.form_data[field.id] ?? "" : ""
            ),
        };
    })
    .reduce((a, b) => ({ ...a, ...b }), {});
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

                <FieldGroup
                    v-for="field in form.form_fields"
                    :key="field.id"
                    :field="field"
                    :data="(form_data ? form_data[field.id] : '') ?? ''"
                    error=""
                    :disabled="disabled"
                    :old="old[`f${field.id}`] ?? ''"
                    :old_valid="
                        old[`f${field.id}`] === undefined ? false : true
                    "
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
                    :field_values
                />

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
                <button v-if="save" class="btn btn-primary" type="submit">
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
