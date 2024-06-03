import { FormFieldOptions } from "@/fieldtype";
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
    readonly showWhenCriteria: Array<Function> = [];
    readonly fieldVisible: Ref<boolean>;

    constructor(valueChecks: Array<Function>, showWhenChecks: Array<Function>) {
        this.valueChecks = valueChecks;
        this.showWhenCriteria = showWhenChecks;
        this.fieldVisible = ref(this.isVisible());
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
}

export function useOptions(options: FormFieldOptions) {
    const valueChecks: Array<Function> = [];
    const showWhenChecks: Array<Function> = [];

    if (options?.capitals_enabled) {
        valueChecks.push(isUppercase);
    }

    if (options?.greek_enabled) {
        valueChecks.push(isInGreek);
    }

    if (options?.regex_enabled && options?.regex) {
        valueChecks.push(matchesRegex.bind(null, options.regex));
    }

    return new useOptionsObject(valueChecks, showWhenChecks);
}
