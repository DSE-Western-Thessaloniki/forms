export function cy_bypass_register() {
    cy.create({
        model: "App\\Models\\User",
        attributes: {
            name: "Administrator",
            active: 1,
        },
        state: ["admin"],
    });
    cy.php(
        "App\\Models\\Option::where('name', 'first_run')->update(['value' => 0])"
    );
}

export function cy_create_school(
    school: Omit<
        App.Models.School,
        "created_at" | "updated_at" | "updated_by" | "id" | "active"
    >,
    category: string
) {
    cy.get("[test-data-id='nav-item-schools']").click();
    cy.get("[test-data-id='btn-school-create']").click();
    cy.get("input[name='name'").type(school.name);
    cy.get("input[name='username'").type(school.username);
    cy.get("input[name='code'").type(school.code);
    cy.get("input[name='email'").type(school.email);
    cy.get("input[name='telephone'").type(school.telephone);
    cy.get("[test-data-id='select-category'] select").select(category);
    cy.get("button.btn[type='submit']").click();
}

export function cy_create_school_category(schoolCategory: string) {
    cy.get("[test-data-id='nav-item-schools']").click();
    cy.get("[test-data-id='btn-school-category-index']").click();
    cy.get("[test-data-id='btn-school-category-create']").click();
    cy.get("input[name='name'").type(schoolCategory);
    cy.get("button.btn[type='submit']").click();
}
