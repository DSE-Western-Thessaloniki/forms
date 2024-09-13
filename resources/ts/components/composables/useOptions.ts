import {
    FieldType,
    type FormFieldOptions,
    type FormFieldOptionsShowCriteria,
} from "@/fieldtype";
import { useFormStore } from "@/stores/formStore";
import type { Store } from "pinia";
import { nextTick, type Ref, ref, watch } from "vue";

function isUppercase(value: string) {
    const result = value.toUpperCase() === value;
    let errorMsg = "";
    if (!result) {
        errorMsg = "Το πεδίο πρέπει να συμπληρωθεί με κεφαλαίους χαρακτήρες";
    }

    return { result, errorMsg };
}

function isInGreek(value: string) {
    // Κάνε δεκτά τα ελληνικά, τα σύμβολα και τους αριθμούς
    const result =
        !/[^\u0020-\u0040\u005b-\u0060\u007b-\u007e\u0370-\u03ff]/gu.test(
            value
        );
    let errorMsg = "";
    if (!result) {
        errorMsg =
            "Το πεδίο πρέπει να συμπληρωθεί μόνο με ελληνικούς χαρακτήρες";
    }
    console.log(
        "isInGreek: " +
            !/[^\u0020-\u0040\u005b-\u0060\u007b-\u007e\u0370-\u03ff]/gu.test(
                value
            )
    );

    return { result, errorMsg };
}

function matchesRegex(regex: string, regexDescription: string, value: string) {
    const result = new RegExp(regex, "u").test(value);
    let errorMsg = "";
    if (!result) {
        errorMsg = regexDescription;
    }
    console.log(
        "matchesRegex: " +
            new RegExp(regex, "u").test(value) +
            ` regex: ${regex} value: ${value}`
    );

    return { result, errorMsg };
}

function matchesLength(maxLength: string, value: string) {
    const result = value.length <= Number.parseInt(maxLength);
    let errorMsg = "";
    if (!result) {
        errorMsg = `Η τιμή του πεδίου πρέπει να είναι μέχρι ${maxLength} χαρακτήρες`;
    }
    return { result, errorMsg };
}

function isPositive(value: string) {
    const result = Number.parseInt(value) >= 0;
    let errorMsg = "";
    if (!result) {
        errorMsg = `Η τιμή του πεδίου πρέπει να είναι θετική`;
    }
    return { result, errorMsg };
}

export class useOptionsObject {
    readonly valueChecks: Array<
        (value: string) => { result: boolean; errorMsg: string }
    > = [];
    readonly validationChecks: Array<
        (value: string) => { result: boolean; errorMsg: string }
    > = [];
    readonly showWhenCriteria: Array<FormFieldOptionsShowCriteria> = [];
    readonly fieldVisible: Ref<boolean>;
    readonly formStore: ReturnType<typeof useFormStore>;
    readonly options: FormFieldOptions;

    constructor(
        valueChecks: Array<
            (value: string) => { result: boolean; errorMsg: string }
        >,
        validationChecks: Array<
            (value: string) => { result: boolean; errorMsg: string }
        >,
        showWhenChecks: Array<FormFieldOptionsShowCriteria>,
        withWatchers: boolean,
        options: FormFieldOptions
    ) {
        this.valueChecks = valueChecks;
        this.validationChecks = validationChecks;
        this.showWhenCriteria = showWhenChecks;
        this.fieldVisible = ref(false);
        this.formStore = useFormStore();
        this.options = options;

        if (withWatchers) {
            this.addWatchers();
            this.fieldVisible.value = this.isVisible();
        }
    }

    /**
     * Κάνει έλεγχο αν τα κριτήρια για την τιμή του πεδίου ικανοποιούνται
     * @param value Η τιμή του πεδίου που θα χρησιμοποιηθεί για τους ελέγχους
     * @returns true αν τα κριτήρια ικανοποιούνται, false αλλιώς
     */
    valueMatch(
        this: useOptionsObject,
        value: string
    ): { result: boolean; errorMessages: Array<string> } {
        if (this.valueChecks.length === 0) {
            return { result: true, errorMessages: [] };
        }

        let result = true;
        let errorMessages: Array<string> = [];
        this.valueChecks.forEach((check) => {
            const response = check(value);
            result = result && response.result;
            if (!response.result) {
                errorMessages.push(response.errorMsg);
            }
        });

        return { result, errorMessages };
    }

