<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = new User();
        $admin->name = 'Enrich';
        $admin->email = 'enrich@enrich.com';
        $admin->password = '123456--en';
        $admin->normalized_name = normalize('Enrich');
        $admin->role = 'ADMIN';
        $admin->status = 'active';
        $admin->save();
    }
}
