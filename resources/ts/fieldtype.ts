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

export interface FormFieldOptions {
    regex_enabled?: boolean;
    regex?: string;
    capitals_enabled?: boolean;
    greek_enabled?: boolean;
    positive?: boolean;
    show_when?: string;
    field_width_enabled?: boolean;
    field_width?: string;
    filetype?: {
        value: string;
        field_for_filename?: string | null;
        custom_value?: string | null;
    };
}
