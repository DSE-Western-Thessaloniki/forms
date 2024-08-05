<script setup lang="ts">
import { nextTick, onMounted, ref, type Ref } from "vue";
import Toolbar from "./Toolbar.vue";
import { stripHtml } from "string-strip-html";
import { marked } from "marked";

const props = defineProps<{
    name: string;
}>();

const model = defineModel<string>();

if (typeof model.value === "undefined") {
    model.value = ""; // Set initial value if not provided
}

const textToEditorText = (str: string): string => {
    let editorText = document.createElement("div");
    str.split("\n").forEach((paragraph: string) => {
        const div = document.createElement("div");
        let content = stripHtml(paragraph).result;
        if (!content) {
            // Υποχρέωσε τον browser να εμφανίσει το div
            content = "&ZeroWidthSpace;";
            div.innerHTML = content;
        } else {
            div.textContent = content;
        }
        editorText.appendChild(div);
    });

    return editorText.innerHTML;
};

function getRangeSelectedNodes(range: Range) {
    let node: Node | null = range.startContainer;
    let endNode = range.endContainer;

    // Special case for a range that is contained within a single node
    if (node == endNode) {
        return [node];
    }

    // Iterate nodes until we hit the end container
    let rangeNodes = [node];
    console.log(node, node.parentNode);
    node = node.parentNode;
    while (node && node.childNodes[0] != endNode) {
        node = node.nextSibling;
        if (node) {
            rangeNodes.push(node.childNodes[0]);
        }
    }

    // // Add partially selected nodes at the start of the range
    // node = range.startContainer;
    // while (node && node != range.commonAncestorContainer) {
    //     rangeNodes.unshift(node);
    //     node = node.parentNode;
    // }

    return rangeNodes;
}

const surroundWithMarker = (marker: string) => {
    const selection = document.getSelection();
    if (!selection) return;

    for (let i = 0; i < selection.rangeCount; i++) {
        const sel = selection.getRangeAt(i);
        const nodes = getRangeSelectedNodes(sel);

        if (nodes.length === 1) {
            let container = nodes[0];

            // Κάνε έλεγχο αν η επιλογή είναι μέσα στο κειμενογράφο και όχι οπουδήποτε στη σελίδα
            if (container.parentElement?.parentElement === mainEditor.value) {
                container.textContent =
                    container.textContent!.slice(0, sel.startOffset) +
                    marker +
                    container.textContent!.slice(
                        sel.startOffset,
                        sel.endOffset
                    ) +
                    marker +
                    container.textContent!.slice(sel.endOffset);
            }
        } else {
            const startingContainer = nodes[0];
            const endingContainer = nodes[nodes.length - 1];

            // Κάνε έλεγχο αν η επιλογή είναι μέσα στο κειμενογράφο και όχι οπουδήποτε στη σελίδα
            if (
                startingContainer.parentElement?.parentElement ===
                    mainEditor.value &&
                endingContainer.parentElement?.parentElement ===
                    mainEditor.value
            ) {
                startingContainer.textContent =
                    startingContainer.textContent?.slice(0, sel.startOffset) +
                    marker +
                    startingContainer.textContent!.slice(sel.startOffset);
                endingContainer.textContent =
                    endingContainer.textContent?.slice(0, sel.endOffset) +
                    marker +
                    endingContainer.textContent!.slice(sel.endOffset);
            }
        }
    }
};

const toggleParagraphMarker = (regex: RegExp, marker: string) => {
    const selection = document.getSelection();
    if (!selection) return;

    for (let i = 0; i < selection.rangeCount; i++) {
        const sel = selection.getRangeAt(i);
        const nodes = getRangeSelectedNodes(sel);

        nodes.forEach((node) => {
            // Κάνε έλεγχο αν η επιλογή είναι μέσα στο κειμενογράφο και όχι οπουδήποτε στη σελίδα
            if (node.parentElement?.parentElement === mainEditor.value) {
                const text = node.textContent ?? "";
                const existingHeading = regex.exec(text);
                if (existingHeading) {
                    node.textContent = text.slice(existingHeading[0].length);
                } else {
                    node.textContent = `${marker} ` + node.textContent;
                }
            }
        });
    }
};

const onBoldPressed = () => {
    surroundWithMarker("**");
};

const onItalicPressed = () => {
    surroundWithMarker("*");
};

const onHeadingPressed = () => {
    const selection = document.getSelection();
    if (!selection) return;

    for (let i = 0; i < selection.rangeCount; i++) {
        const sel = selection.getRangeAt(i);
        const nodes = getRangeSelectedNodes(sel);

        nodes.forEach((node) => {
            // Κάνε έλεγχο αν η επιλογή είναι μέσα στο κειμενογράφο και όχι οπουδήποτε στη σελίδα
            if (node.parentElement?.parentElement === mainEditor.value) {
                const text = node.textContent ?? "";
                const existingHeading = /^\#+ /.exec(text);
                if (existingHeading) {
                    let newHeading = "";
                    if (existingHeading[0].length < 7) {
                        newHeading = "#" + existingHeading[0];
                    }

                    node.textContent =
                        newHeading + text.slice(existingHeading[0].length);
                } else {
                    node.textContent = "# " + node.textContent;
                }
            }
        });
    }
};

const onQuotePressed = () => {
    toggleParagraphMarker(/^> /, ">");
};

const onUlListPressed = () => {
    toggleParagraphMarker(/^- /, "-");
};

const onOlListPressed = () => {
    toggleParagraphMarker(/^1\. /, "1.");
};

