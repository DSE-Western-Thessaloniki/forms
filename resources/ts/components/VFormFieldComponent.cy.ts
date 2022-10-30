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

    it("emits delete when close icon is clicked", () => {
        const deleteFieldSpy = cy.spy().as("deleteFieldSpy");
        cy.mount(VFormFieldComponent, {
            props: {
                id: 1,
                type: FieldType.Text,
                sort_id: 1,
                onDeleteField: deleteFieldSpy,
            },
        });
        cy.get("[test-data-id='closeButton']").click();
        cy.get("@deleteFieldSpy").should("always.have.been.calledWith", 1);
    });

    it("shows an editable list for specific field types", () => {
        cy.mount(VFormFieldComponent, {
            props: {
                id: 1,
                type: FieldType.Text,
                sort_id: 1,
            },
        });
        for (let i = 2; i < 5; i++) {
            cy.get("[name='field[1][type]']").select(`${i}`);
            cy.get("[test-data-id='editableList']").should("be.visible");
        }
        for (let i = 6; i <= 10; i++) {
            cy.get("[name='field[1][type]']").select(`${i}`);
            cy.get("[test-data-id='editableList']").should("not.exist");
        }
    });

    it("emits update:value when changing the title", () => {
        const updateValueSpy = cy.spy().as("updateValueSpy");
        const newTitle = "New Title";
        cy.mount(VFormFieldComponent, {
            props: {
                id: 1,
                type: FieldType.Text,
                sort_id: 1,
                "onUpdate:value": updateValueSpy,
            },
        });
        cy.get("[test-data-id='editableText'] > span").click();
        cy.focused().type("{selectAll}").type(newTitle).type("{enter}");
        cy.get("[test-data-id='editableText'] > span").then((element) => {
            cy.log(element.text());
        });
        cy.get("@updateValueSpy").should(
            "always.have.been.calledWith",
            newTitle
        );
    });
});