    /**
     * Κάνει έλεγχο αν τα κριτήρια που αφορούν την εμφάνιση του πεδίου ικανοποιούνται
     * @returns true αν ικανοποιούνται, false αλλιώς
     */
    private isVisible(this: useOptionsObject): boolean {
        const returnValues = this.showWhenCriteria.map((criteria) => {
            if (criteria.visible === "always") {
                return true;
            }

            if (criteria.visible === "when_field_is_active") {
                if (typeof criteria.active_field === "undefined") {
                    console.warn(
                        "Δεν βρέθηκε όνομα για το ενεργό πεδίο. Το πεδίο δεν θα εμφανιστεί."
                    );
                    return false;
                }

                let active_field_value =
                    this.formStore.field[criteria.active_field];
                if (
                    this.formStore.fieldType[criteria.active_field] ===
                    FieldType.CheckBoxes
                ) {
                    active_field_value = JSON.parse(
                        active_field_value ?? "[]"
                    ).length;
                }

                if (active_field_value === null) {
                    console.warn(
                        "Δεν βρέθηκε τιμή για το ενεργό πεδίο. Το πεδίο δεν θα εμφανιστεί."
                    );
                    return false;
                }
                return Boolean(active_field_value);
            }

            if (criteria.visible === "when_value") {
                if (typeof criteria.active_field === "undefined") {
                    console.warn(
                        "Δεν βρέθηκε τιμή για το ενεργό πεδίο. Το πεδίο δεν θα εμφανιστεί."
                    );
                    return false;
                }

                if (typeof criteria.value_is === "undefined") {
                    console.warn(
                        "Δεν βρέθηκε τελεστής για το κριτήριο εμφάνισης. Το πεδίο δεν θα εμφανιστεί."
                    );
                    return false;
                }

                const active_field_value =
                    this.formStore.field[criteria.active_field];

                if (active_field_value === null) {
                    console.warn(
                        "Δεν βρέθηκε τιμή για το ενεργό πεδίο. Το πεδίο δεν θα εμφανιστεί."
                    );
                    return false;
                }

                if (typeof criteria.value === "undefined") {
                    console.warn(
                        "Δεν βρέθηκε τιμή για το κριτήριο εμφάνισης. Το πεδίο δεν θα εμφανιστεί."
                    );
                    return false;
                }

                if (criteria.value_is === "eq") {
                    return active_field_value == criteria.value;
                } else if (criteria.value_is === "ne") {
                    return active_field_value != criteria.value;
                } else if (criteria.value_is === "gt") {
                    return active_field_value > criteria.value;
                } else if (criteria.value_is === "ge") {
                    return active_field_value >= criteria.value;
                } else if (criteria.value_is === "lt") {
                    return active_field_value < criteria.value;
                } else if (criteria.value_is === "le") {
                    return active_field_value <= criteria.value;
                } else {
                    console.warn(
                        "Άκυρος τελεστής για το κριτήριο εμφάνισης. Το πεδίο δεν θα εμφανιστεί."
                    );
                    return false;
                }
            }

            console.warn(
                "Άκυρο κριτήριο εμφάνισης. Το πεδίο δεν θα εμφανιστεί."
            );
            return false;
        });

        this.fieldVisible.value = returnValues.reduce(
            (previous, current, index) => {
                const criteria = this.showWhenCriteria[index];

                if (index === 0 || criteria.operator === "and") {
                    return previous && current;
                } else if (criteria.operator === "or") {
                    return previous || current;
                } else {
                    console.warn(
                        `Άκυρος τελεστής για το κριτήριο εμφάνισης '${index}' (${criteria.operator}). Το πεδίο δεν θα εμφανιστεί.`
                    );
                    return false;
                }
            },
            true
        );

        return this.fieldVisible.value;
    }

    private addWatchers(this: useOptionsObject) {
        this.showWhenCriteria.forEach((criteria) => {
            if (criteria.visible === "always") {
                return;
            }

            watch(
                this.formStore.field,
                () => {
                    nextTick(() => {
                        this.isVisible();
                    });
                },
                {
                    deep: true,
                }
            );
        });
    }

    validationCheck(this: useOptionsObject, value: string) {
        if (this.validationChecks.length === 0) {
            return { result: true, errorMessages: [] };
        }

        let result = true;
        let errorMessages: Array<string> = [];
        this.validationChecks.forEach((check) => {
            const response = check(value);
            result = result && response.result;
            if (!response.result) {
                errorMessages.push(response.errorMsg);
            }
        });

        return { result, errorMessages };
    }
}

export function useOptions(
    options: FormFieldOptions,
    withWatchers: boolean = false
): useOptionsObject {
    const valueChecks: Array<
        (value: string) => { result: boolean; errorMsg: string }
    > = [];
    const validationChecks: Array<
        (value: string) => { result: boolean; errorMsg: string }
    > = [];
    let showWhenChecks: Array<FormFieldOptionsShowCriteria> = [];

    if (options?.capitals_enabled) {
        valueChecks.push(isUppercase);
    }

    if (options?.greek_enabled) {
        valueChecks.push(isInGreek);
    }

    if (
        options?.regex_enabled &&
        options?.regex &&
        options?.regex_description
    ) {
        validationChecks.push(
            matchesRegex.bind(null, options.regex, options.regex_description)
        );
    }

    if (options?.field_width_enabled && options?.field_width) {
        valueChecks.push(matchesLength.bind(null, options.field_width));
    }

    if (options?.positive) {
        valueChecks.push(isPositive);
    }

    if (options?.show_when && options?.show_when.length > 0) {
        showWhenChecks = options.show_when;
    }

    return new useOptionsObject(
        valueChecks,
        validationChecks,
        showWhenChecks,
        withWatchers,
        options
    );
}
