<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class FormDataTableService
{
    private $withPagination = false;

    private $paginationItems = 15;

    private $withLinks = false;

    private function getTeacherQuery(Form $form): Builder
    {
        // Ετοίμασε το υποερώτημα
        $subQuery = DB::query()
            ->select(['record', 'name', 'am_afm', 'type', 'tid', 'created_at', 'updated_at'])
            ->fromSub(function (Builder $query) use ($form) {
                $query->from('teachers')
                    ->addSelect(['form_field_data.*', 'teachers.id as tid'])
                    ->selectRaw("'teacher' as type")
                    ->selectRaw("CONCAT(teachers.surname, ' ', teachers.name) as name")
                    ->selectRaw('IFNULL(teachers.am,teachers.afm) as am_afm')
                    ->leftJoin('form_field_data', 'form_field_data.teacher_id', '=', 'teachers.id')
                    ->leftJoin('form_fields', 'form_fields.id', '=', 'form_field_data.form_field_id')
                    ->whereIn(
                        'form_field_id',
                        $form
                            ->form_fields()
                            ->get(['id'])
                            ->flatten()
                            ->toArray()
                    )
                    ->union(function (Builder $query) use ($form) {
                        $query->from('other_teachers')
                            ->addSelect(['form_field_data.*', 'other_teachers.id as tid'])
                            ->selectRaw("'other_teacher' as type")
                            ->addSelect(['name', 'employeenumber as am_afm'])
                            ->leftJoin('form_field_data', 'form_field_data.other_teacher_id', '=', 'other_teachers.id')
                            ->leftJoin('form_fields', 'form_fields.id', '=', 'form_field_data.form_field_id')
                            ->whereIn(
                                'form_field_id',
                                $form
                                    ->form_fields()
                                    ->get(['id'])
                                    ->flatten()
                                    ->toArray()
                            );
                    });
            }, 'p')
            ->orderBy('name')
            ->orderBy('record');

        foreach ($form->form_fields as $field) {
            $subQuery->selectRaw("CASE WHEN form_field_id='{$field->id}' THEN data END AS '{$field->id}'");
        }

        // Δημιούργησε το συγκεντρωτικό πίνακα
        $pivot = DB::query()
            ->select(['p2.record', 'p2.name', 'p2.am_afm', 'p2.type', 'p2.tid', 'p2.created_at', 'p2.updated_at'])
            ->fromSub($subQuery, 'p2')
            ->groupBy(['name', 'record']);

        foreach ($form->form_fields as $field) {
            $pivot->selectRaw("GROUP_CONCAT(`p2`.`{$field->id}`) AS '{$field->id}'");
        }

        return $pivot;
    }

    private function getSchoolQuery(Form $form): Builder
    {
        // Ετοίμασε το υποερώτημα
        $subQuery = DB::query()
            ->select(['record', 'name', 'code', 'type', 'tid', 'created_at', 'updated_at'])
            ->fromSub(function (Builder $query) use ($form) {
                $query->from('schools')
                    ->addSelect(['form_field_data.*', 'schools.id as tid', 'name', 'code'])
                    ->selectRaw("'school' as type")
                    ->leftJoin('form_field_data', 'form_field_data.school_id', '=', 'schools.id')
                    ->leftJoin('form_fields', 'form_fields.id', '=', 'form_field_data.form_field_id')
                    ->whereIn(
                        'form_field_id',
                        $form
                            ->form_fields()
                            ->get(['id'])
                            ->flatten()
                            ->toArray()
                    )
                    ->orderBy('name')
                    ->orderBy('record');
            }, 'p');

        foreach ($form->form_fields as $field) {
            $subQuery->selectRaw("CASE WHEN form_field_id='{$field->id}' THEN data END AS '{$field->id}'");
        }

        // Δημιούργησε το συγκεντρωτικό πίνακα
        $pivot = DB::query()
            ->select(['p2.record', 'p2.name', 'p2.code', 'p2.type', 'p2.tid', 'p2.created_at', 'p2.updated_at'])
            ->fromSub($subQuery, 'p2')
            ->groupBy(['name', 'record']);

        foreach ($form->form_fields as $field) {
            $pivot->selectRaw("GROUP_CONCAT(`p2`.`{$field->id}`) AS '{$field->id}'");
        }

        return $pivot;
    }

    private function createTable(Form $form, string $type): array
    {
        if ($type == 'teacher') {
            $dataTableColumns = ['Εκπαιδευτικός', 'ΑΜ/ΑΦΜ'];
            $query = $this->getTeacherQuery($form);
        } else {
            $dataTableColumns = ['Σχολική μονάδα', 'Κωδ. σχολικής μονάδας'];
            $query = $this->getSchoolQuery($form);
        }

        // Έλεγξε αν θέλουμε σελιδοποίηση
        $links = null;
        if ($this->withPagination) {
            /** @var Paginator $result */
            $result = $query->paginate($this->paginationItems);
            $links = $result->links();
        } else {
            $result = $query->get();
        }

        foreach ($form->form_fields as $field) {
            array_push($dataTableColumns, $field->title);

            if ($field->type == FormField::TYPE_RADIO_BUTTON || $field->type == FormField::TYPE_SELECT) {
                $selections = json_decode($field->listvalues);

                // Μετέτρεψε την επιλογή σε τιμή
                $result = $result->map(function ($row) use ($selections, $field) {
                    foreach ($selections as $selection) {
                        if ($selection->id == $row->{$field->id}) {
                            $row->{$field->id} = $selection->value;
                        }
                    }

                    return $row;
                });
            } elseif ($field->type == FormField::TYPE_CHECKBOX) {
                $selections = json_decode($field->listvalues);

                // Μετέτρεψε την επιλογή σε τιμή
                $result = $result->map(function ($row) use ($selections, $field) {
                    // Αν δεν επέλεξε τίποτα επέστρεψε
                    if ($row->{$field->id} === null) {
                        return $row;
                    }

                    // Μπορεί να έχουμε επιλέξει παραπάνω από ένα
                    $data = json_decode($row->{$field->id});
                    $i = 0;
                    foreach ($data as $item) {
                        foreach ($selections as $selection) {
                            if ($selection->id == $item) {
                                if ($i === 0) {
                                    $row->{$field->id} = $selection->value;
                                } else {
                                    $row->{$field->id} .= ', '.$selection->value;
                                }
                            }
                        }
                        $i++;
                    }

                    return $row;
                });
            } elseif ($field->type == FormField::TYPE_FILE) {
                if ($this->withLinks) {
                    $result = $result->map(function ($row) use ($field, $form) {
                        $row->{$field->id} = [
                            'value' => $row->{$field->id},
                            'file' => true,
                            'link' => route('admin.report.download', [
                                $form->id,
                                $row->type,
                                $row->tid,
                                $row->record,
                                $field->id,
                            ]),
                        ];

                        return $row;
                    });
                }
            }
        }

        $dataTable = $result->map(function ($row) use ($form, $type) {
            if ($type === 'teacher') {
                $data = [
                    $row->name,
                    $row->am_afm,
                ];
            } else {
                $data = [
                    $row->name,
                    $row->code,
                ];
            }

            foreach ($form->form_fields as $field) {
                $data[] = $row->{$field->id};
            }

            $data[] = $row->created_at;
            $data[] = $row->updated_at;

            return $data;
        });

        array_push($dataTableColumns, 'Δημιουργήθηκε', 'Ενημερώθηκε');

        return [
            $dataTableColumns,
            $dataTable,
            $links,
        ];
    }

    public function create(Form $form): array
    {
        if ($form->for_teachers) {
            return $this->createTable($form, 'teacher');
        } else {
            return $this->createTable($form, 'school');
        }
    }

    public function useLinks(): self
    {
        $this->withLinks = true;

        return $this;
    }

    public function usePagination(?int $items = null): self
    {
        if ($items) {
            $this->paginationItems = $items;
        }

        $this->withPagination = true;

        return $this;
    }
}
