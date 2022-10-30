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

    // TODO: Έλεγχος drag'n'drop
    it.skip("can reorder form fields", () => {
        const dataTransfer = new DataTransfer();

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
        // cy.get("i.handle")
        //     .last()
        //     .then((element) => {
        //         cy.get(".card-body > ul > div").trigger("dragenter", {
        //             target: element,
        //             dataTransfer,
        //         });
        //     });
        // cy.get("i.handle")
        //     .first()
        //     .then((element) => {
        //         cy.get(".card-body > ul > div").trigger("end", {
        //             target: element,
        //             dataTransfer,
        //         });
        //     });
        cy.get("i.handle").last().trigger("mousedown", {
            button: 1,
            eventConstructor: "MouseEvent",
        });
        // cy.wait(1000);
        // cy.get(".sortable-chosen").trigger("dragstart", { dataTransfer });
        cy.get(".card-body > ul > div > li")
            .last()
            .trigger("dragstart", "topLeft", { dataTransfer });
        cy.get(".card-body > ul > div > li")
            .first()
            // .trigger("dragover")
            .trigger("end", "topLeft", { dataTransfer })
            // .trigger("dragend", { dataTransfer })
            .trigger("mouseup", "topLeft", { button: 0 });
        cy.get("[name='field[2][sort_id]']").should("have.value", 1);
    });
});
