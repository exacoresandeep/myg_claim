<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use DB;
Use Hash;

class Usersseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
        	'emp_id'=>"MYGK01",
            'emp_name' => 'Super admin',
            'emp_email' => 'admin@exacoreitsolutions.com',
            'password' => Hash::make('123456'),
            'emp_role'=>'Super admin',
            'reporting_person'=>"",
            'emp_grade'=>"Grade 0",
            'emp_designation'=>"Super admin",
            'emp_baselocation'=>"kozhikode(MYGK01)",
            'emp_phonenumber'=>"9632587412",
            'emp_department'=>"Super admin",
            'emp_branch'=>"kozhikode(MYGK01)",
            'reporting_person_empid'=>"0",
        ]);

        User::create([
        	'emp_id'=>"MYGK02",
            'emp_name' => 'Finance',
            'emp_email' => 'finance@exacoreitsolutions.com',
            'password' => Hash::make('123456'),
            'emp_role'=>'Finance',
            'reporting_person'=>"",
            'emp_grade'=>"Grade 1",
            'emp_designation'=>"Finance",
            'emp_baselocation'=>"kozhikode(MYGK02)",
            'emp_phonenumber'=>"8754215487",
            'emp_department'=>"Finance",
            'emp_branch'=>"kozhikode(MYGK02)",
            'reporting_person_empid'=>"1",
        ]);

        User::create([
        	'emp_id'=>"MYGK03",
            'emp_name' => 'Manager',
            'emp_email' => 'manager@exacoreitsolutions.com',
            'password' => Hash::make('123456'),
            'emp_role'=>'Manager',
            'reporting_person'=>"",
            'emp_grade'=>"Grade 2",
            'emp_designation'=>"Manager",
            'emp_baselocation'=>"kozhikode(MYGK03)",
            'emp_phonenumber'=>"9632587412",
            'emp_department'=>"Manager",
            'emp_branch'=>"kozhikode(MYGK03)",
            'reporting_person_empid'=>"2",
        ]);
    }
}
