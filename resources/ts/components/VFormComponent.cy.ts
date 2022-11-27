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

        let targetRect: DOMRect;
        let targetScrollY: number;
        // cy.get(".card-body > ul > div > li")
        cy.get("i.handle")
            .first()
            .then((element) => {
                targetRect = element[0].getBoundingClientRect();
                cy.window().then((window) => {
                    targetScrollY = window.scrollY;
                });
            });
        cy.get("i.handle")
            .last()
            .then((element) => {
                let handleRect = element[0].getBoundingClientRect();

                cy.window().then((window) => {
                    const pageY =
                        window.scrollY + handleRect.top + handleRect.height / 2;
                    const dragAmount =
                        window.scrollY +
                        handleRect.top -
                        (targetScrollY + targetRect.top);
                    console.log(dragAmount);
                    console.log(handleRect);
                    console.log(pageY);

                    cy.wrap(element)
                        .trigger("pointerover", { force: true })
                        .trigger("mouseover", { force: true })
                        .trigger("pointerdown", {
                            button: -1,
                            buttons: 0,
                            pageX: handleRect.left + handleRect.width / 2,
                            pageY,
                            force: true,
                            pointerId: 1,
                            pointerType: "mouse",
                            width: 1,
                            height: 1,
                            pressure: 0.5,
                            isTrusted: true,
                        })
                        .trigger("mousedown", {
                            button: 0,
                            buttons: 1,
                            pageX: handleRect.left + handleRect.width / 2,
                            pageY,
                            force: true,
                            isTrusted: true,
                        })
                        .trigger("pointermove", {
                            button: -1,
                            buttons: 0,
                            pageX: handleRect.left + handleRect.width / 2,
                            pageY,
                            force: true,
                            pointerId: 1,
                            pointerType: "mouse",
                            width: 1,
                            height: 1,
                            pressure: 0.5,
                            isTrusted: true,
                        })
                        .trigger("mousemove", {
                            button: 0,
                            buttons: 1,
                            pageX: handleRect.left + handleRect.width / 2,
                            pageY,
                            force: true,
                            isTrusted: true,
                        })
                        .trigger("pointermove", {
                            button: -1,
                            buttons: 0,
                            pageX: handleRect.left + handleRect.width / 2,
                            pageY: pageY + dragAmount,
                            force: true,
                            pointerId: 1,
                            pointerType: "mouse",
                            width: 1,
                            height: 1,
                            pressure: 0.5,
                            isTrusted: true,
                        })
                        .trigger("mousemove", {
                            button: 0,
                            buttons: 1,
                            pageX: handleRect.left + handleRect.width / 2,
                            pageY: pageY + dragAmount,
                            force: true,
                            isTrusted: true,
                        })
                        .trigger("mouseup", { force: true });
                });
            });
        // .trigger("mouseover", { force: true })
        // .trigger("mousedown", {
        //     button: 1,
        //     eventConstructor: "MouseEvent",
        // });
        // cy.get("i.handle").last().trigger("dragstart", { dataTransfer });
        // cy.wait(1000);
        // cy.get("li.sortable-chosen").trigger("dragstart", { dataTransfer });
        // cy.get(".card-body > ul > div > li")
        //     .last()
        //     .trigger("dragstart", "topLeft", { dataTransfer });
        // cy.get(".card-body > ul > div > li")
        //     .first()
        //     // .trigger("dragover")
        //     .trigger("end", "topLeft", { dataTransfer })
        //     // .trigger("dragend", { dataTransfer })
        //     .trigger("mouseup", "topLeft", { button: 0 });
        cy.get("[name='field[2][sort_id]']").should("have.value", 1);
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
