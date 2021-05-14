<template>
    <div class="card">
        <div class="card-header">Ρόλοι</div>
        <div class="card body">
            <fieldset class="form-group">
                <div class="row no-gutters p-4">
                    <legend class="col-form-label col-2 pt-0">Κύριοι ρόλοι:</legend>
                    <div class="col-10">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="Administrator" id="Administrator" value="1" v-model="administrator">
                            <label for="Administrator" class="form-check-label">Διαχειριστής</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="User" id="User" value="1" v-model="user">
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
        },
        mounted() {
            this.cur_roles=JSON.parse(this.current_roles);
            this.administrator=this.cur_roles.includes('Administrator');
            this.user=this.cur_roles.includes('User');
        },
        data: function() {
            return {
                cur_roles: '',
                administrator: false,
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
