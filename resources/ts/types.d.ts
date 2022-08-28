declare namespace App.Types {
    export type AssociativeArray<T = unknown> = {
        [key: string | number]: AssociativeArray<T> | T | undefined;
    };
}
