export enum FieldType {
    Text,
    TextArea,
    RadioButtons,
    CheckBoxes,
    SelectionList,
    File,
    Date,
    Number,
    Telephone,
    Email,
    WebPage,
    List,
}

export const FieldTypeOptions: { id: FieldType; value: string }[] = [
    {
        id: FieldType.Text,
        value: "Πεδίο κειμένου",
    },
    {
        id: FieldType.TextArea,
        value: "Περιοχή κειμένου",
    },
    {
        id: FieldType.RadioButtons,
        value: "Επιλογή ενός από πολλά",
    },
    {
        id: FieldType.CheckBoxes,
        value: "Πολλαπλή επιλογή",
    },
    {
        id: FieldType.SelectionList,
        value: "Λίστα επιλογών",
    },
    {
        id: FieldType.File,
        value: "Ανέβασμα αρχείου",
    },
    {
        id: FieldType.Date,
        value: "Ημερομηνία",
    },
    {
        id: FieldType.Number,
        value: "Αριθμός",
    },
    {
        id: FieldType.Telephone,
        value: "Τηλέφωνο",
    },
    {
        id: FieldType.Email,
        value: "E-mail",
    },
    {
        id: FieldType.WebPage,
        value: "Διεύθυνση ιστοσελίδας",
    },
    {
        id: FieldType.List,
        value: "Έτοιμη λίστα δεδομένων",
    },
];

export interface FormFieldOptionsShowCriteria {
    operator?: "and" | "or";
    visible: "always" | "when_field_is_active" | "when_value";
    active_field?: string;
    value_is?: "gt" | "ge" | "lt" | "le" | "eq" | "ne";
    value?: string;
}

export interface FormFieldOptions {
    regex_enabled?: boolean;
    regex?: string;
    regex_description?: string;
    capitals_enabled?: boolean;
    greek_enabled?: boolean;
    positive?: boolean;

    // For numerical fields
    number_type?: "integer" | "float";
    decimal_places?: number | string;

    show_when?: Array<FormFieldOptionsShowCriteria>;
    field_width_enabled?: boolean;
    field_width?: string;
    filetype?: {
        value: string;
        field_for_filename?: string | null;
        custom_value?: string | null;
    };

    // For legacy support / backward compatibility (stored as plain string in the DB)
    step?: string;

    // Read-only fields cannot be edited by users
    readonly?: boolean;
}

export function createFormFieldOptions(
    options: Partial<FormFieldOptions> = {}
): Required<FormFieldOptions> {
    return {
        regex_enabled: options.regex_enabled ?? false,
        regex: options.regex ?? "",
        regex_description: options.regex_description ?? "",
        capitals_enabled: options.capitals_enabled ?? false,
        greek_enabled: options.greek_enabled ?? false,
        positive: options.positive ?? false,
        number_type: options.number_type ?? "integer",
        decimal_places: options.decimal_places ?? "",
        show_when: options.show_when ?? [],
        field_width_enabled: options.field_width_enabled ?? false,
        field_width: options.field_width ?? "",
        filetype: options.filetype ?? {
            value: "",
            field_for_filename: null,
            custom_value: null,
        },
        step: options.step ?? "",
        readonly: options.readonly ?? false,
    };
}
