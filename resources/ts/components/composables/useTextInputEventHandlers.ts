import type { useOptionsObject } from "@/components/composables/useOptions";

export function useTextInputEventHandlers(options: useOptionsObject) {
    const onKeyPress = (event: KeyboardEvent) => {
        const target = event.target as HTMLInputElement;
        const cursorPos = target.selectionStart ?? 0;
        const selectionEnd = target.selectionEnd ?? cursorPos;
        const inputText = target.value;

        const proposedNewText = `${inputText.slice(0, cursorPos)}${
            event.key
        }${inputText.slice(selectionEnd, inputText.length)}`;

        if (!options.valueMatch(proposedNewText)) {
            event.preventDefault();
        }
    };

    const onPaste = (event: ClipboardEvent) => {
        event.preventDefault();
        if (!event.clipboardData) return;

        const pastedText = event.clipboardData.getData("text/plain");

        const target = event.target as HTMLInputElement;
        const cursorPos = target.selectionStart ?? 0;
        const selectionEnd = target.selectionEnd ?? cursorPos;
        const inputText = target.value;

        const proposedNewText = `${inputText.slice(
            0,
            cursorPos
        )}${pastedText}${inputText.slice(selectionEnd, inputText.length)}`;

        if (!options.valueMatch(proposedNewText)) {
            return;
        }

        target.value = proposedNewText;
        target.selectionStart = pastedText.length + cursorPos;
        target.selectionEnd = pastedText.length + cursorPos;
    };

    return { onKeyPress, onPaste };
}
