<?php

return [
    'sections' => [
        'client_information' => [
            'label' => 'Client Information Form',
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                ['name' => 'residence_address', 'label' => 'Residence Address', 'type' => 'text', 'required' => true],
                ['name' => 'mobile_number', 'label' => 'Mobile Number', 'type' => 'text', 'required' => true],
                ['name' => 'tin', 'label' => 'TIN', 'type' => 'text', 'required' => true],
                ['name' => 'birthdate', 'label' => 'Birthdate', 'type' => 'date', 'required' => true],
                ['name' => 'registration_date', 'label' => 'Registration Date', 'type' => 'date', 'required' => false],
                ['name' => 'alternate_contact_person', 'label' => 'Alternate Contact Person', 'type' => 'text', 'required' => false],
                ['name' => 'alternate_contact_phone_number', 'label' => 'Alternate Contact Person / Phone Number', 'type' => 'text', 'required' => false],
                ['name' => 'alternate_contact_email', 'label' => 'Alternate Contact Person / Email', 'type' => 'email', 'required' => false],
                ['name' => 'product_industry_1', 'label' => 'Product / Industry 1', 'type' => 'text', 'required' => false],
                ['name' => 'product_industry_2', 'label' => 'Product / Industry 2', 'type' => 'text', 'required' => false],
            ],
        ],
        'treasurer_details' => [
            'label' => 'Treasurer Details',
            'fields' => [
                ['name' => 'corporate_name', 'label' => 'Corporate Name', 'type' => 'text', 'required' => false],
                ['name' => 'treasurer_first_name', 'label' => 'Treasurer First Name', 'type' => 'text', 'required' => false],
                ['name' => 'treasurer_middle_name', 'label' => 'Treasurer Middle Name', 'type' => 'text', 'required' => false],
                ['name' => 'treasurer_surname', 'label' => 'Treasurer Surname', 'type' => 'text', 'required' => false],
                ['name' => 'treasurer_address', 'label' => 'Treasurer Address', 'type' => 'text', 'required' => false],
                ['name' => 'treasurer_mobile_number', 'label' => 'Treasurer Mobile Number', 'type' => 'text', 'required' => false],
                ['name' => 'treasurer_birthdate', 'label' => 'Treasurer Birthdate', 'type' => 'date', 'required' => false],
                ['name' => 'treasurer_tin', 'label' => 'Treasurer Tax Identification Number', 'type' => 'text', 'required' => false],
                ['name' => 'assistant_treasurer_first_name', 'label' => 'Assistant Treasurer First Name', 'type' => 'text', 'required' => false],
                ['name' => 'assistant_treasurer_middle_name', 'label' => 'Assistant Treasurer Middle Name', 'type' => 'text', 'required' => false],
                ['name' => 'assistant_treasurer_surname', 'label' => 'Assistant Treasurer Surname', 'type' => 'text', 'required' => false],
                ['name' => 'assistant_treasurer_address', 'label' => 'Assistant Treasurer Address', 'type' => 'text', 'required' => false],
                ['name' => 'assistant_treasurer_mobile_number', 'label' => 'Assistant Treasurer Mobile Number', 'type' => 'text', 'required' => false],
                ['name' => 'assistant_treasurer_birthdate', 'label' => 'Assistant Treasurer Birthdate', 'type' => 'date', 'required' => false],
                ['name' => 'assistant_treasurer_tin', 'label' => 'Assistant Treasurer Tax Identification Number', 'type' => 'text', 'required' => false],
            ],
        ],
        'opc_details' => [
            'label' => 'OPC Details',
            'fields' => [
                ['name' => 'proposed_corporation_name_1', 'label' => 'Proposed Corporation Name 1', 'type' => 'text', 'required' => true],
                ['name' => 'proposed_corporation_name_2', 'label' => 'Proposed Corporation Name 2', 'type' => 'text', 'required' => false],
                ['name' => 'proposed_corporation_name_3', 'label' => 'Proposed Corporation Name 3', 'type' => 'text', 'required' => false],
                ['name' => 'incorporator_first_name', 'label' => 'Incorporator First Name', 'type' => 'text', 'required' => true],
                ['name' => 'incorporator_middle_name', 'label' => 'Incorporator Middle Name', 'type' => 'text', 'required' => false],
                ['name' => 'incorporator_surname', 'label' => 'Incorporator Surname', 'type' => 'text', 'required' => true],
                ['name' => 'incorporator_birthdate', 'label' => 'Incorporator Birthdate', 'type' => 'date', 'required' => true],
                ['name' => 'incorporator_complete_address', 'label' => 'Incorporator Complete Address', 'type' => 'text', 'required' => true],
                ['name' => 'incorporator_tin', 'label' => 'Incorporator Tax Identification Number', 'type' => 'text', 'required' => true],
                ['name' => 'incorporator_email_address', 'label' => 'Incorporator E-Mail Address', 'type' => 'email', 'required' => true],
                ['name' => 'incorporator_contact_number', 'label' => 'Incorporator Contact Number', 'type' => 'text', 'required' => true],
                ['name' => 'nominee_person_choice', 'label' => 'Nominee Auto-Fill Person', 'type' => 'select', 'required' => false, 'options' => [
                    ['label' => 'No one (enter manually)', 'value' => 'no_one'],
                    ['label' => 'Vince Anthony Paule Feir', 'value' => 'person_1'],
                    ['label' => 'Tristan Harvey Mallari Braceros', 'value' => 'person_2'],
                ]],
                ['name' => 'nominee_role', 'label' => 'Nominee Role', 'type' => 'select', 'required' => true, 'options' => [
                    ['label' => 'President', 'value' => 'president'],
                    ['label' => 'Treasurer', 'value' => 'treasurer'],
                    ['label' => 'Secretary', 'value' => 'secretary'],
                    ['label' => 'Treasurer and Secretary', 'value' => 'treasurer_and_secretary'],
                ]],
                ['name' => 'nominee_first_name', 'label' => 'Nominee First Name', 'type' => 'text', 'required' => true],
                ['name' => 'nominee_middle_name', 'label' => 'Nominee Middle Name', 'type' => 'text', 'required' => false],
                ['name' => 'nominee_surname', 'label' => 'Nominee Surname', 'type' => 'text', 'required' => true],
                ['name' => 'nominee_birthdate', 'label' => 'Nominee Birthdate', 'type' => 'date', 'required' => true],
                ['name' => 'nominee_complete_address', 'label' => 'Nominee Complete Address', 'type' => 'text', 'required' => true],
                ['name' => 'nominee_tin', 'label' => 'Nominee Tax Identification Number', 'type' => 'text', 'required' => true],
                ['name' => 'nominee_email_address', 'label' => 'Nominee E-Mail Address', 'type' => 'email', 'required' => true],
                ['name' => 'nominee_contact_number', 'label' => 'Nominee Contact Number', 'type' => 'text', 'required' => true],
                ['name' => 'alternate_nominee_person_choice', 'label' => 'Alternate Nominee Auto-Fill Person', 'type' => 'select', 'required' => false, 'options' => [
                    ['label' => 'No one (enter manually)', 'value' => 'no_one'],
                    ['label' => 'Vince Anthony Paule Feir', 'value' => 'person_1'],
                    ['label' => 'Tristan Harvey Mallari Braceros', 'value' => 'person_2'],
                ]],
                ['name' => 'alternate_nominee_role', 'label' => 'Alternate Nominee Role', 'type' => 'select', 'required' => true, 'options' => [
                    ['label' => 'President', 'value' => 'president'],
                    ['label' => 'Treasurer', 'value' => 'treasurer'],
                    ['label' => 'Secretary', 'value' => 'secretary'],
                    ['label' => 'Treasurer and Secretary', 'value' => 'treasurer_and_secretary'],
                ]],
                ['name' => 'alternate_nominee_first_name', 'label' => 'Alternate Nominee First Name', 'type' => 'text', 'required' => true],
                ['name' => 'alternate_nominee_middle_name', 'label' => 'Alternate Nominee Middle Name', 'type' => 'text', 'required' => false],
                ['name' => 'alternate_nominee_surname', 'label' => 'Alternate Nominee Surname', 'type' => 'text', 'required' => true],
                ['name' => 'alternate_nominee_birthdate', 'label' => 'Alternate Nominee Birthdate', 'type' => 'date', 'required' => true],
                ['name' => 'alternate_nominee_complete_address', 'label' => 'Alternate Nominee Complete Address', 'type' => 'text', 'required' => true],
                ['name' => 'alternate_nominee_tin', 'label' => 'Alternate Nominee Tax Identification Number', 'type' => 'text', 'required' => true],
                ['name' => 'alternate_nominee_email_address', 'label' => 'Alternate Nominee E-Mail Address', 'type' => 'email', 'required' => true],
                ['name' => 'alternate_nominee_contact_number', 'label' => 'Alternate Nominee Contact Number', 'type' => 'text', 'required' => true],
            ],
        ],
        'proprietorship' => [
            'label' => 'Proprietorship Details',
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                ['name' => 'address', 'label' => 'Address', 'type' => 'text', 'required' => true],
                ['name' => 'proposed_business_name_1', 'label' => 'Proposed Business Name 1', 'type' => 'text', 'required' => true],
                ['name' => 'proposed_business_name_2', 'label' => 'Proposed Business Name 2', 'type' => 'text', 'required' => false],
                ['name' => 'mobile_number', 'label' => 'Mobile Number', 'type' => 'text', 'required' => true],
                ['name' => 'tin', 'label' => 'TIN', 'type' => 'text', 'required' => true],
                ['name' => 'birthdate', 'label' => 'Birthdate', 'type' => 'date', 'required' => true],
                ['name' => 'mother_maiden_name', 'label' => 'Mother\'s Maiden Name', 'type' => 'text', 'required' => false],
                ['name' => 'mother_birthdate', 'label' => 'Mother\'s Birthdate', 'type' => 'date', 'required' => false],
                ['name' => 'father_name', 'label' => 'Father\'s Name', 'type' => 'text', 'required' => false],
                ['name' => 'father_birthdate', 'label' => 'Father\'s Birthdate', 'type' => 'date', 'required' => false],
                ['name' => 'spouse_name', 'label' => 'Spouse\'s Name', 'type' => 'text', 'required' => false],
                ['name' => 'spouse_birthdate', 'label' => 'Spouse\'s Birthdate', 'type' => 'date', 'required' => false],
                ['name' => 'registration_date', 'label' => 'Registration Date', 'type' => 'date', 'required' => false],
                ['name' => 'alternate_contact_person', 'label' => 'Alternate Contact Person', 'type' => 'text', 'required' => false],
                ['name' => 'alternate_contact_phone_number', 'label' => 'Alternate Contact Person / Phone Number', 'type' => 'text', 'required' => false],
                ['name' => 'alternate_contact_email', 'label' => 'Alternate Contact Person / Email', 'type' => 'email', 'required' => false],
                ['name' => 'product_industry_1', 'label' => 'Product / Industry 1', 'type' => 'text', 'required' => false],
                ['name' => 'product_industry_2', 'label' => 'Product / Industry 2', 'type' => 'text', 'required' => false],
            ],
        ],
        'regular_corporation' => [
            'label' => 'Regular Corporation Details',
            'fields' => array_merge(
                [
                    ['name' => 'proposed_corporation_name_1', 'label' => 'Proposed Corporation Name 1', 'type' => 'text', 'required' => true],
                    ['name' => 'proposed_corporation_name_2', 'label' => 'Proposed Corporation Name 2', 'type' => 'text', 'required' => false],
                    ['name' => 'proposed_corporation_name_3', 'label' => 'Proposed Corporation Name 3', 'type' => 'text', 'required' => false],
                ],
                (static function (): array {
                    $fields = [];

                    for ($index = 1; $index <= 15; $index++) {
                        $isRequired = $index <= 2;

                        $fields[] = ['name' => "incorporator_{$index}_first_name", 'label' => "Incorporator {$index} First Name", 'type' => 'text', 'required' => $isRequired];
                        $fields[] = ['name' => "incorporator_{$index}_middle_name", 'label' => "Incorporator {$index} Middle Name", 'type' => 'text', 'required' => false];
                        $fields[] = ['name' => "incorporator_{$index}_surname", 'label' => "Incorporator {$index} Surname", 'type' => 'text', 'required' => $isRequired];
                        $fields[] = ['name' => "incorporator_{$index}_birthdate", 'label' => "Incorporator {$index} Birthdate", 'type' => 'date', 'required' => $isRequired];
                        $fields[] = ['name' => "incorporator_{$index}_complete_address", 'label' => "Incorporator {$index} Complete Address", 'type' => 'text', 'required' => $isRequired];
                        $fields[] = ['name' => "incorporator_{$index}_tin", 'label' => "Incorporator {$index} Tax Identification Number", 'type' => 'text', 'required' => $isRequired];
                        $fields[] = ['name' => "incorporator_{$index}_email_address", 'label' => "Incorporator {$index} E-Mail Address", 'type' => 'email', 'required' => $isRequired];
                        $fields[] = ['name' => "incorporator_{$index}_contact_number", 'label' => "Incorporator {$index} Contact Number", 'type' => 'text', 'required' => $isRequired];
                    }

                    return $fields;
                })(),
            ),
        ],
    ],
    'company_type_sections' => [
        'opc' => 'opc_details',
        'sole_prop' => 'proprietorship',
        'corp' => 'regular_corporation',
    ],
];
