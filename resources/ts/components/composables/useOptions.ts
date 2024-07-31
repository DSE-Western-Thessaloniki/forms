import type {
    FormFieldOptions,
    FormFieldOptionsShowCriteria,
} from "@/fieldtype";
import { useFormStore } from "@/stores/formStore";
import type { Store } from "pinia";
import { type Ref, ref } from "vue";

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

export class useOptionsObject {
    readonly valueChecks: Array<
        (value: string) => { result: boolean; errorMsg: string }
    > = [];
    readonly showWhenCriteria: Array<FormFieldOptionsShowCriteria> = [];
    readonly fieldVisible: Ref<boolean>;
    readonly formStore: Store<
        "formStore",
        {
            field: Record<string, string | null>;
        }
    >;

    constructor(
        valueChecks: Array<
            (value: string) => { result: boolean; errorMsg: string }
        >,
        showWhenChecks: Array<FormFieldOptionsShowCriteria>,
        withWatchers: boolean
    ) {
        this.valueChecks = valueChecks;
        this.showWhenCriteria = showWhenChecks;
        this.fieldVisible = ref(false);
        this.formStore = useFormStore();

        if (withWatchers) {
            this.addWatchers();
        }

        // Πρόσθεσε hooks στα events των πεδίων που απαιτείται να παρακολουθούμε
        // window.addEventListener("load", () => {
        //     this.addHooks();
        //     this.fieldVisible.value = this.isVisible();
        // });
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
                if (criteria.active_field === undefined) {
                    console.warn(
                        "Δεν βρέθηκε όνομα για το ενεργό πεδίο. Το πεδίο δεν θα εμφανιστεί."
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
                return Boolean(active_field_value);
            }

            if (criteria.visible === "when_value") {
                if (criteria.active_field === undefined) {
                    console.warn(
                        "Δεν βρέθηκε τιμή για το ενεργό πεδίο. Το πεδίο δεν θα εμφανιστεί."
                    );
                    return false;
                }

                if (criteria.value_is === undefined) {
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

                if (criteria.operator === "and") {
                    return previous && current;
                } else if (criteria.operator === "or") {
                    return previous || current;
                } else {
                    console.warn(
                        "Άκυρος τελεστής για το κριτήριο εμφάνισης. Το πεδίο δεν θα εμφανιστεί."
                    );
                    return false;
                }
            },
            true
        );

        return this.fieldVisible.value;
    }

    private addWatchers(this: useOptionsObject) {}

    /**
     * Πρόσθεσε hooks στα events των πεδίων που απαιτείται να παρακολουθούμε
     */
    private addHooks() {
        this.showWhenCriteria.forEach((criteria) => {
            if (criteria.visible === "always") {
                return;
            }

            if (criteria.active_field === undefined) {
                console.warn(
                    "Δεν βρέθηκε τιμή για το ενεργό πεδίο. Το πεδίο δεν θα εμφανιστεί."
                );
                return;
            }

            const input = document.querySelector(
                `[name='${criteria.active_field}']`
            );

            if (input === null) {
                console.warn(
                    `Δεν βρέθηκε το πεδίο ${criteria.active_field}. Το πεδίο δεν θα εμφανιστεί.`
                );
                return;
            }

            input.addEventListener("change", (event) => this.isVisible());
        });
    }
}

export function useOptions(
    options: FormFieldOptions,
    withWatchers: boolean = false
): useOptionsObject {
    const valueChecks: Array<
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
        valueChecks.push(
            matchesRegex.bind(null, options.regex, options.regex_description)
        );
    }

    if (options?.field_width_enabled && options?.field_width) {
        valueChecks.push(matchesLength.bind(null, options.field_width));
    }

    if (options?.show_when && options?.show_when.length > 0) {
        showWhenChecks = options.show_when;
    }

    return new useOptionsObject(valueChecks, showWhenChecks, withWatchers);
}
