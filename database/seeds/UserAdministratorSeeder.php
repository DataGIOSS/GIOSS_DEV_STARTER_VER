<?php

use Illuminate\Database\Seeder;
use App\User;

class UserAdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
        	'name' => 'Adminstrador',
        	'email' =>'admin@admin',
        	'password' => Hash::make('administrador_1'),
        	'roleid' => 1,
        	'lastname' => 'gioss',
            'status' => 1,
        ]);

        User::create([
            'name' => 'Pepito',
            'email' =>'pepito@perez',
            'password' => Hash::make('invitado_1'),
            'roleid' => 2,
            'lastname' => 'Perez',
            'status' => 0,
        ]);

        return true;
    }
}
