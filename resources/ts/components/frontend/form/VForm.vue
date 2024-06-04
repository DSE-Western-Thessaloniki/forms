<script setup lang="ts">
import FieldGroup from "./FieldGroup.vue";

const props = withDefaults(
    defineProps<{
        form_fields: Array<App.Models.FormField>;
        acting_as: string;
        method?: "get" | "post" | "put" | "delete" | "patch";
        multiple?: boolean | number;
        record?: number;
        total_records?: number;
    }>(),
    {
        record: 0,
        total_records: 0,
        multiple: false,
    }
);

const real_method = props.method?.toLowerCase() === "get" ? "get" : "post";
</script>

<template>
    <form class="container" :method="method" enctype="multipart/form-data">
        <h1><slot name="title"></slot></h1>
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
                    v-for="field in form_fields"
                    :key="field.id"
                    :field="field"
                    accept=""
                    route=""
                    data=""
                    old=""
                    :old_valid="false"
                    error=""
                    :disabled="false"
                />

                <!-- Αν επιτρέπονται πολλαπλές εγγραφές -->
                <nav v-if="multiple">
                    <ul class="pagination justify-content-center">
                        <li
                            class="page-item"
                            :class="record > 0 ? '' : 'disabled'"
                        >
                            <button
                                class="page-link"
                                type="submit"
                                formaction="{{ route('report.edit.record.update', [$form->id, $record, $record > 0 ? $record - 1 : 0]) }}"
                                formmethod="post"
                                :tabindex="record > 0 ? -1 : undefined"
                                :aria-disabled="record > 0 ? true : undefined"
                            >
                                <i class="fas fa-fw fa-fas fa-chevron-left"></i>
                            </button>
                        </li>
                        <!-- prettier-ignore-attribute v-for -->
                        <li
                            v-for="i in (total_records + 1)"
                            class="page-item"
                            :class="i == record + 1 ? 'active' : ''"
                        >
                            <button
                                v-if="i == record + 1"
                                type="button"
                                class="page-link"
                            >
                                {{ i }}
                            </button>
                            <button
                                v-else
                                class="page-link"
                                type="submit"
                                formaction="{{ route('report.edit.record.update', [$form->id, $record, $i]) }}"
                                formmethod="post"
                            >
                                {{ i }}
                            </button>
                        </li>
                        <li
                            class="page-item"
                            :class="record < total_records ? '' : 'disabled'"
                        >
                            <button
                                class="page-link"
                                type="submit"
                                formaction="{{ route('report.edit.record.update', [$form->id, $record, $record < $total_records ? $record + 1 : $total_records]) }}"
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
                                formaction="{{ route('report.edit.record.update', [$form->id, $record, 'new']) }}"
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
                <a class="btn btn-danger" href="{{ route('report.index') }}"
                    >Ακύρωση</a
                >
            </div>
            <div class="col d-flex justify-content-end">
                @method('PUT') @if ($save)
                <button class="btn btn-primary" type="submit">
                    Αποθήκευση
                </button>
                @endif
            </div>
        </div>
        @csrf
    </form>
</template>
