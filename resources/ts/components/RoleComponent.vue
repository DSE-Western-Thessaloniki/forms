<template>
    <div class="card">
        <div class="card-header">Ρόλοι</div>
        <div class="card body">
            <fieldset class="form-group">
                <div class="row no-gutters p-4">
                    <legend class="col-form-label col-2 pt-0">Κύριοι ρόλοι:</legend>
                    <div class="col-10">
                        <div class="form-check">
                            <div v-if="disabled === 'disabled'">
                                <input type="checkbox" class="form-check-input" name="Administrator" id="Administrator"
                                    value="1" v-model="administrator" disabled="disabled">
                            </div>
                            <div v-else>
                                <input type="checkbox" class="form-check-input" name="Administrator" id="Administrator"
                                    value="1" v-model="administrator">
                            </div>
                            <label for="Administrator" class="form-check-label">Διαχειριστής</label>
                        </div>
                        <div class="form-check">
                            <div v-if="disabled === 'disabled'">
                                <input type="checkbox" class="form-check-input" name="Author" id="Author" value="1"
                                    v-model="author" disabled="disabled">
                            </div>
                            <div v-else>
                                <input type="checkbox" class="form-check-input" name="Author" id="Author" value="1"
                                    v-model="author">
                            </div>
                            <label for="Author" class="form-check-label">Συγγραφέας</label>
                        </div>
                        <div class="form-check">
                            <div v-if="disabled === 'disabled'">
                                <input type="checkbox" class="form-check-input" name="User" id="User" value="1"
                                    v-model="user" disabled="disabled">
                            </div>
                            <div v-else>
                                <input type="checkbox" class="form-check-input" name="User" id="User" value="1"
                                    v-model="user">
                            </div>
                            <label for="User" class="form-check-label">Χρήστης</label>
                        </div>
                    </div>
                </div>
            </fieldset>

        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";


let cur_roles = ref(new Array<string>);
let administrator = ref(false);
let author = ref(false);
let user = ref(false);

const props = defineProps<{
    current_roles?: string,
    disabled?: string,
}>();

onMounted(() => {
    cur_roles.value = JSON.parse(props.current_roles ?? "[]");
    administrator.value = cur_roles.value.includes('Administrator');
    author.value = cur_roles.value.includes('Author');
    user.value = cur_roles.value.includes('User');
});
</script>
