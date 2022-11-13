import { FieldType } from "@/fieldtype";
import { cy_bypass_register, cy_create_school } from "./utils/utils";

describe("School creation", () => {
    it("can create a new school as admin", () => {
        let new_school = {
            code: "999999",
            email: "tst@sch.gr",
            name: "Test School",
            telephone: "1234567890",
            username: "999",
        };

        cy.refreshDatabase();
        cy.seed().then(() => {
            cy_bypass_register();
        });
        cy.login({ name: "Administrator" });
        cy.visit("/admin").then(() => {
            cy_create_school(new_school, "ΓΕΛ");
        });
        cy.contains("Η σχολική μονάδα αποθηκεύτηκε");
        cy.contains("Test School");
        cy.php("App\\Models\\School::all()->last()->toArray()").then(
            (result: App.Models.School) => {
                for (const key in new_school) {
                    if (
                        new_school[key as keyof typeof new_school] !==
                        result[key as keyof typeof new_school]
                    ) {
                        throw new Error(
                            "Λάθος αποθήκευση των στοιχείων στην βάση!"
                        );
                    }
                }
            }
        );
    });

    it("can login as school", () => {
        const login = {
            username: "tstsch",
            password: "password",
        };

        cy.visit("/");
        cy.get("[test-data-id='nav-item-login'").click();
        cy.get("input[name='username']").type(login.username);
        cy.get("input[name='password']").type(login.password);
        cy.get("button[name='submit']").click();
        cy.contains("Δεν βρέθηκαν φόρμες");
        cy.contains("Test School");
    });
});
