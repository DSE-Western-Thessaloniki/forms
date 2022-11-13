import { FieldType } from "@/fieldtype";
import { cy_bypass_register } from "./utils/utils";

describe("Form creation", () => {
    it("can create a new form as admin", () => {
        cy.refreshDatabase();
        cy.seed().then(() => {
            cy_bypass_register();
        });
        cy.login({ name: "Administrator" });
        cy.visit("/admin");
        cy.get("[test-data-id='nav-item-forms']").click();
        cy.get("[test-data-id='btn-create-form']").click();
        cy.get("[test-data-id='form-title'] > span").click();
        cy.focused().type("{selectAll}Test form{enter}");
        cy.get("#notes").type("This is a test form");
        cy.get("[test-data-id='field-item'] .editable-text-label").click();
        cy.focused().type("{selectAll}Last name{enter}");
        cy.get("[test-data-id='addField'").click();
        cy.get("[test-data-id='field-item'] .editable-text-label")
            .last()
            .click();
        cy.focused().type("{selectAll}First name{enter}");
        cy.get("[test-data-id='addField'").click();
        cy.get("[test-data-id='field-item'] .editable-text-label")
            .last()
            .click();
        cy.focused().type("{selectAll}Age{enter}");
        cy.get("[test-data-id='field-item'] select")
            .last()
            .select(FieldType.Number.toString());
        cy.get("[test-data-id='category-pillbox'] select").select("ΓΕΛ");
        cy.get("[test-data-id='btn-save']").click();
        cy.contains("Η φόρμα δημιουργήθηκε");
        cy.contains("Test form");
    });
});