const onLinkPressed = () => {
    const selection = document.getSelection();
    if (!selection) return;

    for (let i = 0; i < selection.rangeCount; i++) {
        const sel = selection.getRangeAt(i);
        const nodes = getRangeSelectedNodes(sel);

        if (nodes.length === 1) {
            let container = nodes[0];

            // Κάνε έλεγχο αν η επιλογή είναι μέσα στο κειμενογράφο και όχι οπουδήποτε στη σελίδα
            if (container.parentElement?.parentElement === mainEditor.value) {
                container.textContent =
                    container.textContent!.slice(0, sel.startOffset) +
                    "[" +
                    container.textContent!.slice(
                        sel.startOffset,
                        sel.endOffset
                    ) +
                    "](http://)" +
                    container.textContent!.slice(sel.endOffset);
            }
        } else {
            const startingContainer = nodes[0];
            const endingContainer = nodes[nodes.length - 1];

            // Κάνε έλεγχο αν η επιλογή είναι μέσα στο κειμενογράφο και όχι οπουδήποτε στη σελίδα
            if (
                startingContainer.parentElement?.parentElement ===
                    mainEditor.value &&
                endingContainer.parentElement?.parentElement ===
                    mainEditor.value
            ) {
                startingContainer.textContent =
                    startingContainer.textContent?.slice(0, sel.startOffset) +
                    "[" +
                    startingContainer.textContent!.slice(sel.startOffset);
                endingContainer.textContent =
                    endingContainer.textContent?.slice(0, sel.endOffset) +
                    "](http://)" +
                    endingContainer.textContent!.slice(sel.endOffset);
            }
        }
    }
};

const onImagePressed = () => {
    const selection = document.getSelection();
    if (!selection) return;

    for (let i = 0; i < selection.rangeCount; i++) {
        const sel = selection.getRangeAt(i);
        const nodes = getRangeSelectedNodes(sel);

        if (nodes.length === 1) {
            let container = nodes[0];

            // Κάνε έλεγχο αν η επιλογή είναι μέσα στο κειμενογράφο και όχι οπουδήποτε στη σελίδα
            if (container.parentElement?.parentElement === mainEditor.value) {
                container.textContent =
                    container.textContent!.slice(0, sel.startOffset) +
                    "![" +
                    container.textContent!.slice(
                        sel.startOffset,
                        sel.endOffset
                    ) +
                    "](http://)" +
                    container.textContent!.slice(sel.endOffset);
            }
        } else {
            const startingContainer = nodes[0];
            const endingContainer = nodes[nodes.length - 1];

            // Κάνε έλεγχο αν η επιλογή είναι μέσα στο κειμενογράφο και όχι οπουδήποτε στη σελίδα
            if (
                startingContainer.parentElement?.parentElement ===
                    mainEditor.value &&
                endingContainer.parentElement?.parentElement ===
                    mainEditor.value
            ) {
                startingContainer.textContent =
                    startingContainer.textContent?.slice(0, sel.startOffset) +
                    "![" +
                    startingContainer.textContent!.slice(sel.startOffset);
                endingContainer.textContent =
                    endingContainer.textContent?.slice(0, sel.endOffset) +
                    "](http://)" +
                    endingContainer.textContent!.slice(sel.endOffset);
            }
        }
    }
};

const onPreviewPressed = () => {};

const onHelpPressed = () => {
    window.open(
        "https://docs.github.com/en/get-started/writing-on-github/getting-started-with-writing-and-formatting-on-github/basic-writing-and-formatting-syntax",
        "_blank"
    );
};

const updateModel = (event: Event) => {
    model.value = (event.target as HTMLElement).innerText.replace(
        /\u200B/g,
        ""
    );
    console.log("updateModel:\n", model.value);
};

const textToPreviewText = (text: string) => {
    console.log("textToPreviewText:\n", text);
    return marked.parse(text, {
        gfm: true,
    });
};

const text = model.value;
const mainEditor: Ref<HTMLElement | null> = ref(null);
</script>

<template>
    <div class="d-flex flex-column">
        <Toolbar
            @boldPressed="onBoldPressed"
            @italicPressed="onItalicPressed"
            @headingPressed="onHeadingPressed"
            @quotePressed="onQuotePressed"
            @ulListPressed="onUlListPressed"
            @olListPressed="onOlListPressed"
            @linkPressed="onLinkPressed"
            @imagePressed="onImagePressed"
            @previewPressed="onPreviewPressed"
            @helpPressed="onHelpPressed"
        ></Toolbar>
        <div
            class="main-editor"
            v-html="textToEditorText(text)"
            contenteditable="true"
            @input="updateModel"
            @blur="updateModel"
            ref="mainEditor"
        ></div>
        <textarea
            :name="name"
            rows="12"
            class="flex-fill rounded-bottom"
            v-model="model"
            hidden
        ></textarea>
        <div name="preview" class="preview">
            <div class="preview-title">Προεπισκόπηση:</div>
            <div
                class="preview-markdown"
                v-html="textToPreviewText(model ?? '')"
            ></div>
        </div>
    </div>
</template>
<style lang="css">
.main-editor {
    flex-grow: 1;
    padding: 1em;
    background-color: white;
    border: 1px solid gray;
    border-bottom-left-radius: 3px;
    border-bottom-right-radius: 3px;
}
.main-editor:focus {
    outline: none;
}

.preview {
    border-bottom-left-radius: 3px;
    border-bottom-right-radius: 3px;
    padding: 0.5em;
    margin-top: 1em;
}
.preview-title {
    background-color: lightblue;
    padding: 0.5em;
    border: 1px solid black;
}
.preview-markdown {
    padding: 0.5em;
    background-color: lightyellow;
}
</style>
