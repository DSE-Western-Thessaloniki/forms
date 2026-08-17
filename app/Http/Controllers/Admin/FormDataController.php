<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Services\FileService;
use App\Services\FormDataTableService;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class FormDataController extends Controller
{
    public function __construct(private FormDataTableService $formDataTableService, private FileService $fileService) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Form $form, Request $request, #[RouteParameter('format')] ?string $format): BinaryFileResponse|View
    {
        return match ($format) {
            'csv' => $this->handleCSV($form),
            'xlsx' => $this->handleXLSX($form),
            default => $this->handleDefault($form)
        };
    }

    private function handleCSV(Form $form): BinaryFileResponse
    {
        $form->load('form_fields');

        [$dataTableColumns, $dataTable] = $this->formDataTableService->create($form);

        $fname = $this->fileService->createCSV($form, $dataTable, $dataTableColumns);

        return response()->download($fname);
    }

    private function handleXLSX(Form $form): BinaryFileResponse
    {
        $form->load('form_fields');

        [$dataTableColumns, $dataTable] = $this->formDataTableService->create($form);

        $fname = $this->fileService->createXLSX($form, $dataTable, $dataTableColumns);

        return response()->download($fname);
    }

    private function handleDefault(Form $form): View
    {
        $noPagination = request('noPagination');
        $form->load('form_fields');

        if ($noPagination === 1) {
            [$dataTableColumns, $dataTable, $links] = $this->formDataTableService
                ->useLinks()
                ->create($form);
        } else {
            [$dataTableColumns, $dataTable, $links] = $this->formDataTableService
                ->useLinks()
                ->usePagination(50)
                ->create($form);
        }

        return view('admin.form.data')
            ->with('dataTable', $dataTable)
            ->with('dataTableColumns', $dataTableColumns)
            ->with('form', $form)
            ->with('links', $links);
    }
}
