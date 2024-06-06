import { defineStore } from "pinia";

export const useFormStore = defineStore("formStore", {
    state: () => {
        return { field: {} as Record<string, string | null> };
    },
});
