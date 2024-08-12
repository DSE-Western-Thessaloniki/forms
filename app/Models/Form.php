<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class Form extends Model
{
    use HasFactory;
    use UsesUuid;

    // Table name
    // Not really needed as Laravel takes the plural form of the model as
    // a default name for the table...
    protected $table = 'forms';

    // Primary key
    public $primaryKey = 'id';

    // Timestamps
    public $timestamps = true;

    public function form_fields()
    {
        return $this->hasMany(FormField::class)->orderBy('sort_id');
    }

    public function schools()
    {
        return $this->belongsToMany(School::class);
    }

    public function school_categories()
    {
        return $this->belongsToMany(SchoolCategory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function data()
    {
        return $this->hasManyThrough(FormFieldData::class, FormField::class);
    }

    public static function fetchWithPagination(?string $filter, bool $only_active): LengthAwarePaginator
    {
        if ($filter && $only_active) {
            $forms = Form::orderBy('created_at', 'desc')
                ->withCount(['data' => function ($query) {
                    $query->where('record', 0);
                }])
                ->where('active', '1')
                ->where(function ($query) use ($filter) {
                    $query->where('id', 'like', '%'.$filter.'%')
                        ->orWhere('title', 'like', '%'.$filter.'%');
                })
                ->with('user')
                ->withCount(['schools' => function ($query) {
                    $query->where('active', 1);
                }])
                ->with('schools')
                ->with('school_categories')
                ->with('school_categories.schools')
                ->paginate(15);
        } elseif ($filter) {
            $forms = Form::orderBy('created_at', 'desc')
                ->withCount(['data' => function ($query) {
                    $query->where('record', 0);
                }])
                ->where('id', 'like', '%'.$filter.'%')
                ->orWhere('title', 'like', '%'.$filter.'%')
                ->with('user')
                ->withCount(['schools' => function ($query) {
                    $query->where('active', 1);
                }])
                ->with('schools')
                ->with('school_categories')
                ->with('school_categories.schools')
                ->paginate(15);
        } elseif ($only_active) {
            $forms = Form::orderBy('created_at', 'desc')
                ->withCount(['data' => function ($query) {
                    $query->where('record', 0);
                }])
                ->where('active', '1')
                ->with('user')
                ->withCount(['schools' => function ($query) {
                    $query->where('active', 1);
                }])
                ->with('schools')
                ->with('school_categories')
                ->with('school_categories.schools')
                ->paginate(15);
        } else {
            $forms = Form::orderBy('created_at', 'desc')
                ->withCount([
                    'data' => function ($query) {
                        $query->where('record', 0);
                    },
                ])
                ->with('user')
                ->withCount(['schools' => function ($query) {
                    $query->where('active', 1);
                }])
                ->with('schools')
                ->with('school_categories')
                ->with('school_categories.schools')
                ->paginate(15);
        }

        return $forms;
    }

    public static function fromRequest(Request $request): Form
    {
        // Create form
        $form = new Form;
        $form->title = $request->input('title');
        $form->notes = $request->input('notes');
        $form->user_id = Auth::id();
        $form->active = true;
        $form->multiple = $request->input('multiple_input') ? true : false;
        $form->for_teachers = intval($request->input('for_teachers'));
        $form->for_all_teachers = intval($request->input('for_all_teachers'));
        $form->save();

        return $form;
    }

    public function updateFromRequest(Request $request): void
    {
        // Update form
        $this->title = $request->input('title');
        $this->notes = $request->input('notes');
        $this->multiple = $request->input('multiple_input') ? true : false;
        $this->for_teachers = intval($request->input('for_teachers'));
        $this->for_all_teachers = intval($request->input('for_all_teachers'));
        $this->save();
    }
}
