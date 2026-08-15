<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Teacher;

class FormMissingDataService
{
    /**
     * @return array<array<int,string>>
     */
    private function getMissingTeacherTable(Form $form): array
    {
        $dataTableColumns = ['Εκπαιδευτικός', 'ΑΜ/ΑΦΜ', 'Τηλέφωνο'];

        // Βρες όλους τους εκπαιδευτικούς που θα έπρεπε να απαντήσουν
        $teachers = Teacher::where('active', 1)->get();
        $data = $form->data()->get();
        $answer = [];
        $data->each(function ($item, $key) use (&$answer): void {
            $answer[$item->teacher_id] = true;
        });
        $seen = [];
        $filtered_teachers = $teachers->filter(function ($teacher, $key) use ($answer, &$seen): bool {
            if (in_array($teacher, $seen) || isset($answer[$teacher->id])) {
                return false;
            }

            $seen[] = $teacher;

            return true;
        });

        $data = [];
        $data[] = $dataTableColumns;
        foreach ($filtered_teachers as $teacher) {
            $data[] = [
                "{$teacher->surname} {$teacher->name}",
                $teacher->am !== '' ? $teacher->am : $teacher->afm,
                '',
            ];
        }

        return $data;
    }

    /**
     * @return array<array<int,string>>
     */
    private function getMissingSchoolTable(Form $form): array
    {
        $dataTableColumns = ['Σχολική μονάδα', 'Κωδ. σχολικής μονάδας', 'Τηλέφωνο', 'E-mail'];

        // Βρες όλα τα σχολεία που θα έπρεπε να απαντήσουν
        $schools = $form->schools()->where('active', 1)->get();
        foreach ($form->school_categories()->get() as $category) {
            $schools = $schools->merge($category->schools()->where('active', 1)->get());
        }
        $schools = $schools->unique('id');
        $data = $form->data()->get();
        $answer = [];
        $data->each(function ($item, $key) use (&$answer): void {
            $answer[$item->school_id] = true;
        });
        $seen = [];
        $filtered_schools = $schools->filter(function ($school, $key) use ($answer, &$seen): bool {
            if (in_array($school, $seen) || isset($answer[$school->id])) {
                return false;
            }

            $seen[] = $school;

            return true;
        });

        $data = [];
        $data[] = $dataTableColumns;
        foreach ($filtered_schools as $school) {
            $data[] = [$school->name, $school->code, $school->telephone, $school->email];
        }

        return $data;
    }

    /**
     * @return array<array<int,string>>
     */
    public function getMissingTable(Form $form): array
    {
        if ($form->for_teachers) {
            return $this->getMissingTeacherTable($form);
        }

        return $this->getMissingSchoolTable($form);
    }
}
