<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RolesMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now=Carbon::now();
        DB::table('rolesmaster')->insert([
            [
                'id'         => 1,
                'code'       => 'R_1',
                'role'       => 'Admin',
                'isActive'   => 1,
                'isDelete'   => 0,
                'addID'      => '',
                'addIP'      => '',
                'deleteID'   => null,
                'deleteIP'   => null,
                'deleteDate' => null,
                'addDate'    => null,
                'editIP'     => null,
                'editID'     => null,
                'editDate'   => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 3,
                'code'       => 'R_2',
                'role'       => 'Driver',
                'isActive'   => 1,
                'isDelete'   => 0,
                'addID'      => '',
                'addIP'      => '',
                'deleteID'   => null,
                'deleteIP'   => null,
                'deleteDate' => null,
                'addDate'    => null,
                'editIP'     => null,
                'editID'     => null,
                'editDate'   => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 2,
                'code'       => 'R_3',
                'role'       => 'Cashier',
                'isActive'   => 1,
                'isDelete'   => 0,
                'addID'      => '',
                'addIP'      => '',
                'deleteID'   => null,
                'deleteIP'   => null,
                'deleteDate' => null,
                'addDate'    => null,
                'editIP'     => null,
                'editID'     => null,
                'editDate'   => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id'         => 9,
                'code'       => 'R_4',
                'role'       => 'Tele Cashier',
                'isActive'   => 1,
                'isDelete'   => 0,
                'addID'      => null,
                'addIP'      => null,
                'deleteID'   => null,
                'deleteIP'   => null,
                'deleteDate' => null,
                'addDate'    => null,
                'editIP'     => null,
                'editID'     => null,
                'editDate'   => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
