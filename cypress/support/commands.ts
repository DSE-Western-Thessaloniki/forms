/// <reference types="cypress" />
// ***********************************************
// This example commands.ts shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })
//
// declare global {
//   namespace Cypress {
//     interface Chainable {
//       login(email: string, password: string): Chainable<void>
//       drag(subject: string, options?: Partial<TypeOptions>): Chainable<Element>
//       dismiss(subject: string, options?: Partial<TypeOptions>): Chainable<Element>
//       visit(originalFn: CommandOriginalFn, url: string, options: Partial<VisitOptions>): Chainable<Element>
//     }
//   }
// }

Cypress.Commands.add("rewriteHeaders", () => {
    cy.intercept("*", (req) =>
        req.on("response", (res) => {
            const setCookies = res.headers["set-cookie"];
            res.headers["set-cookie"] = (
                Array.isArray(setCookies) ? setCookies : [setCookies]
            )
                .filter((x) => x)
                .map((headerContent) =>
                    headerContent.replace(
                        /samesite=(lax|strict)/gi,
                        "secure; samesite=none"
                    )
                );
            if (res.headers["set-cookie"].length === 0) {
                res.headers["set-cookie"] = ["secure; samesite=none"];
            }
            console.log(res.headers["set-cookie"]);
        })
    );
});
