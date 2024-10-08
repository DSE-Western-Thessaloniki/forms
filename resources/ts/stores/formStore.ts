import type { useOptionsObject } from "@/components/composables/useOptions";
import type { FieldType } from "@/fieldtype";
import { defineStore } from "pinia";

export const useFormStore = defineStore("formStore", {
    state: () => {
        return {
            field: {} as Record<string, string | null>,
            fieldType: {} as Record<string, FieldType>,
            fieldOptions: {} as Record<string, useOptionsObject>,
        };
    },
});
