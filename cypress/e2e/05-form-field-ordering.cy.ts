import { cy_bypass_register } from "./utils/utils";

describe("Form field ordering", () => {
    it("keeps field order after adding a field and reordering", () => {
        cy.refreshDatabase();
        cy.seed().then(() => {
            cy_bypass_register();
        });
        cy.login({ name: "Administrator" });
        cy.visit("/admin");
        cy.get("[test-data-id='nav-item-forms']").click();
        cy.get("[test-data-id='btn-create-form']").click();

        // Create initial form with two fields
        cy.get("[test-data-id='form-title'] > span").click();
        cy.focused().type("{selectAll}Test form{enter}");

        cy.get("[test-data-id='field-item'] .editable-text-label").click();
        cy.focused().type("{selectAll}First field{enter}");

        cy.get("[test-data-id='addField']").click();
        cy.get("[test-data-id='field-item'] .editable-text-label")
            .last()
            .click();
        cy.focused().type("{selectAll}Second field{enter}");

        // Save the form
        cy.get("[test-data-id='btn-save']").click();
        cy.contains("Η φόρμα δημιουργήθηκε");

        // Edit the form: add a new field and move it to the top
        cy.contains("a", "Test form").click();
        cy.contains("Επεξεργασία").click();

        cy.get("[test-data-id='addField']").click();
        cy.get("[test-data-id='field-item'] .editable-text-label")
            .last()
            .click();
        cy.focused().type("{selectAll}New field{enter}");

        // Move the new field to the top
        cy.get("[test-data-id='field-item']")
            .last()
            .find(".handle")
            .dragTo("[test-data-id='field-item']:first");

        cy.get("[test-data-id='btn-save']").click();
        cy.contains("Η φόρμα ενημερώθηκε");

        // Verify that the order was saved
        cy.contains("a", "Test form").click();
        cy.get(".card .card-body").within(() => {
            cy.get("label").eq(0).should("contain.text", "New field");
            cy.get("label").eq(1).should("contain.text", "First field");
            cy.get("label").eq(2).should("contain.text", "Second field");
        });
    });
});
