import VDataTableComponent from "./VDataTableComponent.vue";
import "../../sass/app.scss";

const testColumns = ["col1", "col2"];
const testSchools = [
    {
        code: 1,
        name: "School1",
    },
    {
        code: 2,
        name: "School2",
    },
    {
        code: 3,
        name: "School3",
    },
];

const testDataEmpty = {};

const testDataPartial = {
    1: {
        col1: {
            0: {
                value: 1,
                created: "2022-10-23 10:10:10",
                updated: "2022-10-23 10:10:20",
            },
        },
    },
};

const testDataFull = {
    1: {
        col1: {
            0: {
                value: 1,
                created: "2022-10-23 10:10:10",
                updated: "2022-10-23 10:10:20",
            },
        },
        col2: {
            0: {
                value: 2,
                created: "2022-10-23 10:10:10",
                updated: "2022-10-23 10:10:20",
            },
        },
    },
    2: {
        col1: {
            0: {
                value: 3,
                created: "2022-10-23 10:10:10",
                updated: "2022-10-23 10:10:20",
            },
        },
        col2: {
            0: {
                value: 4,
                created: "2022-10-23 10:10:10",
                updated: "2022-10-23 10:10:20",
            },
        },
    },
    3: {
        col1: {
            0: {
                value: 5,
                created: "2022-10-23 10:10:10",
                updated: "2022-10-23 10:10:20",
            },
        },
        col2: {
            0: {
                value: 6,
                created: "2022-10-23 10:10:10",
                updated: "2022-10-23 10:10:20",
            },
        },
    },
};

const testDataRecords = {
    1: {
        col1: {
            0: {
                value: 1,
                created: "2022-10-23 10:10:10",
                updated: "2022-10-23 10:10:20",
            },
            1: {
                value: 2,
                created: "2022-10-23 10:10:10",
                updated: "2022-10-23 10:10:20",
            },
            2: {
                value: 3,
                created: "2022-10-23 10:10:10",
                updated: "2022-10-23 10:10:20",
            },
        },
    },
};

describe("<VDataTableComponent />", () => {
    it("renders with empty data", () => {
        cy.mount(VDataTableComponent, {
            props: {
                columns: testColumns,
                schools: testSchools,
                data: testDataEmpty,
            },
        });
        // Η πρώτη στήλη θα έχει το όνομα του σχολείου
        cy.get("tbody > tr > td:nth-child(1)").each((el, index) => {
            cy.wrap(el).should("have.text", testSchools[index].name);
        });
        // και η δεύτερη τον κωδικό του σχολείου
        cy.get("tbody > tr > td:nth-child(2)").each((el, index) => {
            cy.wrap(el).should("have.text", testSchools[index].code);
        });
    });

    it("renders with partial data", () => {
        cy.mount(VDataTableComponent, {
            props: {
                columns: testColumns,
                schools: testSchools,
                data: testDataPartial,
            },
        });
        cy.get("tbody > tr > td:nth-child(3)").first().should("have.text", "1");
    });

    it("renders with full data", () => {
        cy.mount(VDataTableComponent, {
            props: {
                columns: testColumns,
                schools: testSchools,
                data: testDataFull,
            },
        });
        cy.get("tbody > tr > td:nth-child(3), td:nth-child(4)").each(
            (el, index) => {
                cy.wrap(el).should("have.text", index + 1);
            }
        );
    });

    it("renders multiple records", () => {
        cy.mount(VDataTableComponent, {
            props: {
                columns: testColumns,
                schools: testSchools,
                data: testDataRecords,
            },
        });
        cy.get("tbody > tr:nth-child(-n+3) > td:nth-child(1)").each(
            (el, index) => {
                cy.wrap(el).should("have.text", testSchools[0].name);
            }
        );
        cy.get("tbody > tr:nth-child(-n+3) > td:nth-child(2)").each(
            (el, index) => {
                cy.wrap(el).should("have.text", testSchools[0].code);
            }
        );
        cy.get("tbody > tr:nth-child(-n+3) > td:nth-child(3)").each(
            (el, index) => {
                cy.wrap(el).should("have.text", index + 1);
            }
        );
    });
});
