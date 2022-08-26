declare namespace App.Models {
    export interface User {
        id: string;
        username: string;
        name: string;
        email: string;
        email_verified_at: string | null;
        password: string;
        remember_token: string | null;
        created_at: string | null;
        updated_at: string | null;
        active: boolean;
        updated_by: string;
        last_login: Date;
        isAdministrator: boolean;
        roles?: Array<App.Models.Role> | null;
    }

    export interface Role {
        id: number;
        name: string;
        created_at: string | null;
        updated_at: string | null;
        users?: Array<App.Models.User> | null;
    }

    export interface Option {
        id: number;
        name: string;
        value: string;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface School {
        id: number;
        name: string;
        username: string;
        code: string;
        email: string;
        telephone: string;
        created_at: string | null;
        updated_at: string | null;
        active: boolean;
        updated_by: string;
    }

    export interface SchoolCategory {
        id: number;
        name: string;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface Form {
        id: string;
        title: string;
        notes: string;
        created_at: string | null;
        updated_at: string | null;
        user_id: string;
        active: boolean;
        multiple: boolean;
    }

    export interface FormField {
        id: number;
        form_id: string;
        sort_id: number;
        title: string;
        type: number;
        listvalues: string;
        regex: string;
        capitals: boolean;
        positive: boolean;
        appear_when: string;
        width: string;
        created_at: string | null;
        updated_at: string | null;
    }

    export interface FormFieldData {
        id: number;
        school_id: number;
        form_field_id: number;
        data: string;
        created_at: string | null;
        updated_at: string | null;
        record: number;
    }
}
