<?php

namespace Database\Seeders;

use App\Models\AcceptedFiletype;
use Illuminate\Database\Seeder;

class AcceptedFiletypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['*.pdf,*.doc,*.docx,*.xls,*.xlsx,*.ppt,*.pptx,*.pps,*.ppsx,*.odt,*.ods,*.odp', 'Διάφορα Έγγραφα'],
            ['*.doc,*.docx,*.odt', 'Έγγραφα'],
            ['*.xls,*.xlsx,*.ods', 'Λογιστικά Φύλλα'],
            ['*.ppt,*.pptx,*.pps,*.ppsx,*.odp', 'Παρουσιάσεις'],
            ['*.zip,*.rar,*.7z', 'Συμπιεσμένοι φάκελοι'],
            ['*.pdf', 'PDF αρχεία'],
            ['*.jpg,*.jpeg,*.png,*.webp', 'Εικόνες'],
            ['*', 'Τα πάντα (προσοχή)'],
        ];

        foreach ($types as $type) {
            AcceptedFiletype::create([
                'extension' => $type[0],
                'description' => $type[1],
            ]);
        }
    }
}
