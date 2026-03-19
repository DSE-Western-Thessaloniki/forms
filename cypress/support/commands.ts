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

declare global {
    namespace Cypress {
        interface Chainable {
            /**
             * Drags an element to a target element.
             *
             * This is a minimal helper to simulate a drag-and-drop action for
             * Sortable.js / vuedraggable-based list reordering.
             */
            dragTo(target: string | JQuery<HTMLElement>): Chainable<Element>;
        }
    }
}

Cypress.Commands.add(
    "dragTo",
    { prevSubject: "element" },
    (subject, target) => {
        cy.wrap(subject).then(($source) => {
            const srcRect = $source[0].getBoundingClientRect();
            const startX = srcRect.left + srcRect.width / 2;
            const startY = srcRect.top + srcRect.height / 2;

            cy.wrap($source).trigger("mousedown", {
                button: 0,
                clientX: startX,
                clientY: startY,
                force: true,
            });

            cy.get(target).then(($target) => {
                const tgtRect = $target[0].getBoundingClientRect();
                const endX = tgtRect.left + tgtRect.width / 2;
                const endY = tgtRect.top + tgtRect.height / 2;

                cy.wrap($target)
                    .trigger("mousemove", { clientX: endX, clientY: endY, force: true })
                    .trigger("mouseup", { force: true });
            });
        });

        return cy.wrap(subject);
    }
);
