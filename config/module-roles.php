<?php

return [
    'modules' => [
        'ispo' => [
            'label' => 'System ISPO',
            'roles' => [
                'ISPO Admin',
                'ISPO Auditor',
            ],
        ],
        'sas' => [
            'label' => 'Service Agreement System',
            'roles' => [
                'Admin',
                'Asisten Afdeling',
                'Approver',
                'Manager',
                'KTU',
                'GM',
            ],
        ],
        'qc' => [
            'label' => 'QC Complaint System',
            'roles' => [
                'QC Admin',
                'QC Officer',
                'QC Approver',
            ],
        ],
        'pr' => [
            'label' => 'Purchase Request System',
            'roles' => [
                'Admin',
                'Approver',
                'Purchasing',
                'Warehouse',
                'Finance',
            ],
        ],
    ],
];
