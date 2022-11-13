import {
    cy_create_school_category,
    cy_create_school,
    cy_bypass_register,
} from "./utils/utils";

describe("School category creation", () => {
    it("can create a new school category as admin", () => {
        cy.refreshDatabase();
        cy.seed().then(() => {
            cy_bypass_register();
        });
        cy.login({ name: "Administrator" });
        cy.visit("/admin").then(() => {
            cy_create_school_category("ΙΔΙΩΤΙΚΑ");
        });
        cy.contains("Η κατηγορία σχολικής μονάδας αποθηκεύτηκε");
        cy.contains("ΙΔΙΩΤΙΚΑ");
        cy.php("App\\Models\\SchoolCategory::all()->last()->toArray()").then(
            (result: App.Models.SchoolCategory) => {
                if (result.name !== "ΙΔΙΩΤΙΚΑ") {
                    throw new Error(
                        "Λάθος αποθήκευση των στοιχείων στην βάση!"
                    );
                }
            }
        );
    });

    it("can add a new school to the category as admin", () => {
        let new_school = {
            code: "999999",
            email: "tst@sch.gr",
            name: "Test School",
            telephone: "1234567890",
            username: "999",
        };

        cy.login({ name: "Administrator" });
        cy.visit("/admin").then(() => {
            cy_create_school(new_school, "ΙΔΙΩΤΙΚΑ");
        });

        let category_id = 0;

        cy.get("[test-data-id='nav-item-schools']").click();
        cy.get("[test-data-id='btn-school-category-index']").click();
        cy.contains("Test School");
        cy.php("App\\Models\\SchoolCategory::all()->last()")
            .then((category: App.Models.SchoolCategory) => {
                category_id = category.id;
            })
            .then(() => {
                cy.get(
                    `[test-data-id='category-${category_id}'] > span.bg-success`
                ).contains("1");
            });
    });
});
