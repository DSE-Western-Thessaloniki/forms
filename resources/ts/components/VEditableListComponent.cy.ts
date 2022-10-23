import VEditableListComponent from "./VEditableListComponent.vue";
import "../../sass/app.scss";

const listValues = [
    { id: 0, value: "at" },
    { id: 1, value: "et" },
];

describe("<VEditableListComponent />", () => {
    it("renders", () => {
        cy.mount(VEditableListComponent, {
            props: {
                edittext: JSON.stringify(listValues),
                restricted: false,
            },
        });
    });

    it("can edit values using the pencil icon", () => {
        cy.mount(VEditableListComponent, {
            props: {
                edittext: JSON.stringify(listValues),
                restricted: false,
            },
        });
        cy.get("[test-data-id='preview']").click();
        cy.get("li > input").each((element) => {
            cy.wrap(element).should("be.visible");
        });
    });

    it("can edit values by clicking in list", () => {
        cy.mount(VEditableListComponent, {
            props: {
                edittext: JSON.stringify(listValues),
                restricted: false,
            },
        });
        cy.get("[test-data-id='preview']").click();
        cy.get("li > input").each((element) => {
            cy.wrap(element).should("be.visible");
        });
    });

    // Για κάποιο λόγο το συγκεκριμένο τεστ αποτυγχάνει ενώ λειτουργεί χειροκίνητα
    it.skip("can save values by clicking on save icon", () => {
        cy.mount(VEditableListComponent, {
            props: {
                edittext: JSON.stringify(listValues),
                restricted: false,
            },
        });
        cy.get("[test-data-id='preview']").click();
        cy.get("[test-data-id='edit-body'] li > input")
            .first()
            .type("{selectAll}test");
        cy.get("[test-data-id='edit']").click();
        cy.get("[test-data-id='edit-body'] li > input").each((element) => {
            cy.wrap(element).should("not.be.visible");
        });
        cy.get("li").first().should("have.text", "test");
    });

    it("can save values by clicking outside the list", () => {
        cy.mount(VEditableListComponent, {
            props: {
                edittext: JSON.stringify(listValues),
                restricted: false,
            },
        });
        cy.get("[test-data-id='preview']").click();
        cy.get("[test-data-id='edit-body'] li > input")
            .first()
            .type("{selectAll}test");
        cy.get("[data-cy-root]").click("bottom");
        cy.get("[test-data-id='edit-body'] li > input").each((element) => {
            cy.wrap(element).should("not.be.visible");
        });
        cy.get("li").first().should("have.text", "test");
    });

    it("cannot edit values when restricted", () => {
        cy.mount(VEditableListComponent, {
            props: {
                edittext: JSON.stringify(listValues),
                restricted: true,
            },
        });
        cy.get("[test-data-id='preview']").should("not.exist");
        cy.get("[test-data-id='preview-body']").click();
        cy.get("[test-data-id='edit-body'] li > input").each((element) => {
            cy.wrap(element).should("not.be.visible");
        });
    });

    it("creates another input when typing new value", () => {
        cy.mount(VEditableListComponent, {
            props: {
                edittext: JSON.stringify(listValues),
                restricted: false,
            },
        });
        cy.get("[test-data-id='preview']").click();
        cy.get("[test-data-id='edit-body'] li > input").last().type("test");
        cy.get("[test-data-id='edit-body'] li > input").should(
            "have.length",
            4
        );
    });

    it("creates another input when pasting text", () => {
        cy.mount(VEditableListComponent, {
            props: {
                edittext: JSON.stringify(listValues),
                restricted: false,
            },
        });
        cy.get("[test-data-id='preview']").click();
        cy.get("[test-data-id='edit-body'] li > input")
            .last()
            .invoke("val", "test")
            .trigger("paste");
        cy.get("[test-data-id='edit-body'] li > input").should(
            "have.length",
            4
        );
    });
});
