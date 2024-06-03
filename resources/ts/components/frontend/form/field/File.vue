<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        field: App.Models.FormField;
        data: unknown;
        disabled?: boolean;
        old: unknown;
        old_valid: boolean;
        error: string;
        route?: string;
        accept: string;
    }>(),
    {
        disabled: false,
        error: "",
    }
);

const uploadedFile = () => {
    if (
        props.old_valid &&
        props.old !== undefined &&
        typeof props.old === "string"
    ) {
        return props.old;
    } else if (props.data !== undefined && typeof props.data === "string") {
        return props.data;
    } else {
        console.log(props.old);
        console.log(props.data);
        console.warn(
            "File uploadedFile: unknown type for old or data (should be string or undefined)"
        );

        return "";
    }
};
</script>

<template>
    <div>
        <!-- Αρχείο -->
        <div class="row">
            <div class="mb-2" v-if="uploadedFile()">
                Ήδη ανεβασμένο αρχείο: <a :href="route">{{ uploadedFile() }}</a
                >. Αν θέλετε να το αλλάξετε επιλέξτε ένα νέο αρχείο από κάτω.
            </div>
            <input
                type="file"
                class="form-control mb-2"
                :class="error ? 'is-invalid' : ''"
                :id="`f${field.id}`"
                :name="`f${field.id}`"
                :disabled
                :accept
                :required="field.required"
            />
            <div class="">Σημείωση: Μπορείτε να ανεβάσετε μόνο ένα αρχείο</div>
        </div>
    </div>
</template>
