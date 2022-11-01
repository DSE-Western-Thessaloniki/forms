describe("Form creation", () => {
    it.only("can create a new form as admin", () => {
        cy.refreshDatabase();
        cy.seed();
        cy.seed("DataSeeder");
        cy.visit("/");
        cy.contains("Ρύθμιση διαχειριστή συστήματος");
        cy.get("#name").type("Administrator");
        cy.get("#email").type("admin@example.com");
        cy.get("#username").type("Administrator");
        cy.get("#password").type("password");
        cy.get("#password-confirm").type("password");
        cy.get("button.btn[type='submit']").click();
        cy.get("[test-data-id='nav-item-forms']").click();
        cy.get("[test-data-id='btn-create-form']").click();
        cy.get("[test-data-id='form-title'").click();
        cy.focused().type("Test form");
    });

    it("shows setup page after seeding", () => {
        cy.contains("Πίνακας ελέγχου");
    });
});
