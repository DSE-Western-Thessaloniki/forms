<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class FormService
{
    /**
     * Διόρθωση πεδίου options μετά την πρώτη αποθήκευση της φόρμας. Επειδή τα
     * πεδία δεν είχαν αποθηκευτεί ακόμα, αυτή τη στιγμή το id που είναι
     * αποθηκευμένο αντιστοιχεί στη σειρά εμφάνισής του στη φόρμα. Η συνάρτηση
     * βρίσκει το σωστό id και το αποθηκεύει στο πεδίο options.
     */
    public function fixFormFieldOptionsAfterStore(Form $form): void
    {
        $formFields = $form->form_fields()->get();

        // Διόρθωσε τα options όπου χρειάζεται για την εμφάνιση πεδίων
        $formFields->each(function ($field) use ($formFields) {
            $options = json_decode($field->options, true);
            if (! isset($options['show_when'])) {
                return;
            }

            for ($i = 0; $i < count($options['show_when']); $i++) {
                if (! isset($options['show_when'][$i]['active_field'])) {
                    continue;
                }

                $idx = $options['show_when'][$i]['active_field'];
                $options['show_when'][$i]['active_field'] = $formFields[$idx]->id;
                $field->options = json_encode($options);
                $field->save();
            }

        });
    }

    /**
     * Διόρθωση πεδίου options μετά την επεξεργασία της φόρμας. Επειδή τα νέα
     * πεδία δεν είχαν αποθηκευτεί ακόμα, αυτή τη στιγμή το id που είναι
     * αποθηκευμένο αντιστοιχεί στη σειρά εμφάνισής του νέου πεδίου στη φόρμα.
     * Η συνάρτηση βρίσκει το σωστό id και το αποθηκεύει στο πεδίο options.
     */
    public function fixFormFieldOptionsAfterUpdate(Form $form): void
    {
        $formFields = $form->form_fields()->get();

        // Διόρθωσε τα options όπου χρειάζεται για την εμφάνιση πεδίων
        $formFields->each(function ($field, $fieldIndex) use ($formFields) {
            $options = json_decode($field->options, true);
            if (! isset($options['show_when'])) {
                return;
            }

            for ($i = 0; $i < count($options['show_when']); $i++) {
                if (! isset($options['show_when'][$i]['active_field'])) {
                    continue;
                }

                if (! count($formFields->filter(fn ($item) => $item->id == $options['show_when'][$i]['active_field']))) {
                    // Το id του πεδίου είναι λάθος γιατί περιέχει id που δεν υπάρχει.
                    // Κάνε σύνδεση με το νέο πεδίο
                    $old_field_id = $options['show_when'][$i]['active_field'];

                    $idx = 0;
                    while ($old_field_id > $formFields[$idx]->id && $idx < count($formFields)) {
                        $idx++;
                    }

                    if ($idx < count($formFields)) {
                        $options['show_when'][$i]['active_field'] = $formFields[$idx]->id;
                        $field->options = json_encode($options);
                        $field->save();
                    } else {
                        // Το πεδίο διαγράφηκε
                        $options['show_when'][$i]['visible'] = 'always';
                        $field->options = json_encode($options);
                        $field->save();
                    }
                }
            }

        });
    }

    private function fixFormFieldOptionsAfterCopy(Collection $oldFields, Collection $newFields): void
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

        $this->fixFormFieldOptionsAfterCopy($form_fields, $new_form_fields);

        foreach ($form->school_categories()->get() as $category) {
            $form_clone->school_categories()->attach($category);
        }

        foreach ($form->schools()->get() as $school) {
            $form_clone->schools()->attach($school);
        }

        return $form_clone;
    }
}
