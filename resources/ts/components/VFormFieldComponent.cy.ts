import VFormFieldComponent from "@components/VFormFieldComponent.vue";
import "../../sass/app.scss";
import { FieldType } from "@/fieldtype";

describe("<VFormFieldComponent />", () => {
    it("renders", () => {
        cy.mount(VFormFieldComponent, {
            props: {
                id: 1,
                type: FieldType.Text,
                sort_id: 1,
            },
        });
    });
});
