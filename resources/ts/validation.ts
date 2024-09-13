export function matchesRegex(
    regex: string,
    regexDescription: string,
    value: string
) {
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
