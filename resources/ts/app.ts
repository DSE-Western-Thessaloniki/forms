/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import { createApp, defineAsyncComponent } from "vue";
import "./bootstrap";

// Font Awesome
import "@fortawesome/fontawesome-free/scss/regular.scss";
import "@fortawesome/fontawesome-free/scss/solid.scss";
import "@fortawesome/fontawesome-free/scss/brands.scss";
import "@fortawesome/fontawesome-free/scss/fontawesome.scss";
import "@fortawesome/fontawesome-free/scss/v4-shims.scss";

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = createApp({});

// Απενεργοποίησε την αλλαγή αριθμού με χρήση της ροδέλας του ποντικιού
var inputTypeNumbers = document.querySelectorAll("input[type=number]");
for (var a = 0; a < inputTypeNumbers.length; a++) {
    let input = inputTypeNumbers[a];

    if (input instanceof HTMLElement) {
        input.onwheel = function (event) {
            let target = event.target;

            if (target instanceof HTMLElement) {
                target.blur();
            }
        };
    }
}
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

// Vue.component('example-component', require('./components/ExampleComponent.vue').default);
app.component(
    "vform-component",
    defineAsyncComponent(() => import("./components/VFormComponent.vue"))
);
app.component(
    "editable-text",
    defineAsyncComponent(
        () => import("./components/VEditableTextComponent.vue")
    )
);
app.component(
    "vform-field-component",
    defineAsyncComponent(() => import("./components/VFormFieldComponent.vue"))
);
app.component(
    "editable-list",
    defineAsyncComponent(
        () => import("./components/VEditableListComponent.vue")
    )
);
app.component(
    "rolecomponent",
    defineAsyncComponent(() => import("./components/RoleComponent.vue"))
);
app.component(
    "pillbox",
    defineAsyncComponent(() => import("./components/VPillBoxComponent.vue"))
);
app.component(
    "vdatatable-component",
    defineAsyncComponent(() => import("./components/VDataTableComponent.vue"))
);

app.component(
    "selectionlistdata",
    defineAsyncComponent(() => import("./components/SelectionListData.vue"))
);

app.component(
    "v-form",
    defineAsyncComponent(() => import("./components/frontend/form/VForm.vue"))
);

app.component(
    "field-group",
    defineAsyncComponent(
        () => import("./components/frontend/form/FieldGroup.vue")
    )
);

app.mount("#app");
