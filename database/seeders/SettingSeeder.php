<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    // все столбцы
    const ALL_PARAMETRS_VISIBILITY = [
        'certificate_number',
        'certificate_date',
        'certificate_type_short_name',
        'certificate_sign_name',
        'certificate_chamber_short_name',
        'certificate_payer_short_name',
        'certificate_sender_short_name',
        'certificate_company_short_name',
        'certificate_company_country_short_name',
        'certificate_scan_path',
        'certificate_expert_full_name',
        'certificate_invoice_issued',
        'certificate_paid',
        'certificate_delivery_id',
        'certificate_deleted_at',
        'certificate_created_at',
        'certificate_updated_at',
        'delivery_number',
        'delivery_accepted_at',
        'delivery_organization_short_name',
        'delivery_deliveryman_full_name',
        'delivery_cost',
        'delivery_certificates_count',
        'delivery_is_pickup',
        'delivery_delivered_at',
        'delivery_deleted_at',
        'delivery_created_at',
        'delivery_updated_at',
    ];
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // столбцы, которые необходимо исключить для просмотра
        $courierColumnsToExclude = [
            'delivery_deliveryman_full_name',
            'delivery_deleted_at',
            'delivery_created_at',
            'delivery_updated_at',
        ];
        $expertColumnsToExclude = [
            'certificate_expert_full_name',
            'certificate_deleted_at',
            'certificate_created_at',
            'certificate_updated_at',
            'delivery_deleted_at',
            'delivery_created_at',
            'delivery_updated_at',
        ];
        $chamberColumnsToExclude = [
            'certificate_chamber_short_name',
            'certificate_deleted_at',
            'certificate_created_at',
            'certificate_updated_at',
            'delivery_deleted_at',
            'delivery_created_at',
            'delivery_updated_at',
        ];
        $chiefColumnsToExclude = [
            'delivery_deleted_at',
            'certificate_deleted_at',
        ];

        //Setting model
        Setting::create(['role_name' => 'Эксперт', 'columns_visibility' =>array_values(array_diff(self::ALL_PARAMETRS_VISIBILITY, $expertColumnsToExclude))]);
        Setting::create(['role_name' => 'Представитель палаты', 'columns_visibility' =>array_values(array_diff(self::ALL_PARAMETRS_VISIBILITY, $chamberColumnsToExclude))]);
        Setting::create(['role_name' => 'Руководитель', 'columns_visibility' =>array_values(array_diff(self::ALL_PARAMETRS_VISIBILITY, $chiefColumnsToExclude))]);
        Setting::create(['role_name' => 'Администратор', 'columns_visibility' => self::ALL_PARAMETRS_VISIBILITY]);
        Setting::create(['role_name' => 'Курьер', 'columns_visibility' =>array_values(array_diff(self::ALL_PARAMETRS_VISIBILITY, $courierColumnsToExclude))]);
    }
}
