import type { useOptionsObject } from "@/components/composables/useOptions";
import type { Ref } from "vue";

export function useTextInputEventHandlers(
    options: useOptionsObject,
    errorRef: Ref<Array<string>>
) {
    function showValueCriteria() {}

    const validateProposedText = (
        proposedNewText: string,
        prevent?: () => void
    ) => {
        const match = options.valueMatch(proposedNewText);
        errorRef.value = match.errorMessages;
        if (!match.result && prevent) {
            prevent();
        }
        return match.result;
    };

    const getCursorState = (target: HTMLInputElement) => {
        const inputText = target.value;
        const cursorPos = target.selectionStart ?? inputText.length;
        const selectionEnd = target.selectionEnd ?? cursorPos;
        return { inputText, cursorPos, selectionEnd };
    };

    const buildProposedNewText = (
        inputText: string,
        cursorPos: number,
        selectionEnd: number,
        insert: string
    ) => `${inputText.slice(0, cursorPos)}${insert}${inputText.slice(selectionEnd)}`;

    const onBeforeInput = (event: InputEvent) => {
        const target = event.target as HTMLInputElement;
        if (!target) return;
        if (event.isComposing) return;

        const { inputText, cursorPos, selectionEnd } = getCursorState(target);

        let proposedNewText = inputText;

        const deleteWordBoundaryBackward = (text: string, pos: number) => {
            if (pos === 0) return 0;
            const before = text.slice(0, pos);
            const withoutTrailing = before.replace(/\s+$/, "");
            const match = withoutTrailing.match(/(\s*\S+)$/);
            return match ? withoutTrailing.length - match[1].length : 0;
        };

        const deleteWordBoundaryForward = (text: string, pos: number) => {
            if (pos >= text.length) return text.length;
            const after = text.slice(pos);
            const match = after.match(/^(\s*\S+)/);
            return match ? pos + match[1].length : text.length;
        };

        const deleteToLineStart = (text: string, pos: number) => {
            const lastLineBreak = text.lastIndexOf("\n", pos - 1);
            return lastLineBreak + 1;
        };

        const deleteToLineEnd = (text: string, pos: number) => {
            const nextLineBreak = text.indexOf("\n", pos);
            return nextLineBreak === -1 ? text.length : nextLineBreak;
        };

        switch (event.inputType) {
            case "insertText":
            case "insertFromPaste":
            case "insertFromDrop":
            case "insertFromComposition":
            case "insertFromYank": {
                const data = event.data ?? "";
                proposedNewText = buildProposedNewText(
                    inputText,
                    cursorPos,
                    selectionEnd,
                    data
                );
                break;
            }
            case "insertReplacementText": {
                const data = event.data ?? "";
                proposedNewText = buildProposedNewText(
                    inputText,
                    cursorPos,
                    selectionEnd,
                    data
                );
                break;
            }
            case "insertLineBreak":
            case "insertParagraph": {
                const insertValue = "\n";
                proposedNewText = buildProposedNewText(
                    inputText,
                    cursorPos,
                    selectionEnd,
                    insertValue
                );
                break;
            }
            case "deleteContentBackward": {
                if (selectionEnd !== cursorPos) {
                    proposedNewText = `${inputText.slice(
                        0,
                        cursorPos
                    )}${inputText.slice(selectionEnd)}`;
                } else if (cursorPos > 0) {
                    proposedNewText = `${inputText.slice(
                        0,
                        cursorPos - 1
                    )}${inputText.slice(selectionEnd)}`;
                }
                break;
            }
            case "deleteContentForward": {
                if (selectionEnd !== cursorPos) {
                    proposedNewText = `${inputText.slice(
                        0,
                        cursorPos
                    )}${inputText.slice(selectionEnd)}`;
                } else {
                    proposedNewText = `${inputText.slice(
                        0,
                        cursorPos
                    )}${inputText.slice(selectionEnd + 1)}`;
                }
                break;
            }
            case "deleteWordBackward": {
                if (selectionEnd !== cursorPos) {
                    proposedNewText = `${inputText.slice(
                        0,
                        cursorPos
                    )}${inputText.slice(selectionEnd)}`;
                } else {
                    const newCursor = deleteWordBoundaryBackward(inputText, cursorPos);
                    proposedNewText = `${inputText.slice(0, newCursor)}${inputText.slice(selectionEnd)}`;
                }
                break;
            }
            case "deleteWordForward": {
                if (selectionEnd !== cursorPos) {
                    proposedNewText = `${inputText.slice(
                        0,
                        cursorPos
                    )}${inputText.slice(selectionEnd)}`;
                } else {
                    const end = deleteWordBoundaryForward(inputText, cursorPos);
                    proposedNewText = `${inputText.slice(0, cursorPos)}${inputText.slice(end)}`;
                }
                break;
            }
            case "deleteSoftLineBackward": {
                if (selectionEnd !== cursorPos) {
                    proposedNewText = `${inputText.slice(
                        0,
                        cursorPos
                    )}${inputText.slice(selectionEnd)}`;
                } else {
                    const newCursor = deleteToLineStart(inputText, cursorPos);
                    proposedNewText = `${inputText.slice(0, newCursor)}${inputText.slice(selectionEnd)}`;
                }
                break;
            }
            case "deleteSoftLineForward": {
                if (selectionEnd !== cursorPos) {
                    proposedNewText = `${inputText.slice(
                        0,
                        cursorPos
                    )}${inputText.slice(selectionEnd)}`;
                } else {
                    const end = deleteToLineEnd(inputText, cursorPos);
                    proposedNewText = `${inputText.slice(0, cursorPos)}${inputText.slice(end)}`;
                }
                break;
            }
            case "deleteEntireSoftLine": {
                const start = deleteToLineStart(inputText, cursorPos);
                const end = deleteToLineEnd(inputText, cursorPos);
                proposedNewText = `${inputText.slice(0, start)}${inputText.slice(end)}`;
                break;
            }
            case "deleteHardLineBackward":
            case "deleteHardLineForward":
            case "deleteByCut":
            case "deleteByDrag": {
                if (selectionEnd !== cursorPos) {
                    proposedNewText = `${inputText.slice(
                        0,
                        cursorPos
                    )}${inputText.slice(selectionEnd)}`;
                }
                break;
            }
            case "deleteContent": {
                // Generic deletion, fallback to validating current value
                proposedNewText = inputText;
                break;
            }
            case "historyUndo":
            case "historyRedo": {
                // Hard to predict the resulting value; validate current one
                proposedNewText = inputText;
                break;
            }
            default:
                // Let the browser handle formatting/history/etc.
                return;
        }

        validateProposedText(proposedNewText, () => event.preventDefault());
    };

    const onKeyPress = (event: KeyboardEvent) => {
        const target = event.target as HTMLInputElement;
        const { inputText, cursorPos, selectionEnd } = getCursorState(target);

        const proposedNewText = buildProposedNewText(
            inputText,
            cursorPos,
            selectionEnd,
            event.key
        );

        validateProposedText(proposedNewText, () => event.preventDefault());
    };

    const onKeyDown = (event: KeyboardEvent) => {
        if (event.key !== "Backspace" && event.key !== "Delete") {
            return;
        }

        const target = event.target as HTMLInputElement;
        const { inputText, cursorPos, selectionEnd } = getCursorState(target);

        let proposedNewText = inputText;

        if (selectionEnd !== cursorPos) {
            proposedNewText = `${inputText.slice(
                0,
                cursorPos
            )}${inputText.slice(selectionEnd)}`;
        } else if (event.key === "Backspace" && cursorPos > 0) {
            proposedNewText = `${inputText.slice(
                0,
                cursorPos - 1
            )}${inputText.slice(selectionEnd)}`;
        } else if (event.key === "Delete") {
            proposedNewText = `${inputText.slice(
                0,
                cursorPos
            )}${inputText.slice(selectionEnd + 1)}`;
        }

        validateProposedText(proposedNewText, () => event.preventDefault());
    };

    const onPaste = (event: ClipboardEvent) => {
        if (!event.clipboardData) return;

        const pastedText = event.clipboardData.getData("text/plain");

        const target = event.target as HTMLInputElement;
        const { inputText, cursorPos, selectionEnd } = getCursorState(target);

        const proposedNewText = buildProposedNewText(
            inputText,
            cursorPos,
            selectionEnd,
            pastedText
        );

        validateProposedText(proposedNewText, () => event.preventDefault());
    };

    return { onBeforeInput, onKeyPress, onKeyDown, onPaste };
}
