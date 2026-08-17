<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Services\FileService;
use App\Services\FormMissingDataService;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class FormDataMissingController extends Controller
{
    public function __construct(private FormMissingDataService $formMissingDataService, private FileService $fileService) {}

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

        $data = $this->formMissingDataService->getMissingTable($form);

        $fname = $this->fileService->createCSV($form, $data, suffix: '-missing');

        return response()->download($fname);
    }

    private function handleXLSX(Form $form): BinaryFileResponse
    {
        $form->load('form_fields');

        $data = $this->formMissingDataService->getMissingTable($form);

        $fname = $this->fileService->createXLSX($form, $data, suffix: '-missing');

        return response()->download($fname);
    }

    private function handleDefault(Form $form): View
    {
        $data = $this->formMissingDataService->getMissingTable($form);

        return view('admin.form.missing')
            ->with('form', $form)
            ->with('missing_data', $data);
    }
}
