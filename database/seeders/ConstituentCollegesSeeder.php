<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\College;

class ConstituentCollegesSeeder extends Seeder
{
    public function run(): void
    {
        $colleges = [
            [
                'code' => 'VMKVASC',
                'name' => "Vinayaka Mission's Kirupananda Variyar Arts and Science College, Salem",
                'location' => 'Salem, Tamil Nadu',
            ],
            [
                'code' => 'VMKVEC',
                'name' => "Vinayaka Mission's Kirupananda Variyar Engineering College, Salem",
                'location' => 'Salem, Tamil Nadu',
            ],
            [
                'code' => 'VMSDC',
                'name' => "Vinayaka Mission's Sankarachariyar Dental College, Salem",
                'location' => 'Salem, Tamil Nadu',
            ],
            [
                'code' => 'VMACON',
                'name' => "Vinayaka Mission's Annapoorana College of Nursing, Salem",
                'location' => 'Salem, Tamil Nadu',
            ],
            [
                'code' => 'VMKVMC',
                'name' => "Vinayaka Mission's Kirupananda Variyar Medical College & Hospitals, Salem",
                'location' => 'Salem, Tamil Nadu',
            ],
            [
                'code' => 'VMCPT-SLM',
                'name' => "Vinayaka Mission's College of Physiotherapy, Salem",
                'location' => 'Salem, Tamil Nadu',
            ],
            [
                'code' => 'SAHS-SLM',
                'name' => "School of Allied Health Sciences, VMRF (DU), Salem",
                'location' => 'Salem, Tamil Nadu',
            ],
            [
                'code' => 'AVIT',
                'name' => "Aarupadai Veedu Institute of Technology (AVIT), Paiyanoor, Chennai",
                'location' => 'Paiyanoor, Chennai',
            ],
            [
                'code' => 'VMCPE',
                'name' => "Vinayaka Mission's College of Physical Education, Salem",
                'location' => 'Salem, Tamil Nadu',
            ],
            [
                'code' => 'VMCON-PDY',
                'name' => "Vinayaka Mission's College of Nursing, Puducherry",
                'location' => 'Puducherry',
            ],
            [
                'code' => 'VMMCH-KKL',
                'name' => "Vinayaka Mission's Medical College & Hospital, Karaikal",
                'location' => 'Karaikal',
            ],
            [
                'code' => 'VMCON-KKL',
                'name' => "Vinayaka Mission's College of Nursing, Karaikal",
                'location' => 'Karaikal',
            ],
            [
                'code' => 'SAHS-PDY',
                'name' => "School of Allied Health Sciences, Puducherry",
                'location' => 'Puducherry',
            ],
            [
                'code' => 'SAHS-VMKVMC',
                'name' => "School of Allied Health Sciences, VMKVMC & Hospital, Salem",
                'location' => 'Salem, Tamil Nadu',
            ],
            [
                'code' => 'SOPT-PDY',
                'name' => "School of Physiotherapy, Puducherry",
                'location' => 'Puducherry',
            ],
            [
                'code' => 'AVMC',
                'name' => "Aarupadai Veedu Medical College & Hospital, Puducherry",
                'location' => 'Puducherry',
            ],
            [
                'code' => 'VMCP',
                'name' => "Vinayaka Mission's College of Pharmacy, Salem",
                'location' => 'Salem, Tamil Nadu',
            ],
            [
                'code' => 'SAHS-KKL',
                'name' => "School of Allied Health Sciences, Karaikal",
                'location' => 'Karaikal',
            ],
            [
                'code' => 'VMHMC',
                'name' => "Vinayaka Mission's Homoeopathic Medical College & Hospital, Salem",
                'location' => 'Salem, Tamil Nadu',
            ],
            [
                'code' => 'SRBS-PDY',
                'name' => "School of Rehabilitation and Behavioral Sciences, Puducherry",
                'location' => 'Puducherry',
            ],
            [
                'code' => 'SCPT-KKL',
                'name' => "Shanmuga College of Physiotherapy, Karaikal",
                'location' => 'Karaikal',
            ],
            [
                'code' => 'SAHS-CHN',
                'name' => "School of Allied Health Sciences, Paiyanoor, Chennai",
                'location' => 'Paiyanoor, Chennai',
            ],
            [
                'code' => 'SAS-CHN',
                'name' => "School of Arts and Science, Paiyanoor, Chennai",
                'location' => 'Paiyanoor, Chennai',
            ],
            [
                'code' => 'VMLS',
                'name' => "Vinayaka Mission's Law School (VMLS), Paiyanoor, Chennai",
                'location' => 'Paiyanoor, Chennai',
            ],
            [
                'code' => 'VSEP',
                'name' => "Vinayaka Mission's School of Economics and Public Policy (VSEP), Paiyanoor, Chennai",
                'location' => 'Paiyanoor, Chennai',
            ],
            [
                'code' => 'DOM-SLM',
                'name' => "Department of Management, VMKV Engineering College Campus, Salem",
                'location' => 'Salem, Tamil Nadu',
            ],
            [
                'code' => 'DOM-CHN',
                'name' => "Department of Management, AVIT Campus, Paiyanoor, Chengalpattu",
                'location' => 'Paiyanoor, Chengalpattu',
            ],
            [
                'code' => 'DMB-PDY',
                'name' => "Department of Medical Biotechnology, AVMC Campus, Puducherry",
                'location' => 'Puducherry',
            ]
        ];

        foreach ($colleges as $data) {
            College::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'location' => $data['location'],
                    'is_active' => true,
                ]
            );
        }
    }
}
