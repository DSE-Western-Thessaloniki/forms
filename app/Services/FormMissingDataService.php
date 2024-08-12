<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Teacher;

class FormMissingDataService
{
    private function getMissingTeacherTable(Form $form)
    {
        $dataTableColumns = ['Εκπαιδευτικός', 'ΑΜ/ΑΦΜ', 'Τηλέφωνο'];

        // Βρες όλους τους εκπαιδευτικούς που θα έπρεπε να απαντήσουν
        $teachers = Teacher::where('active', 1)->get();
        $data = $form->data()->get();
        $answer = [];
        $data->each(function ($item, $key) use (&$answer) {
            $answer[$item->teacher_id] = true;
        });
        $seen = [];
        $filtered_teachers = $teachers->filter(function ($teacher, $key) use ($answer, &$seen) {
            if (in_array($teacher, $seen) || isset($answer[$teacher->id])) {
                return false;
            }

            array_push($seen, $teacher);

            return true;
        });

        $data = [];
        array_push($data, $dataTableColumns);
        foreach ($filtered_teachers as $teacher) {
            array_push($data, [$teacher->name, $teacher->code, '']);
        }

        return $data;
    }

    private function getMissingSchoolTable(Form $form)
    {
        $dataTableColumns = ['Σχολική μονάδα', 'Κωδ. σχολικής μονάδας', 'Τηλέφωνο'];

        // Βρες όλα τα σχολεία που θα έπρεπε να απαντήσουν
        $schools = $form->schools()->where('active', 1)->get();
        foreach ($form->school_categories()->get() as $category) {
            $schools = $schools->merge($category->schools()->where('active', 1)->get());
        }
        $schools = $schools->unique('id');
        $data = $form->data()->get();
        $answer = [];
        $data->each(function ($item, $key) use (&$answer) {
            $answer[$item->school_id] = true;
        });
        $seen = [];
        $filtered_schools = $schools->filter(function ($school, $key) use ($answer, &$seen) {
            if (in_array($school, $seen) || isset($answer[$school->id])) {
                return false;
            }

            array_push($seen, $school);

            return true;
        });

        $data = [];
        array_push($data, $dataTableColumns);
        foreach ($filtered_schools as $school) {
            array_push($data, [$school->name, $school->code, $school->telephone]);
        }

        return $data;
    }

    public function getMissingTable(Form $form)
    {
        if ($form->for_teachers) {
            return $this->getMissingTeacherTable($form);
        } else {
            return $this->getMissingSchoolTable($form);
        }
    }
}
