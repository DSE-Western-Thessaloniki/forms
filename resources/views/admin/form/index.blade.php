@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">Φόρμες</div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div class="btn-toolbar pb-2 justify-content-between" role="toolbar">
                        <div class="btn-group me-2">
                            @if(Auth::user()->roles->whereNotIn('name', ['User'])->count())
                            <a class="btn btn-primary" href="{{ route('admin.form.create') }}">
                                @icon('fas fa-plus') Δημιουργία φόρμας
                            </a>
                            @endif
                        </div>
                        <form id="search" method="GET" action="{{ route('admin.form.index') }}">
                            <div class="input-group align-items-center" role="group">
                                <div class="form-check me-2">
                                    <input class="form-check-input" type="checkbox" id="only_active" name="only_active" value="1" {{ ($only_active ?? 0) ? "checked" : "" }} />
                                    <label class="form-check-label" for="only_active">
                                    Εμφάνιση μόνο ενεργών φορμών
                                    </label>
                                </div>
                                <input type="text" class="form-control" placeholder="Κριτήρια αναζήτησης..." name="filter" value="{{ $filter }}">
                                <button type="submit" class="btn btn-primary ms-2" form="search">Αναζήτηση</button>
                            </div>
                        </form>
                    </div>

                    <div class="container">
                        @forelse ($forms as $form)
                            <div class="card my-2 shadow-sm">
                                <div class="card-header">
                                    <div class="container">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <a href="{{ route('admin.form.show', $form->id) }}">{{ $loop->iteration + $forms->firstItem() - 1 }}. {{ $form->title }}</a>
                                            </div>
                                            <div>
                                                <a class="btn btn-sm btn-primary mx-1" href="{{ route('admin.form.copy', $form) }}" data-bs-toggle="tooltip" data-placement="top" title="Δημιουργία αντιγράφου">@icon('fas fa-copy')</a>
                                                @if(Auth::user()->roles->whereNotIn('name', ['User'])->count() && !(Auth::user()->roles->where('name', 'Author')->count() && Auth::user()->id != $form->user->id))
                                                    <a class="btn btn-sm btn-danger mx-1" href="{{ route('admin.form.confirmDelete', $form) }}" data-bs-toggle="tooltip" data-placement="top" title="Διαγραφή">@icon('fas fa-trash-alt')</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class='col d-flex flex-column'>
                                            <div class='flex-grow-1'>
                                                <span class='pre-wrap'>{{ $form->notes }}</span>
                                            </div>
                                            <div class='pt-4'>
                                                <div class='row'>
                                                    <div class='col-10'>
                                                        <div class="row">
                                                            @php
                                                            // Πάρε το πλήθος των συμπληρωμένων πεδίων από τα σχολεία
                                                            $get_counts = DB::table('forms')
                                                                ->select(DB::raw('count(form_field_data.school_id) as answers'))
                                                                ->leftJoin('form_fields', 'form_fields.form_id', '=', 'forms.id')
                                                                ->leftJoin('form_field_data', 'form_field_data.form_field_id', '=', 'form_fields.id')
                                                                ->leftJoin('schools', 'schools.id', '=', 'form_field_data.school_id')
                                                                ->where('form_field_data.record', 0)
                                                                ->where('forms.id', $form->id)
                                                                ->where('schools.active', 1)
                                                                ->groupBy('form_fields.id')
                                                                ->get();
                                                            $field_answers = array_map(
                                                                function ($item) {
                                                                    return $item->answers;
                                                                },
                                                                $get_counts->unique()->toArray()
                                                            );

                                                            // Αν έχουμε παραπάνω από 1 αποτέλεσμα τότε
                                                            // κάποιο σχολείο δεν έχει συμπληρώσει όλα τα πεδία
                                                            $missing_fields = count($field_answers) > 1;
                                                            if ($field_answers) {
                                                                $forms_filled = max($field_answers);
                                                            } else {
                                                                $forms_filled = 0;
                                                            }

                                                            $school_categories = $form->school_categories()
                                                                ->withCount(['schools' => function ($query) {
                                                                    $query->where('active', 1);
                                                                }])
                                                                ->get();
                                                            $should_have = 0;
                                                            foreach ($school_categories as $school_category) {
                                                                $should_have += $school_category->schools_count;
                                                            }
                                                            $should_have += $form->schools_count;
                                                            $percent = 0;
                                                            if ($should_have > 0) {
                                                                $percent = round($forms_filled / $should_have * 100, 2);
                                                            }
                                                            @endphp
                                                            <div class='col-2 small'>Απάντησαν: {{ $forms_filled }}/{{ $should_have }}
                                                            @if($missing_fields)
                                                                <abbr title="Κάποια σχολεία δεν έχουν συμπληρώσει όλα τα πεδία">*</abbr>
                                                            @endif
                                                            </div>
                                                            <div class="col-10">
                                                                <div class="progress">
                                                                    <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">{{ $percent }}%</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="small pt-2">
                                                            Ημερομηνία δημιουργίας: {{ $form->created_at }}
                                                        </div>
                                                        @if($form->created_at != $form->updated_at)
                                                        <div class="small">
                                                            Ημερομηνία τροποποίησης: {{ $form->updated_at }}
                                                        </div>
                                                        @endif
                                                        <div class="small pt-2">
                                                            Δημιουργήθηκε από: {{ $form->user->name }}
                                                        </div>
                                                    </div>
                                                    <div class='col-2'>
                                                        Ενεργή:
                                                        @if($form->active)
                                                        <a href="{{ route('admin.form.active.toggle', $form) }}" class='btn btn-light m-1'><span class='text-success'>@icon('fas fa-check')</span></a>
                                                        @else
                                                        <a href="{{ route('admin.form.active.toggle', $form) }}" class='btn btn-light m-1'><span class='text-danger'>@icon('fas fa-times')</span></a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-2'>
                                            @if(Auth::user()->roles->whereNotIn('name', ['User'])->count() && !(Auth::user()->roles->where('name', 'Author')->count() && Auth::user()->id != $form->user->id))
                                            <a href="{{ route('admin.form.edit', $form->id) }}" class="btn btn-primary m-1 float-right">@icon('fas fa-edit') Επεξεργασία</a>
                                            @endif
                                            <a href="{{ route('admin.form.data', $form) }}" class="btn btn-success m-1 float-right">@icon('fas fa-table') Δεδομένα</a>
                                            <a href="{{ route('admin.form.missing', $form) }}" class="btn btn-secondary m-1 float-right">@icon('fas fa-exclamation') Απομένουν</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            Δεν βρέθηκαν φόρμες
                        @endforelse
                        <div class="row justify-content-md-center">
                            @php
                                $pagination_extra_fields = ['filter' => $filter];
                                if ($only_active) {
                                    $pagination_extra_fields += ['only_active' => 1];
                                }
                            @endphp
                            {{ $forms->appends($pagination_extra_fields)->links() }} <!-- Σελιδοποίηση -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
