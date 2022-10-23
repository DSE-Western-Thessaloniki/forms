import VEditableTextComponent from "./VEditableTextComponent.vue";
import "../../sass/app.scss";

describe("<VEditableTextComponent />", () => {
    it("renders", () => {
        // see: https://test-utils.vuejs.org/guide/
        cy.mount(VEditableTextComponent, {
            props: {
                edittext: "This is a test",
                fid: "test",
            },
        });
    });

    it("shows input field when clicked", () => {
        cy.mount(VEditableTextComponent, {
            props: {
                edittext: "This is a test",
                fid: "test",
            },
        });
        cy.get(".editable-text-group > span").click();
        cy.get("[name='test']").should("be.visible");
    });

    it("doesn't show input field when clicked if restricted", () => {
        cy.mount(VEditableTextComponent, {
            props: {
                edittext: "This is a test",
                fid: "test",
                restricted: true,
            },
        });
        cy.get(".editable-text-group > span").click();
        cy.get("[name='test']").should("not.be.visible");
    });

    it("changes span text after typing new value and pressing Enter", () => {
        const updateEdittextSpy = cy.spy().as("updateEdittextSpy");
        cy.mount(VEditableTextComponent, {
            props: {
                edittext: "This is a test",
                fid: "test",
                "onUpdate:edittext": updateEdittextSpy,
            },
        });
        cy.get(".editable-text-group > span").click();
        cy.get("[name='test']")
            .type("{selectAll}")
            .type("New text")
            .type("{enter}");
        cy.get(".editable-text-group > span")
            .should("have.text", "New text")
            .should("be.visible");
        cy.get("@updateEdittextSpy").should(
            "always.have.been.calledWith",
            "New text"
        );
    });

    it("changes span text after typing new value and losing focus", () => {
        const updateEdittextSpy = cy.spy().as("updateEdittextSpy");
        cy.mount(VEditableTextComponent, {
            props: {
                edittext: "This is a test",
                fid: "test",
                "onUpdate:edittext": updateEdittextSpy,
            },
        });
        cy.get(".editable-text-group > span").click();
        cy.get("[name='test']").type("{selectAll}").type("New text").blur();
        cy.get(".editable-text-group > span")
            .should("have.text", "New text")
            .should("be.visible");
        cy.get("@updateEdittextSpy").should(
            "always.have.been.calledWith",
            "New text"
        );
    });

    it("sets span text to initial prop value if text is removed completely", () => {
        cy.mount(VEditableTextComponent, {
            props: {
                edittext: "This is a test",
                fid: "test",
            },
        });
        cy.get(".editable-text-group > span").click();
        cy.get("[name='test']")
            .type("{selectAll}")
            .type("{backspace}")
            .type("{enter}");
        cy.get(".editable-text-group > span").should(
            "have.text",
            "This is a test"
        );
    });
});
