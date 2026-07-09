<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create role if not exists
        DB::table('roles')->insertOrIgnore([
            'RoleID' => 1,
            'RoleName' => 'Student',
        ]);
        DB::table('roles')->insertOrIgnore([
            'RoleID' => 2,
            'RoleName' => 'Lecturer',
        ]);
        DB::table('roles')->insertOrIgnore([
            'RoleID' => 3,
            'RoleName' => 'Admin',
        ]);

        // Create super admin user
        $userID = DB::table('users')->insertGetId([
            'FullName'       => 'Super Admin',
            'Email'          => 'admin@forum.com',
            'Password'       => Hash::make('Admin@1234'),
            'DateJoined'     => now(),
            'LastActiveDate' => now(),
            'RoleID'         => 3,
        ], 'UserID');

        // Insert into admins table
        DB::table('admins')->insert([
            'AdminID'      => 'ADM001',
            'UserID'       => $userID,
            'AssignedDate' => now(),
            'Scope'        => 'Forum Wide',
        ]);
    }
}