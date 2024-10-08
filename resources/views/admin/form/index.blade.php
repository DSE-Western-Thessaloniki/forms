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
                                @if (Auth::user()->roles->whereNotIn('name', ['User'])->count())
                                    <a class="btn btn-primary" href="{{ route('admin.form.create') }}"
                                        test-data-id="btn-create-form">
                                        @icon('fas fa-plus') Δημιουργία φόρμας
                                    </a>
                                @endif
                            </div>
                            <form id="search" method="GET" action="{{ route('admin.form.index') }}">
                                <div class="input-group align-items-center" role="group">
                                    <div class="form-check me-2">
                                        <input type="hidden" name="only_active" value="0" />
                                        <input class="form-check-input" type="checkbox" id="only_active" name="only_active"
                                            value="1" {{ $only_active ?? 0 ? 'checked' : '' }} />
                                        <label class="form-check-label" for="only_active">
                                            Εμφάνιση μόνο ενεργών φορμών
                                        </label>
                                    </div>
                                    <input type="search" class="form-control" placeholder="Κριτήρια αναζήτησης..."
                                        name="filter" value="{{ $filter }}">
                                    <button type="submit" class="btn btn-primary ms-2" form="search">Αναζήτηση</button>
                                </div>
                            </form>
                        </div>

                        <div class="container">
                            @forelse ($forms as $form)
                                <div class="card my-2 shadow-sm">
                                    @php
                                        if ($form->active) {
                                            $active_class = 'card-header-active';
                                        } else {
                                            $active_class = 'card-header-inactive';
                                        }
                                    @endphp
                                    <div class="card-header {{ $active_class }}">
                                        <div class="container p-0">
                                            <div class="d-flex align-items-center">
                                                <button type="button" class="btn btn-sm btn-primary me-2"
                                                    onclick="navigator.clipboard.writeText('{!! route('report.edit', $form->id) !!}')"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    data-bs-title="Αντιγραφή συνδέσμου φόρμας προς συμπλήρωση">
                                                    @icon('fas fa-link')
                                                </button>
                                                <div class='me-3 mb-0 h4'>
                                                    @if ($form->for_teachers)
                                                        @icon('chalkboard-user')
                                                    @else
                                                        @icon('school')
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <a href="{{ route('admin.form.show', $form->id) }}">{{ $loop->iteration + $forms->firstItem() - 1 }}.
                                                        {{ $form->title }}</a>
                                                </div>
                                                <div>
                                                    <a class="btn btn-sm btn-primary mx-1"
                                                        href="{{ route('admin.form.copy', $form) }}"
                                                        data-bs-toggle="tooltip" data-placement="top"
                                                        title="Δημιουργία αντιγράφου">@icon('fas fa-copy')</a>
                                                    @if (Auth::user()->roles->whereNotIn('name', ['User'])->count() && !(Auth::user()->roles->where('name', 'Author')->count() && Auth::user()->id != $form->user->id))
                                                        <a class="btn btn-sm btn-danger mx-1"
                                                            href="{{ route('admin.form.confirmDelete', $form) }}"
                                                            data-bs-toggle="tooltip" data-placement="top"
                                                            title="Διαγραφή">@icon('fas fa-trash-alt')</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class='col d-flex flex-column'>
                                                <div class='flex-grow-1'>
                                                    <span class='pre-wrap'>{!! Str::replace('<a ', '<a target="_blank" ', Str::of($form->notes)->markdown(['html_input' => 'strip'])) !!}</span>
                                                </div>
                                                <div class='pt-4'>
                                                    <div class='row'>
                                                        <div class='col-10'>
                                                            <div class="row">
                                                                @php
                                                                    if (!$form->for_teachers) {
                                                                        // Πάρε το πλήθος των συμπληρωμένων πεδίων από τα σχολεία
                                                                        $get_counts = DB::table('forms')
                                                                            ->select(
                                                                                DB::raw(
                                                                                    'count(form_field_data.school_id) as answers',
                                                                                ),
                                                                            )
                                                                            ->leftJoin(
                                                                                'form_fields',
                                                                                'form_fields.form_id',
                                                                                '=',
                                                                                'forms.id',
                                                                            )
                                                                            ->leftJoin(
                                                                                'form_field_data',
                                                                                'form_field_data.form_field_id',
                                                                                '=',
                                                                                'form_fields.id',
                                                                            )
                                                                            ->leftJoin(
                                                                                'schools',
                                                                                'schools.id',
                                                                                '=',
                                                                                'form_field_data.school_id',
                                                                            )
                                                                            ->where('form_field_data.record', 0)
                                                                            ->where('forms.id', $form->id)
                                                                            ->where('schools.active', 1)
                                                                            ->groupBy('form_fields.id')
                                                                            ->get();
                                                                        $field_answers = array_map(function ($item) {
                                                                            return $item->answers;
                                                                        }, $get_counts->unique()->toArray());

                                                                        // Αν έχουμε παραπάνω από 1 αποτέλεσμα τότε
                                                                        // κάποιο σχολείο δεν έχει συμπληρώσει όλα τα πεδία
                                                                        $missing_fields = count($field_answers) > 1;
                                                                        if ($field_answers) {
                                                                            $forms_filled = max($field_answers);
                                                                        } else {
                                                                            $forms_filled = 0;
                                                                        }

                                                                        $all_school_ids = $form->school_categories->flatMap(
                                                                            function ($category) {
                                                                                return $category->schools
                                                                                    ->where('active', 1)
                                                                                    ->pluck('id');
                                                                            },
                                                                        );
                                                                        $all_school_ids = $all_school_ids
                                                                            ->merge(
                                                                                $form->schools
                                                                                    ->where('active', 1)
                                                                                    ->pluck('id'),
                                                                            )
                                                                            ->unique();

                                                                        $result = DB::table('form_field_data')
                                                                            ->select('school_id')
                                                                            ->leftJoin(
                                                                                'form_fields',
                                                                                'form_field_data.form_field_id',
                                                                                'form_fields.id',
                                                                            )
                                                                            ->where('form_id', $form->id)
                                                                            ->where('record', 0)
                                                                            ->get();
                                                                        $answered = $result
                                                                            ->map(function ($row) {
                                                                                return $row->school_id;
                                                                            })
                                                                            ->unique();
                                                                        $answered = $all_school_ids
                                                                            ->intersect($answered)
                                                                            ->count();

                                                                        $should_have = $all_school_ids->count();
                                                                        $percent = 0;
                                                                        if ($should_have > 0) {
                                                                            $percent = round(
                                                                                ($answered / $should_have) * 100,
                                                                                2,
                                                                            );
                                                                        }
                                                                    } else {
                                                                        // Πάρε το πλήθος των συμπληρωμένων πεδίων από τους εκπαιδευτικούς
                                                                        $get_counts = DB::table('forms')
                                                                            ->select(
                                                                                DB::raw(
                                                                                    'count(form_field_data.teacher_id) as answers',
                                                                                ),
                                                                            )
                                                                            ->leftJoin(
                                                                                'form_fields',
                                                                                'form_fields.form_id',
                                                                                '=',
                                                                                'forms.id',
                                                                            )
                                                                            ->leftJoin(
                                                                                'form_field_data',
                                                                                'form_field_data.form_field_id',
                                                                                '=',
                                                                                'form_fields.id',
                                                                            )
                                                                            ->leftJoin(
                                                                                'teachers',
                                                                                'teachers.id',
                                                                                '=',
                                                                                'form_field_data.teacher_id',
                                                                            )
                                                                            ->where('form_field_data.record', 0)
                                                                            ->where('forms.id', $form->id)
                                                                            ->where('teachers.active', 1)
                                                                            ->groupBy('form_fields.id')
                                                                            ->get();
                                                                        $get_other_counts = DB::table('forms')
                                                                            ->select(
                                                                                DB::raw(
                                                                                    'count(form_field_data.other_teacher_id) as answers',
                                                                                ),
                                                                            )
                                                                            ->leftJoin(
                                                                                'form_fields',
                                                                                'form_fields.form_id',
                                                                                '=',
                                                                                'forms.id',
                                                                            )
                                                                            ->leftJoin(
                                                                                'form_field_data',
                                                                                'form_field_data.form_field_id',
                                                                                '=',
                                                                                'form_fields.id',
                                                                            )
                                                                            ->leftJoin(
                                                                                'other_teachers',
                                                                                'other_teachers.id',
                                                                                '=',
                                                                                'form_field_data.other_teacher_id',
                                                                            )
                                                                            ->where('form_field_data.record', 0)
                                                                            ->where('forms.id', $form->id)
                                                                            ->groupBy('form_fields.id')
                                                                            ->get();

                                                                        $field_answers = array_map(function ($item) {
                                                                            return $item->answers;
                                                                        }, $get_counts->unique()->toArray());

                                                                        $other_field_answers = array_map(function (
                                                                            $item,
                                                                        ) {
                                                                            return $item->answers;
                                                                        }, $get_other_counts->unique()->toArray());

                                                                        // Αν έχουμε παραπάνω από 1 αποτέλεσμα τότε
                                                                        // κάποιος εκπαιδευτικός δεν έχει συμπληρώσει όλα τα πεδία
                                                                        $missing_fields =
                                                                            count($field_answers) > 1 ||
                                                                            count($other_field_answers) > 1;
                                                                        $forms_filled = 0;
                                                                        if ($field_answers) {
                                                                            $forms_filled += max($field_answers);
                                                                        }
                                                                        if ($other_field_answers) {
                                                                            $forms_filled += max($other_field_answers);
                                                                        }
                                                                        $answered = $forms_filled;

                                                                        if ($form->for_all_teachers) {
                                                                            $should_have = '∞';
                                                                            $percent = 0;
                                                                        } else {
                                                                            $should_have = App\Models\Teacher::where(
                                                                                'active',
                                                                                1,
                                                                            )->count('id');
                                                                            $percent = 0;
                                                                            if ($should_have > 0) {
                                                                                $percent = round(
                                                                                    ($forms_filled / $should_have) *
                                                                                        100,
                                                                                    2,
                                                                                );
                                                                            }
                                                                        }
                                                                    }
                                                                @endphp
                                                                <div class='col-2 small'>Απάντησαν:
                                                                    {{ $answered }}/{{ $should_have }}
                                                                    @if ($missing_fields && !$form->for_teachers)
                                                                        <abbr
                                                                            title="Κάποια σχολεία δεν έχουν συμπληρώσει όλα τα πεδία">*</abbr>
                                                                    @endif
                                                                    @if ($missing_fields && $form->for_teachers)
                                                                        <abbr
                                                                            title="Κάποιοι εκπαιδευτικοί δεν έχουν συμπληρώσει όλα τα πεδία">*</abbr>
                                                                    @endif
                                                                </div>
                                                                <div class="col-10">
                                                                    <div class="progress">
                                                                        <div class="progress-bar" role="progressbar"
                                                                            style="width: {{ $percent }}%"
                                                                            aria-valuenow="{{ $percent }}"
                                                                            aria-valuemin="0" aria-valuemax="100">
                                                                            {{ $percent }}%</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="small pt-2">
                                                                Ημερομηνία δημιουργίας: {{ $form->created_at }}
                                                            </div>
                                                            @if ($form->created_at != $form->updated_at)
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
                                                            @if ($form->active)
                                                                <a href="{{ route('admin.form.active.toggle', $form) }}"
                                                                    class='btn btn-light m-1'><span
                                                                        class='text-success'>@icon('fas fa-check')</span></a>
                                                            @else
                                                                <a href="{{ route('admin.form.active.toggle', $form) }}"
                                                                    class='btn btn-light m-1'><span
                                                                        class='text-danger'>@icon('fas fa-times')</span></a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='col-2'>
                                                @if (Auth::user()->roles->whereNotIn('name', ['User'])->count() && !(Auth::user()->roles->where('name', 'Author')->count() && Auth::user()->id != $form->user->id))
                                                    <a href="{{ route('admin.form.edit', $form->id) }}"
                                                        class="btn btn-primary m-1 float-right">@icon('fas fa-edit')
                                                        Επεξεργασία</a>
                                                @endif
                                                <a href="{{ route('admin.form.data', $form) }}"
                                                    class="btn btn-success m-1 float-right">@icon('fas fa-table') Δεδομένα</a>

                                                @if (!$form->for_all_teachers)
                                                    <a href="{{ route('admin.form.missing', $form) }}"
                                                        class="btn btn-secondary m-1 float-right">@icon('fas fa-exclamation')
                                                        Απομένουν</a>
                                                @endif
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
                                {{ $forms->appends($pagination_extra_fields)->links() }}
                                <!-- Σελιδοποίηση -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
