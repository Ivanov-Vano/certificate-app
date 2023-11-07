<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // reset cached roles and permissions (сбросить кешированные роли и разрешения)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        //Misc (пустышка)
        $miscPermission = Permission::create(['name' => 'N/A']);

        //Certificate model
        $certificatePermission1 = Permission::create(['name' => 'просмотр всех: сертификат']);
        $certificatePermission2 = Permission::create(['name' => 'просмотр: сертификат']);
        $certificatePermission3 = Permission::create(['name' => 'создание: сертификат']);
        $certificatePermission4 = Permission::create(['name' => 'изменение: сертификат']);
        $certificatePermission5 = Permission::create(['name' => 'удаление: сертификат']);
        $certificatePermission6 = Permission::create(['name' => 'восстановление: сертификат']);
        $certificatePermission7 = Permission::create(['name' => 'безвозвратное удаление: сертификат']);

        //Chamber model
        $chamberPermission1 = Permission::create(['name' => 'просмотр всех: палата']);
        $chamberPermission2 = Permission::create(['name' => 'просмотр: палата']);
        $chamberPermission3 = Permission::create(['name' => 'создание: палата']);
        $chamberPermission4 = Permission::create(['name' => 'изменение: палата']);
        $chamberPermission5 = Permission::create(['name' => 'удаление: палата']);
        $chamberPermission6 = Permission::create(['name' => 'восстановление: палата']);
        $chamberPermission7 = Permission::create(['name' => 'безвозвратное удаление: палата']);

        //Company model
        $companyPermission1 = Permission::create(['name' => 'просмотр всех: компания']);
        $companyPermission2 = Permission::create(['name' => 'просмотр: компания']);
        $companyPermission3 = Permission::create(['name' => 'создание: компания']);
        $companyPermission4 = Permission::create(['name' => 'изменение: компания']);
        $companyPermission5 = Permission::create(['name' => 'удаление: компания']);
        $companyPermission6 = Permission::create(['name' => 'восстановление: компания']);
        $companyPermission7 = Permission::create(['name' => 'безвозвратное удаление: компания']);

        //Expert model
        $expertPermission1 = Permission::create(['name' => 'просмотр всех: эксперт']);
        $expertPermission2 = Permission::create(['name' => 'просмотр: эксперт']);
        $expertPermission3 = Permission::create(['name' => 'создание: эксперт']);
        $expertPermission4 = Permission::create(['name' => 'изменение: эксперт']);
        $expertPermission5 = Permission::create(['name' => 'удаление: эксперт']);
        $expertPermission6 = Permission::create(['name' => 'восстановление: эксперт']);
        $expertPermission7 = Permission::create(['name' => 'безвозвратное удаление: эксперт']);

        //Organization model
        $organizationPermission1 = Permission::create(['name' => 'просмотр всех: организация']);
        $organizationPermission2 = Permission::create(['name' => 'просмотр: организация']);
        $organizationPermission3 = Permission::create(['name' => 'создание: организация']);
        $organizationPermission4 = Permission::create(['name' => 'изменение: организация']);
        $organizationPermission5 = Permission::create(['name' => 'удаление: организация']);
        $organizationPermission6 = Permission::create(['name' => 'восстановление: организация']);
        $organizationPermission7 = Permission::create(['name' => 'безвозвратное удаление: организация']);

        //Type model
        $typePermission1 = Permission::create(['name' => 'просмотр всех: тип сертификата']);
        $typePermission2 = Permission::create(['name' => 'просмотр: тип сертификата']);
        $typePermission3 = Permission::create(['name' => 'создание: тип сертификата']);
        $typePermission4 = Permission::create(['name' => 'изменение: тип сертификата']);
        $typePermission5 = Permission::create(['name' => 'удаление: тип сертификата']);
        $typePermission6 = Permission::create(['name' => 'восстановление: тип сертификата']);
        $typePermission7 = Permission::create(['name' => 'безвозвратное удаление: тип сертификата']);


        //CREATE ROLES (создание ролей)
        $userRole = Role::create(['name' => 'Эксперт'])->syncPermissions([
            $certificatePermission1,
            $certificatePermission2,
            $certificatePermission3,
            $certificatePermission4,
            $certificatePermission5,
            $organizationPermission1,
            $organizationPermission2,
            $organizationPermission3,
            $organizationPermission4,
            $organizationPermission5,
            $companyPermission1,
            $companyPermission2,
            $companyPermission3,
            $companyPermission4,
            $companyPermission5,
            $typePermission1,
            $typePermission2,
            $typePermission3,
            $typePermission4,
            $typePermission5,
            $chamberPermission1,
            $chamberPermission2,
            $chamberPermission3,
            $chamberPermission4,
            $chamberPermission5,
            $chamberPermission6,
            $chamberPermission7,
        ]);
        $chiefRole = Role::create(['name' => 'Руководитель'])->syncPermissions([
            $certificatePermission1,
            $certificatePermission2,
            $certificatePermission3,
            $certificatePermission4,
            $certificatePermission5,
            $certificatePermission6,
            $certificatePermission7,
            $chamberPermission1,
            $chamberPermission2,
            $chamberPermission3,
            $chamberPermission4,
            $chamberPermission5,
            $chamberPermission6,
            $chamberPermission7,
            $companyPermission1,
            $companyPermission2,
            $companyPermission3,
            $companyPermission4,
            $companyPermission5,
            $companyPermission6,
            $companyPermission7,
            $expertPermission1,
            $expertPermission2,
            $expertPermission3,
            $expertPermission4,
            $expertPermission5,
            $expertPermission6,
            $expertPermission7,
            $typePermission1,
            $typePermission2,
            $typePermission3,
            $typePermission4,
            $typePermission5,
            $typePermission6,
            $typePermission7,
            $organizationPermission1,
            $organizationPermission2,
            $organizationPermission3,
            $organizationPermission4,
            $organizationPermission5,
            $organizationPermission6,
            $organizationPermission7,
        ]);
        $adminRole = Role::create(['name' => 'Администратор'])->syncPermissions([
            $certificatePermission1,
            $certificatePermission2,
            $certificatePermission3,
            $certificatePermission4,
            $certificatePermission5,
            $certificatePermission6,
            $certificatePermission7,
            $chamberPermission1,
            $chamberPermission2,
            $chamberPermission3,
            $chamberPermission4,
            $chamberPermission5,
            $chamberPermission6,
            $chamberPermission7,
            $companyPermission1,
            $companyPermission2,
            $companyPermission3,
            $companyPermission4,
            $companyPermission5,
            $companyPermission6,
            $companyPermission7,
            $expertPermission1,
            $expertPermission2,
            $expertPermission3,
            $expertPermission4,
            $expertPermission5,
            $expertPermission6,
            $expertPermission7,
            $typePermission1,
            $typePermission2,
            $typePermission3,
            $typePermission4,
            $typePermission5,
            $typePermission6,
            $typePermission7,
            $organizationPermission1,
            $organizationPermission2,
            $organizationPermission3,
            $organizationPermission4,
            $organizationPermission5,
            $organizationPermission6,
            $organizationPermission7,
        ]);
        $courierRole = Role::create(['name' => 'Курьер'])->syncPermissions([
            $certificatePermission1,
            $certificatePermission2,
            $certificatePermission4,
            $chamberPermission1,
            $chamberPermission2,
            $chamberPermission4,
            $companyPermission1,
            $companyPermission2,
            $companyPermission4,
            $expertPermission1,
            $expertPermission2,
            $expertPermission4,
            $typePermission1,
            $typePermission2,
            $typePermission4,
            $organizationPermission1,
            $organizationPermission2,
            $organizationPermission4,
        ]);
        User::create([
            'email' => 'chief@mail.ru',
            'email_verified_at' => now(),
            'password' => '$2y$10$R5vBPe6dfxDQevMtpH6pmetk3B0oyACoFU7RvLkz8EhUE4u99.r.O', // password 12345678
            'remember_token' => Str::random(10),
            'is_admin' => '1',
            'username' => 'руководитель',
            'name' => 'руководитель',
        ])->assignRole($chiefRole);
        User::create([
            'email' => 'admin@mail.ru',
            'email_verified_at' => now(),
            'password' => '$2y$10$R5vBPe6dfxDQevMtpH6pmetk3B0oyACoFU7RvLkz8EhUE4u99.r.O', // password 12345678
            'remember_token' => Str::random(10),
            'is_admin' => '1',
            'username' => 'админ',
            'name' => 'админ',
        ])->assignRole($adminRole);
        for ($i=1; $i <5; $i++) {
            User::create([
                'email' => 'user'.$i.'@mail.ru',
                'email_verified_at' => now(),
                'password' => '$2y$10$R5vBPe6dfxDQevMtpH6pmetk3B0oyACoFU7RvLkz8EhUE4u99.r.O', // password 12345678
                'remember_token' => Str::random(10),
                'is_admin' => '0',
                'username' => 'пользователь'.$i,
                'name' => 'пользователь'.$i,
            ])->assignRole($userRole);
        }
        for ($i=1; $i <3; $i++) {
            User::create([
                'email' => 'courier'.$i.'@mail.ru',
                'email_verified_at' => now(),
                'password' => '$2y$10$R5vBPe6dfxDQevMtpH6pmetk3B0oyACoFU7RvLkz8EhUE4u99.r.O', // password 12345678
                'remember_token' => Str::random(10),
                'is_admin' => '0',
                'username' => 'курьер'.$i,
                'name' => 'курьер'.$i,
            ])->assignRole($userRole);
        }

    }
}
