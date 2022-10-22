import RoleComponent from "./RoleComponent.vue";
import "../../sass/app.scss";

describe("<RoleComponent />", () => {
    it("renders", () => {
        cy.mount(RoleComponent);
    });

    it("can select admin", () => {
        cy.mount(RoleComponent, {
            props: {
                current_roles: '["Administrator"]',
            },
        });

        cy.get("#Administrator").should("be.checked");
        cy.get("#Author").should("not.be.checked");
        cy.get("#User").should("not.be.checked");
    });

    it("can select author", () => {
        cy.mount(RoleComponent, {
            props: {
                current_roles: '["Author"]',
            },
        });

        cy.get("#Administrator").should("not.be.checked");
        cy.get("#Author").should("be.checked");
        cy.get("#User").should("not.be.checked");
    });

    it("can select user", () => {
        cy.mount(RoleComponent, {
            props: {
                current_roles: '["User"]',
            },
        });

        cy.get("#Administrator").should("not.be.checked");
        cy.get("#Author").should("not.be.checked");
        cy.get("#User").should("be.checked");
    });

    it("can select multiple roles - Admin,Author", () => {
        cy.mount(RoleComponent, {
            props: {
                current_roles: '["Administrator","Author"]',
            },
        });

        cy.get("#Administrator").should("be.checked");
        cy.get("#Author").should("be.checked");
        cy.get("#User").should("not.be.checked");
    });

    it("can select multiple roles - Author,User", () => {
        cy.mount(RoleComponent, {
            props: {
                current_roles: '["Author","User"]',
            },
        });

        cy.get("#Administrator").should("not.be.checked");
        cy.get("#Author").should("be.checked");
        cy.get("#User").should("be.checked");
    });

    it("can select multiple roles - Admin,User", () => {
        cy.mount(RoleComponent, {
            props: {
                current_roles: '["Administrator","User"]',
            },
        });

        cy.get("#Administrator").should("be.checked");
        cy.get("#Author").should("not.be.checked");
        cy.get("#User").should("be.checked");
    });

    it("can select multiple roles - Admin,Author,User", () => {
        cy.mount(RoleComponent, {
            props: {
                current_roles: '["Administrator","Author","User"]',
            },
        });

        cy.get("#Administrator").should("be.checked");
        cy.get("#Author").should("be.checked");
        cy.get("#User").should("be.checked");
    });

    it("cannot select roles if disabled", () => {
        cy.mount(RoleComponent, {
            props: {
                current_roles: '["Administrator","Author","User"]',
                disabled: "disabled",
            },
        });

        cy.get("input").each((element) => {
            cy.wrap(element).should("be.disabled");
        });
    });
});
