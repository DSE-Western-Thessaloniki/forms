import MultipleSelect from "./MultipleSelect.vue";
import "../../sass/app.scss";

describe("<MultipleSelect />", () => {
    it("renders", () => {
        cy.mount(MultipleSelect);
    });
});
