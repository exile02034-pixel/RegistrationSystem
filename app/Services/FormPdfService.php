<?php

namespace App\Services;

use App\Models\FormSubmission;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

class FormPdfService
{
    public function streamPdf(FormSubmission $submission, string $section): Response
    {
        $pdf = $this->generatePdf($submission, $section);

        return response($pdf['binary'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$pdf['name'].'"',
        ]);
    }

    public function downloadPdf(FormSubmission $submission, string $section): Response
    {
        $pdf = $this->generatePdf($submission, $section);

        return response($pdf['binary'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$pdf['name'].'"',
        ]);
    }

    public function mergeToPdf(FormSubmission $submission, array $sections): Response
    {
        $sections = array_values(array_unique($sections));

        $sectionsData = collect($sections)
            ->map(function (string $section) use ($submission): array {
                $this->assertSectionIsAllowed($submission, $section);

                return $this->buildSectionData($submission, $section);
            })
            ->all();

        if ($sectionsData === []) {
            throw new InvalidArgumentException('No valid sections provided.');
        }

        $html = view('pdf.merged', [
            'submission' => $submission,
            'sections' => $sectionsData,
        ])->render();

        $dompdf = $this->makeDompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="selected-forms.pdf"',
        ]);
    }

    public function deleteSection(FormSubmission $submission, string $section): void
    {
        $this->assertSectionIsAllowed($submission, $section);

        $submission->fields()->where('section', $section)->delete();

        $submission->update([
            'status' => $this->hasAllSectionsData($submission) ? 'completed' : 'incomplete',
        ]);
    }

    public function hasSectionData(FormSubmission $submission, string $section): bool
    {
        $this->assertSectionIsAllowed($submission, $section);

        return $submission->fields()
            ->where('section', $section)
            ->whereNotNull('field_value')
            ->where('field_value', '!=', '')
            ->exists();
    }

    public function expectedSectionsForCompanyType(string $companyType): array
    {
        $alwaysIncluded = ['client_information', 'treasurer_details'];
        $sectionMap = config('registration_forms.company_type_sections', []);
        $companySpecific = $sectionMap[$companyType] ?? null;

        return array_values(array_filter(array_merge($alwaysIncluded, [$companySpecific])));
    }

    private function generatePdf(FormSubmission $submission, string $section): array
    {
        $this->assertSectionIsAllowed($submission, $section);
        $sectionData = $this->buildSectionData($submission, $section);

        $html = view('pdf.partials.form-template', [
            'title' => $sectionData['title'],
            'rows' => $sectionData['rows'],
            'submission' => $submission,
            'sectionView' => $sectionData['view'],
        ])->render();

        $dompdf = $this->makeDompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return [
            'binary' => $dompdf->output(),
            'name' => str_replace('_', '-', $section).'.pdf',
        ];
    }

    private function buildSectionData(FormSubmission $submission, string $section): array
    {
        $fields = $submission->fields()
            ->where('section', $section)
            ->get()
            ->pluck('field_value', 'field_name');

        $sectionConfig = Arr::get(config('registration_forms.sections', []), $section, []);
        $rows = collect($sectionConfig['fields'] ?? [])
            ->map(function (array $field) use ($fields): array {
                $value = $fields->get($field['name']);

                return [
                    'label' => $field['label'] ?? Str::title(str_replace('_', ' ', $field['name'] ?? '')),
                    'value' => is_string($value) && trim($value) !== '' ? $value : '—',
                ];
            })
            ->all();

        return [
            'section' => $section,
            'title' => $sectionConfig['label'] ?? Str::title(str_replace('_', ' ', $section)),
            'view' => $this->getViewForSection($section),
            'rows' => $rows,
        ];
    }

    private function makeDompdf(): Dompdf
    {
        $options = new Options;
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('isRemoteEnabled', false);

        return new Dompdf($options);
    }

    private function getViewForSection(string $section): string
    {
        return match ($section) {
            'client_information' => 'pdf.client-information',
            'treasurer_details' => 'pdf.treasurer-details',
            'opc_details' => 'pdf.opc-details',
            'proprietorship' => 'pdf.proprietorship-details',
            'regular_corporation' => 'pdf.regular-corporation-details',
            default => throw new InvalidArgumentException("Unknown section: {$section}"),
        };
    }

    private function assertSectionIsAllowed(FormSubmission $submission, string $section): void
    {
        $allowed = $this->expectedSectionsForCompanyType($submission->company_type);

        if (! in_array($section, $allowed, true)) {
            throw new InvalidArgumentException("Unknown section: {$section}");
        }
    }

    private function hasAllSectionsData(FormSubmission $submission): bool
    {
        $sectionData = $submission->fields()
            ->whereNotNull('field_value')
            ->where('field_value', '!=', '')
            ->get()
            ->groupBy('section');

        $requiredSections = $this->expectedSectionsForCompanyType($submission->company_type);

        return collect($requiredSections)->every(fn (string $section): bool => $sectionData->has($section));
    }
}
