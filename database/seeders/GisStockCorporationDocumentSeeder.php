<?php

namespace Database\Seeders;

use App\Models\RegistrationLink;
use App\Services\DocumentGenerationService;
use Illuminate\Database\Seeder;

class GisStockCorporationDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documentService = app(DocumentGenerationService::class);
        $seedEmail = (string) env('GIS_SEED_EMAIL', 'gis.demo@example.com');
        $seedToken = (string) env('GIS_SEED_TOKEN', 'gis-stock-corp-seed-token');
        $targetRegistrationId = env('GIS_SEED_REGISTRATION_ID');

        $registrationLink = null;

        if (is_string($targetRegistrationId) && trim($targetRegistrationId) !== '') {
            $registrationLink = RegistrationLink::query()->whereKey(trim($targetRegistrationId))->first();
        }

        if ($registrationLink === null) {
            $registrationLink = RegistrationLink::query()
                ->where('company_type', 'corp')
                ->latest('created_at')
                ->first();
        }

        if ($registrationLink === null) {
            $registrationLink = RegistrationLink::query()->updateOrCreate(
                ['email' => $seedEmail],
                [
                    'token' => $seedToken,
                    'company_type' => 'corp',
                    'status' => 'completed',
                ],
            );
        }

        $generatedDocument = $documentService->generate(
            registrationLink: $registrationLink,
            documentType: DocumentGenerationService::GIS_STOCK_CORPORATION,
            fields: $this->payload(),
            generatedBy: null,
        );

        $this->command?->info('GIS seed registration email: '.$registrationLink->email);
        $this->command?->info('GIS seed registration id: '.$registrationLink->id);
        $this->command?->info('Generated document id: '.$generatedDocument->id);
        $this->command?->info('Open registration page: /admin/registration/'.$registrationLink->id);
        $this->command?->info('View generated PDF: /admin/registration/'.$registrationLink->id.'/documents/'.$generatedDocument->id.'/view');
        $this->command?->info('Generated PDF path: storage/app/private/'.$generatedDocument->pdf_path);
    }

    private function payload(): array
    {
        return [
            'step_1' => [
                'corporate_name' => 'ACME Trading Corporation',
                'business_trade_name' => 'ACME Trade',
                'sec_registration_number' => 'CS2026-000123',
                'date_registered' => '2025-04-10',
                'fiscal_year_end' => '2025-12-31',
                'corporate_tin' => '123-456-789-000',
                'principal_office_address' => '482 Purok 4 San Juan Nepomuceno, Betis, Guagua, Pampanga',
                'business_address' => '482 Purok 4 San Juan Nepomuceno, Betis, Guagua, Pampanga',
                'email' => 'corporate@example.com',
                'alternate_email' => 'alternate@example.com',
                'official_mobile' => '09171234567',
                'alternate_mobile' => '09998887777',
                'website_url' => 'https://example.com',
                'fax_number' => '045-123-4567',
                'primary_purpose' => 'Wholesale and retail trading of consumer goods.',
                'industry_classification' => 'Retail sale via internet',
                'geographical_code' => 'PH-0354',
                'external_auditor_name' => 'ABC Auditing Firm',
                'sec_accreditation_number' => 'SEC-AUD-2026-001',
                'telephone' => '045-987-6543',
                'meeting_date_annual' => '2026-03-21',
                'meeting_date_actual' => '2026-03-21',
                'meeting_month' => 'March',
                'intercompany_parent_company' => 'ACME Holdings Inc.',
                'intercompany_parent_sec_no' => 'CS2018-999999',
                'intercompany_parent_address' => 'Makati City, Philippines',
                'intercompany_subsidiary' => 'ACME Logistics Corp.',
                'intercompany_subsidiary_sec_no' => 'CS2020-777777',
                'intercompany_subsidiary_address' => 'Clark Freeport, Pampanga',
            ],
            'step_2' => [
                'amla_covered' => true,
                'cdd_complied' => true,
                'amla_types' => [],
                'amla_detailed' => [
                    'one' => ['a' => false, 'b' => false, 'c' => false, 'd' => false, 'e' => false, 'f' => false, 'g' => false, 'h' => false, 'i' => false, 'j' => false, 'k' => false],
                    'two' => ['a' => false, 'b' => false, 'c' => false, 'd' => false, 'e' => false, 'f' => false, 'g' => false, 'h' => false, 'i' => false, 'j' => false],
                    'three' => ['a' => true, 'b' => false, 'c' => false, 'd' => false, 'e' => false, 'f' => false, 'g' => false, 'h' => false, 'i' => false, 'j' => false, 'k' => false, 'l' => false, 'm' => false, 'n' => false],
                    'four' => false,
                    'five' => false,
                    'six' => ['a' => false, 'b' => false, 'c' => true, 'd' => false],
                    'seven' => ['a' => false, 'b' => false, 'c' => false, 'd' => false],
                    'eight' => false,
                ],
                'amla_other_details' => 'Covered due to securities-related services.',
                'nature_of_business' => 'E-commerce and general merchandise trading',
            ],
            'step_3' => [
                'authorized_capital_stock' => '10,000,000.00',
                'subscribed_capital_stock' => '6,500,000.00',
                'paid_up_capital_stock' => '4,000,000.00',
                'authorized_rows' => $this->capitalRows([
                    ['Common', 100000, '100.00', '10,000,000.00'],
                    ['Preferred', 10000, '100.00', '1,000,000.00'],
                    ['Redeemable', 5000, '100.00', '500,000.00'],
                ]),
                'subscribed_filipino_rows' => $this->subscribedRows([
                    [8, 'Common', 55000, 10000, '100.00', '5,500,000.00', '84.62'],
                    [2, 'Preferred', 5000, 1000, '100.00', '500,000.00', '7.69'],
                ]),
                'subscribed_foreign_rows' => $this->subscribedRows([
                    [1, 'Common', 3000, 500, '100.00', '300,000.00', '4.62'],
                    [1, 'Preferred', 2000, 500, '100.00', '200,000.00', '3.08'],
                ]),
                'paidup_filipino_rows' => $this->paidUpRows([
                    [8, 'Common', 40000, '100.00', '4,000,000.00', '80.00'],
                    [2, 'Preferred', 5000, '100.00', '500,000.00', '10.00'],
                ]),
                'paidup_foreign_rows' => $this->paidUpRows([
                    [1, 'Common', 3000, '100.00', '300,000.00', '6.00'],
                    [1, 'Preferred', 2000, '100.00', '200,000.00', '4.00'],
                ]),
                'percentage_foreign_equity' => '10%',
                'total_subscribed_capital' => '6,500,000.00',
                'total_paid_up_capital' => '5,000,000.00',
            ],
            'step_4' => $this->directorRows(),
            'step_5' => [
                'total_stockholders' => '20',
                'stockholders_with_100_plus' => '12',
                'total_assets' => '12,000,000.00',
                'rows' => $this->stockholderRows(1),
            ],
            'step_6' => [
                'rows' => $this->stockholderRows(8),
            ],
            'step_7' => [
                'rows' => $this->stockholderRows(15),
                'others_count' => '6',
            ],
            'step_8' => [
                'investment_stocks' => '1,500,000.00',
                'investment_bonds' => '350,000.00',
                'investment_loans_advances' => '200,000.00',
                'investment_treasury_bills' => '175,000.00',
                'investment_others' => '100,000.00',
                'investment_board_resolution_date' => '2026-01-20',
                'secondary_purpose_activity' => 'Warehousing and logistics support',
                'secondary_purpose_board_resolution_date' => '2026-02-10',
                'secondary_purpose_ratification_date' => '2026-02-20',
                'treasury_shares_count' => '1000',
                'treasury_shares_percent' => '1%',
                'retained_earnings' => '2,100,000.00',
                'dividend_cash_amount' => '450,000.00',
                'dividend_cash_date' => '2025-12-20',
                'dividend_stock_amount' => '120,000.00',
                'dividend_stock_date' => '2025-12-20',
                'dividend_property_amount' => '0.00',
                'dividend_property_date' => '',
                'additional_shares' => [
                    ['date' => '2025-05-15', 'no_of_shares' => '5000', 'amount' => '500,000.00'],
                    ['date' => '2025-08-10', 'no_of_shares' => '3000', 'amount' => '300,000.00'],
                    ['date' => '2025-10-05', 'no_of_shares' => '2000', 'amount' => '200,000.00'],
                    ['date' => '', 'no_of_shares' => '', 'amount' => ''],
                ],
                'agency_name' => 'SEC',
                'secondary_license_type' => 'Secondary Market Registration',
                'secondary_license_date_issued' => '2025-01-05',
                'secondary_license_date_started' => '2025-01-20',
                'secondary_license_sec' => true,
                'secondary_license_bsp' => false,
                'secondary_license_ic' => false,
                'total_annual_compensation_directors' => '1,250,000.00',
                'total_no_officers' => '12',
                'total_rank_file_employees' => '85',
                'total_manpower_complement' => '97',
            ],
            'step_9' => [
                'certifier_name' => 'Juan Dela Cruz',
                'certifier_tin' => '111-222-333-444',
                'certifier_date' => '2026-03-01',
                'done_date' => '2026-03-01',
                'done_day' => '1st',
                'done_month' => 'March',
                'done_year' => '2026',
                'done_place' => 'Guagua, Pampanga',
                'notary_place' => 'Guagua, Pampanga',
                'notary_date' => '2026-03-01',
                'competent_evidence' => 'Passport P1234567A',
                'issued_at' => 'DFA Pampanga',
                'issued_on' => '2024-06-12',
            ],
        ];
    }

    private function capitalRows(array $rows): array
    {
        return array_map(fn (array $row): array => [
            'type_of_shares' => $row[0] ?? '',
            'no_of_stockholders' => '',
            'number_of_shares' => $row[1] ?? '',
            'public_shares' => '',
            'par_or_stated_value' => $row[2] ?? '',
            'amount' => $row[3] ?? '',
            'ownership_percent' => '',
        ], $rows);
    }

    private function subscribedRows(array $rows): array
    {
        return array_map(fn (array $row): array => [
            'type_of_shares' => $row[1] ?? '',
            'no_of_stockholders' => $row[0] ?? '',
            'number_of_shares' => $row[2] ?? '',
            'public_shares' => $row[3] ?? '',
            'par_or_stated_value' => $row[4] ?? '',
            'amount' => $row[5] ?? '',
            'ownership_percent' => $row[6] ?? '',
        ], $rows);
    }

    private function paidUpRows(array $rows): array
    {
        return array_map(fn (array $row): array => [
            'type_of_shares' => $row[1] ?? '',
            'no_of_stockholders' => $row[0] ?? '',
            'number_of_shares' => $row[2] ?? '',
            'public_shares' => '',
            'par_or_stated_value' => $row[3] ?? '',
            'amount' => $row[4] ?? '',
            'ownership_percent' => $row[5] ?? '',
        ], $rows);
    }

    private function directorRows(): array
    {
        $rows = [];

        for ($index = 1; $index <= 15; $index++) {
            $rows[] = [
                'name' => "Director {$index}",
                'nationality' => 'Filipino',
                'incorporator' => $index <= 5 ? 'Y' : 'N',
                'board' => 'M',
                'gender' => $index % 2 === 0 ? 'F' : 'M',
                'stockholder' => 'Y',
                'officer' => $index <= 5 ? 'Officer' : '',
                'exec_comm' => $index <= 3 ? 'Y' : 'N',
                'tin' => sprintf('123-456-%03d-%03d', $index, $index),
            ];
        }

        return $rows;
    }

    private function stockholderRows(int $startNumber): array
    {
        $rows = [];

        for ($offset = 0; $offset < 7; $offset++) {
            $number = $startNumber + $offset;

            $rows[] = [
                'no' => $number,
                'name_address' => "Stockholder {$number}, Guagua Pampanga",
                'nationality' => $number % 3 === 0 ? 'American' : 'Filipino',
                'share_type' => $number % 2 === 0 ? 'Preferred' : 'Common',
                'number_of_shares' => (string) (1000 + ($number * 25)),
                'amount_subscribed' => number_format((1000 + ($number * 25)) * 100, 2, '.', ','),
                'percent_ownership' => number_format(2.5 + ($number * 0.2), 2).'%',
                'amount_paid' => number_format((1000 + ($number * 20)) * 100, 2, '.', ','),
                'tin' => sprintf('321-654-%03d-%03d', $number, $number),
            ];
        }

        return $rows;
    }
}
