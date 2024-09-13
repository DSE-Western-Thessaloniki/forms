<script setup lang="ts">
import { useFormStore } from "@/stores/formStore";

const props = withDefaults(
    defineProps<{
        field: App.Models.FormField;
        disabled?: boolean;
        errors: Array<string>;
        route?: string;
        accept: string;
    }>(),
    {
        disabled: false,
    }
);

const formStore = useFormStore();
const alreadyUploadedFile = formStore.field[props.field.id];

const handleValueChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    formStore.field[props.field.id] = target.value;
};
</script>

<template>
    <div>
        <!-- Αρχείο -->
        <div class="row">
            <div class="mb-2" v-if="alreadyUploadedFile">
                Ήδη ανεβασμένο αρχείο:
                <a :href="route">{{ alreadyUploadedFile }}</a
                >. Αν θέλετε να το αλλάξετε επιλέξτε ένα νέο αρχείο από κάτω.
            </div>
            <input
                type="file"
                class="form-control mb-2"
                :class="errors.length ? 'is-invalid' : ''"
                :id="`f${field.id}`"
                :name="`f${field.id}`"
                :disabled
                :accept
                :required="!alreadyUploadedFile && field.required"
                @change="handleValueChange"
            />
            <div class="">Σημείωση: Μπορείτε να ανεβάσετε μόνο ένα αρχείο</div>
        </div>
    </div>
</template>
