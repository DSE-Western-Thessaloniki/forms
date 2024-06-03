import { FormFieldOptions } from "@/fieldtype";

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
    functionSet: Array<Function> = [];

    constructor(checks: Array<Function>) {
        this.functionSet = checks;
    }

    match(this: useOptionsObject, value: string) {
        if (this.functionSet.length === 0) {
            return true;
        }

        return this.functionSet.every((check) => check(value));
    }
}

export function useOptions(options: FormFieldOptions) {
    const checks: Array<Function> = [];

    if (options?.capitals_enabled) {
        checks.push(isUppercase);
    }

    if (options?.greek_enabled) {
        checks.push(isInGreek);
    }

    if (options?.regex_enabled && options?.regex) {
        checks.push(matchesRegex.bind(null, options.regex));
    }

    return new useOptionsObject(checks);
}
