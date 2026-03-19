import VFormComponent from "./VFormComponent.vue";
import "../../sass/app.scss";
import { last } from "cypress/types/lodash";

const dataSchools: Array<Partial<App.Models.School>> = [
    {
        id: 1,
        name: "school1",
        active: true,
    },
    {
        id: 2,
        name: "school2",
        active: true,
    },
];

const dataSchoolCategories: Array<Partial<App.Models.SchoolCategory>> = [
    {
        id: 1,
        name: "category1",
    },
    {
        id: 2,
        name: "category2",
    },
];

describe("<VFormComponent />", () => {
    it("renders", () => {
        cy.mount(VFormComponent, {
            props: {
                schools: dataSchools,
                categories: dataSchoolCategories,
            },
        });
    });

    it("can add multiple form fields", () => {
        cy.mount(VFormComponent, {
            props: {
                schools: dataSchools,
                categories: dataSchoolCategories,
            },
        });
        cy.get("[test-data-id='addField']").click().click();
        cy.get(".handle").should("have.length", 3);
    });

    it("can reorder form fields", () => {
        cy.mount(VFormComponent, {
            props: {
                schools: dataSchools,
                categories: dataSchoolCategories,
            },
        });
        cy.get("[test-data-id='addField']").click().click();
        cy.get("label[for='fieldtitleid'] + div > div> span").each(
            (element, index) => {
                cy.wrap(element).click();
                cy.focused().type(`{selectAll}Test ${index}{enter}`);
            }
        );

        // Move the last field to the top
        cy.get("i.handle").last().dragTo("i.handle:first");

        cy.get("[name='field[2][sort_id]']").should("have.value", "1");
    });

    it("selecting for teachers hides school selection", () => {
        cy.mount(VFormComponent, {
            props: {
                schools: dataSchools,
                categories: dataSchoolCategories,
            },
        });
        cy.get("[test-data-id='ul_for_schools']").should("be.visible");
        cy.get("#radio_for_teachers1").click();
        cy.get("[test-data-id='ul_for_schools']").should("not.exist");
        cy.get("#radio_for_teachers2").click();
        cy.get("[test-data-id='ul_for_schools']").should("be.visible");
    });
});
