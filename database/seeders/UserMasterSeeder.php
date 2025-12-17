<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        DB::table('usermaster')->insert([
            [
                'id'                       => 1,
                'code'                     => 'USR_1',
                'firstname'                => 'Admin',
                'lastname'                 => 'User',
                'middlename'               => null,
                'username'                 => 'admin',
                'password'                 => Hash::make('123456'),
                'role'                     => 'R_1',
                'userEmail'                => 'testing.neosaoservices@gmail.com',
                'profilePhoto'             => 'user.png',
                'firebase_id'              => null,
                'mobile'                   => '5195740074',
                'isActive'                 => 1,
                'isDelete'                 => 0,
                'addID'                    => null,
                'addIP'                    => null,
                'deleteID'                 => null,
                'deleteIP'                 => null,
                'deleteDate'               => null,
                'addDate'                  => null,
                'editIP'                   => '106.76.78.105',
                'editID'                   => 'USR_1',
                'editDate'                 => '2025-08-31 20:43:15',
                'resetToken'               => null,
                'updated_at'               => $now,
                'created_at'               => $now,
                'storeLocationCode'        => 'STR_6',
                'defaultDeliveryExecutive' => 0,
            ]
        ]);
    }
}
