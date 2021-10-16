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
                                <input type="checkbox" class="form-check-input" name="Administrator" id="Administrator" value="1" v-model="administrator" disabled="disabled">
                            </div>
                            <div v-else>
                                <input type="checkbox" class="form-check-input" name="Administrator" id="Administrator" value="1" v-model="administrator">
                            </div>
                            <label for="Administrator" class="form-check-label">Διαχειριστής</label>
                        </div>
                        <div class="form-check">
                            <div v-if="disabled === 'disabled'">
                                <input type="checkbox" class="form-check-input" name="Author" id="Author" value="1" v-model="author" disabled="disabled">
                            </div>
                            <div v-else>
                                <input type="checkbox" class="form-check-input" name="Author" id="Author" value="1" v-model="author">
                            </div>
                            <label for="Author" class="form-check-label">Συγγραφέας</label>
                        </div>
                        <div class="form-check">
                            <div v-if="disabled === 'disabled'">
                                <input type="checkbox" class="form-check-input" name="User" id="User" value="1" v-model="user" disabled="disabled">
                            </div>
                            <div v-else>
                                <input type="checkbox" class="form-check-input" name="User" id="User" value="1" v-model="user">
                            </div>
                            <label for="User" class="form-check-label">Χρήστης</label>
                        </div>
                    </div>
                </div>
            </fieldset>

        </div>
    </div>
</template>

<script>

    export default {
        props: {
            current_roles: String,
            disabled: String,
        },
        mounted() {
            this.cur_roles=JSON.parse(this.current_roles);
            this.administrator=this.cur_roles.includes('Administrator');
            this.author=this.cur_roles.includes('Author');
            this.user=this.cur_roles.includes('User');
        },
        data: function() {
            return {
                cur_roles: '',
                administrator: false,
                author: false,
                user: false,
            }
        },
        ready: function() {
        },
        methods: {
        },
        computed: {
        }
    }
</script>
