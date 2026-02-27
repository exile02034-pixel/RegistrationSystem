<?php

namespace App\Services;

use InvalidArgumentException;

class RegistrationTemplateService
{
    public const TYPE_CORP = 'corp';
    public const TYPE_SOLE_PROP = 'sole_prop';
    public const TYPE_OPC = 'opc';

    private const TEMPLATE_MAP = [
        self::TYPE_CORP => [
            'registration_form' => ['path' => 'app/template/Client Information Form.docx', 'name' => 'Client Information Form.docx'],
            'treasurer_details' => ['path' => 'app/template/TREASURER DETAILS.docx', 'name' => 'TREASURER DETAILS.docx'],
            'company_requirements' => ['path' => 'app/template/Registration Details Regular Corp.docx', 'name' => 'Registration Details Regular Corp.docx'],
        ],
        self::TYPE_SOLE_PROP => [
            'registration_form' => ['path' => 'app/template/Client Information Form.docx', 'name' => 'Client Information Form.docx'],
            'treasurer_details' => ['path' => 'app/template/TREASURER DETAILS.docx', 'name' => 'TREASURER DETAILS.docx'],
            'company_requirements' => ['path' => 'app/template/Sole Prop Registaration Form.docx', 'name' => 'Sole Prop Registaration Form.docx'],
        ],
        self::TYPE_OPC => [
            'registration_form' => ['path' => 'app/template/Client Information Form.docx', 'name' => 'Client Information Form.docx'],
            'treasurer_details' => ['path' => 'app/template/TREASURER DETAILS.docx', 'name' => 'TREASURER DETAILS.docx'],
            'company_requirements' => ['path' => 'app/template/Registration Details Format OPC.docx', 'name' => 'Registration Details Format OPC.docx'],
        ],
    ];

    private const LABEL_MAP = [
        self::TYPE_CORP => 'Corporation (Corp)',
        self::TYPE_SOLE_PROP => 'Sole Proprietorship (Sole Prop)',
        self::TYPE_OPC => 'One Person Corporation (OPC)',
    ];

    public function availableCompanyTypes(): array
    {
        return [
            ['value' => self::TYPE_CORP, 'label' => self::LABEL_MAP[self::TYPE_CORP]],
            ['value' => self::TYPE_SOLE_PROP, 'label' => self::LABEL_MAP[self::TYPE_SOLE_PROP]],
            ['value' => self::TYPE_OPC, 'label' => self::LABEL_MAP[self::TYPE_OPC]],
        ];
    }

    public function labelFor(string $companyType): string
    {
        if (! isset(self::LABEL_MAP[$companyType])) {
            throw new InvalidArgumentException('Unsupported company type: '.$companyType);
        }

        return self::LABEL_MAP[$companyType];
    }

    public function templatesFor(string $companyType): array
    {
        if (! isset(self::TEMPLATE_MAP[$companyType])) {
            throw new InvalidArgumentException('Unsupported company type: '.$companyType);
        }

        return self::TEMPLATE_MAP[$companyType];
    }

    public function templateByKey(string $companyType, string $key): array
    {
        $templates = $this->templatesFor($companyType);

        if (! isset($templates[$key])) {
            throw new InvalidArgumentException('Template not found for key: '.$key);
        }

        return $templates[$key];
    }
}
