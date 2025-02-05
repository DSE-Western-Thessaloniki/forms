<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class FormService
{
    private function fixFormFieldOptions(Collection $oldFields, Collection $newFields): void
    {
        // Διόρθωσε τα options όπου χρειάζεται για την εμφάνιση πεδίων
        $newFields->each(function ($field) use ($oldFields, $newFields) {
            $options = json_decode($field->options, true);
            if (! isset($options['show_when'])) {
                return;
            }

            for ($i = 0; $i < count($options['show_when']); $i++) {
                if (! isset($options['show_when'][$i]['active_field'])) {
                    continue;
                }
                // Το id του πεδίου είναι λάθος γιατί περιέχει το πεδίο της αρχικής φόρμας.
                // Κάνε σύνδεση με το νέο πεδίο
                $old_field_id = $options['show_when'][$i]['active_field'];
                $found = false;
                $idx = 0;
                while (! $found && $idx < count($oldFields)) {
                    if ($oldFields[$idx]->id == $old_field_id) {
                        $found = true;

                        break;
                    }
                    $idx++;
                }

                if ($found) {
                    $options['show_when'][$i]['active_field'] = $newFields[$idx]->id;
                    $field->options = json_encode($options);
                    $field->save();
                }
            }

        });
    }

    public function copyForm(Form $form): Form
    {
        // Δημιουργία αντιγράφου του αντικειμένου της φόρμας
        $form_clone = $form->replicate();
        $form_clone->user_id = Auth::user()->id;
        $form_clone->save();

        // Αντιγραφή των πεδίων
        $form_fields = $form->form_fields()->get();
        $new_form_fields = collect();
        foreach ($form_fields as $item) {
            $field = new FormField;
            $field->sort_id = $item->sort_id;
            $field->title = $item->title;
            $field->type = $item->type;
            $field->required = $item->required;
            $field->listvalues = $item->listvalues;
            $field->options = $item->options;
            $result = $form_clone->form_fields()->save($field);

            // Αν δεν απέτυχε η αποθήκευση
            if ($result) {
                $new_form_fields->push($field);
            } else {
                // Αφαίρεσε το πεδίο από την φόρμα.
                // Προτιμότερο από το να έχουμε ένα πεδίο που δεν αντιστοιχεί
                // πουθενά.
                $field->delete();
            }
        }

        $this->fixFormFieldOptions($form_fields, $new_form_fields);

        foreach ($form->school_categories()->get() as $category) {
            $form_clone->school_categories()->attach($category);
        }

        foreach ($form->schools()->get() as $school) {
            $form_clone->schools()->attach($school);
        }

        return $form_clone;
    }
}
