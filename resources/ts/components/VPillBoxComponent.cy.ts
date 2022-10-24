import VPillBoxComponent from "./VPillBoxComponent.vue";
import "../../sass/app.scss";

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

describe("<VPillBoxComponent />", () => {
    it("renders with school data", () => {
        cy.mount(VPillBoxComponent, {
            props: {
                options: dataSchools,
                name: "test",
            },
        });
    });

    it("renders with school category data", () => {
        cy.mount(VPillBoxComponent, {
            props: {
                options: dataSchoolCategories,
                name: "test",
            },
        });
    });

    it("filters inactive schools", () => {
        let modifiedSchools = JSON.parse(JSON.stringify(dataSchools));
        modifiedSchools[0].active = false;
        cy.mount(VPillBoxComponent, {
            props: {
                options: modifiedSchools,
                name: "test",
            },
        });
        // Θα πρέπει να έχει 2 μόνο επιλογές (μια είναι η -1)
        cy.get("option").should("have.length", 2);
        cy.get("option").last().should("have.text", "school2");
    });

    it("can select multiple items", () => {
        cy.mount(VPillBoxComponent, {
            props: {
                options: dataSchools,
                name: "test",
            },
        });
        cy.get(".form-select").select(1);
        cy.get(".form-select").select(2);
        cy.get("span").each((element, index) => {
            cy.wrap(element).should("have.text", "school" + (index + 1) + " ");
            cy.wrap(element).should("have.id", index + 1);
        });
    });

    it("cannot select multiple times same item", () => {
        cy.mount(VPillBoxComponent, {
            props: {
                options: dataSchools,
                name: "test",
            },
        });
        cy.get(".form-select").select(1);
        cy.get(".form-select").select(1);
        cy.get("span").should("have.length", 1);
    });

    it("can select and deselect items", () => {
        cy.mount(VPillBoxComponent, {
            props: {
                options: dataSchools,
                name: "test",
            },
        });
        cy.get(".form-select").select(1);
        cy.get("span > button").each((element, index) => {
            cy.wrap(element).click();
        });
        cy.get("span").should("have.length", 0);
    });
});
