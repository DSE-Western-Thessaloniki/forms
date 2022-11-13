describe("First setup", () => {
    it("loads the front page properly", () => {
        cy.refreshDatabase();
        cy.seed();
        cy.visit("/");
        cy.contains("Σύνδεση");
    });

    it("shows setup page after seeding", () => {
        cy.seed();
        cy.visit("/");
        cy.contains("Ρύθμιση διαχειριστή συστήματος");
        cy.get("#name").type("Administrator");
        cy.get("#email").type("admin@example.com");
        cy.get("#username").type("Administrator");
        cy.get("#password").type("password");
        cy.get("#password-confirm").type("password");
        cy.get("button.btn[type='submit']").click();
        cy.contains("Πίνακας ελέγχου");
    });
});
