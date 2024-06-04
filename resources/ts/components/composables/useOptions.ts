import { FormFieldOptions, FormFieldOptionsShowCriteria } from "@/fieldtype";
import { Ref, ref } from "vue";

function isUppercase(value: string) {
    // console.log("isUppercase: " + (value.toUpperCase() === value));
    return value.toUpperCase() === value;
}

function isInGreek(value: string) {
    // Κάνε δεκτά τα ελληνικά, τα σύμβολα και τους αριθμούς
    // console.log(
    //     "isInGreek: " +
    //         !/[^\u0020-\u0040\u005b-\u0060\u007b-\u007e\u0370-\u03ff]/gu.test(
    //             value
    //         )
    // );
    return !/[^\u0020-\u0040\u005b-\u0060\u007b-\u007e\u0370-\u03ff]/gu.test(
        value
    );
}

function matchesRegex(regex: string, value: string) {
    // console.log(
    //     "matchesRegex: " +
    //         new RegExp(regex, "u").test(value) +
    //         ` regex: ${regex} value: ${value}`
    // );
    return new RegExp(regex, "u").test(value);
}

class useOptionsObject {
    readonly valueChecks: Array<Function> = [];
    readonly showWhenCriteria: Array<FormFieldOptionsShowCriteria> = [];
    readonly fieldVisible: Ref<boolean>;

    constructor(
        valueChecks: Array<Function>,
        showWhenChecks: Array<FormFieldOptionsShowCriteria>
    ) {
        this.valueChecks = valueChecks;
        this.showWhenCriteria = showWhenChecks;
        this.fieldVisible = ref(false);

        // Πρόσθεσε hooks στα events των πεδίων που απαιτείται να παρακολουθούμε
        window.addEventListener("load", () => {
            this.addHooks();
            this.fieldVisible.value = this.isVisible();
        });
    }

    /**
     * Κάνει έλεγχο αν τα κριτήρια για την τιμή του πεδίου ικανοποιούνται
     * @param value Η τιμή του πεδίου που θα χρησιμοποιηθεί για τους ελέγχους
     * @returns true αν τα κριτήρια ικανοποιούνται, false αλλιώς
     */
    valueMatch(this: useOptionsObject, value: string): boolean {
        if (this.valueChecks.length === 0) {
            return true;
        }

        return this.valueChecks.every((check) => check(value));
    }

    /**
     * Κάνει έλεγχο αν τα κριτήρια που αφορούν την εμφάνιση του πεδίου ικανοποιούνται
     * @returns true αν ικανοποιούνται, false αλλιώς
     */
    private isVisible(this: useOptionsObject): boolean {
        return true;
    }

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

export function useOptions(options: FormFieldOptions) {
    const valueChecks: Array<Function> = [];
    let showWhenChecks: Array<FormFieldOptionsShowCriteria> = [];

    if (options?.capitals_enabled) {
        valueChecks.push(isUppercase);
    }

    if (options?.greek_enabled) {
        valueChecks.push(isInGreek);
    }

    if (options?.regex_enabled && options?.regex) {
        valueChecks.push(matchesRegex.bind(null, options.regex));
    }

    if (options?.show_when && options?.show_when.length > 0) {
        showWhenChecks = options.show_when;
    }

    return new useOptionsObject(valueChecks, showWhenChecks);
}
