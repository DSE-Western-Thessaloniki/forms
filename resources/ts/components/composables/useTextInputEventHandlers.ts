import type { useOptionsObject } from "@/components/composables/useOptions";
import type { Ref } from "vue";

export function useTextInputEventHandlers(
    options: useOptionsObject,
    errorRef: Ref<Array<string>>
) {
    function showValueCriteria() {}

    const onKeyPress = (event: KeyboardEvent) => {
        const target = event.target as HTMLInputElement;
        const cursorPos = target.selectionStart ?? 0;
        const selectionEnd = target.selectionEnd ?? cursorPos;
        const inputText = target.value;

        const proposedNewText = `${inputText.slice(0, cursorPos)}${
            event.key
        }${inputText.slice(selectionEnd, inputText.length)}`;

        const match = options.valueMatch(proposedNewText);
        errorRef.value = match.errorMessages;
        if (!match.result) {
            event.preventDefault();
        }
    };

    const onKeyDown = (event: KeyboardEvent) => {
        // Εδώ θα χειριστούμε μόνο τους μη εκτυπώσιμους χαρακτήρες (Delete, Backspace, ...)
        const target = event.target as HTMLInputElement;
        const cursorPos = target.selectionStart ?? 0;
        const selectionEnd = target.selectionEnd ?? cursorPos;
        const inputText = target.value;

        let proposedNewText = inputText;

        if (
            selectionEnd !== cursorPos &&
            (event.key === "Backspace" || event.key === "Delete")
        ) {
            proposedNewText = `${inputText.slice(
                0,
                cursorPos
            )}${inputText.slice(selectionEnd, inputText.length)}`;
            console.log(`New text: '${proposedNewText}'`);
        } else if (event.key === "Backspace") {
            proposedNewText = `${inputText.slice(
                0,
                cursorPos - 1
            )}${inputText.slice(selectionEnd, inputText.length)}`;
            console.log(`New text: '${proposedNewText}'`);
        } else if (event.key === "Delete") {
            proposedNewText = `${inputText.slice(
                0,
                cursorPos
            )}${inputText.slice(selectionEnd + 1, inputText.length)}`;
            console.log(`New text: '${proposedNewText}'`);
        }

        if (
            ["Delete", "Backspace"].every((item) => {
                return item !== event.key;
            })
        ) {
            return;
        }

        const match = options.valueMatch(proposedNewText);
        errorRef.value = match.errorMessages;
    };

    const onPaste = (event: ClipboardEvent) => {
        if (!event.clipboardData) return;

        const pastedText = event.clipboardData.getData("text/plain");

        const target = event.target as HTMLInputElement;

        if (
            !["text", "search", "url", "tel", "password"].includes(target.type)
        ) {
            // Δεν είναι διαθέσιμο το selectionStart & selectionEnd
            const match = options.valueMatch(pastedText);
            errorRef.value = match.errorMessages;
            if (!match.result) {
                event.preventDefault();
                return;
            }

            return;
        }

        const cursorPos = target.selectionStart ?? 0;
        const selectionEnd = target.selectionEnd ?? cursorPos;
        const inputText = target.value;

        const proposedNewText = `${inputText.slice(
            0,
            cursorPos
        )}${pastedText}${inputText.slice(selectionEnd, inputText.length)}`;

        const match = options.valueMatch(proposedNewText);
        errorRef.value = match.errorMessages;

        if (!match.result) {
            event.preventDefault();
            return;
        }
    };

    return { onKeyPress, onKeyDown, onPaste };
}
