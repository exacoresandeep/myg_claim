<?php
namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return User::with(['branchDetails', 'baselocationDetails'])->get();
    }

    public function map($user): array
    {
        return [
            $user->emp_id,
            $user->emp_name,
            $user->emp_phonenumber,
            $user->email,
            optional($user->gradeDetails)->GradeName ?? '',
            optional($user->branchDetails)->BranchName ?? '',
            $user->emp_designation,
            $user->reporting_person,
            $user->reporting_person_empid,
            $user->emp_role,
            $user->Status == 1 ? 'Active' : 'Inactive',
            optional($user->baselocationDetails)->BranchName ?? '', // Baselocation
        ];
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Employee Name',
            'Contact No',
            'Email',
            'Grade',
            'Branch',
            'Designation',
            'Reporting Person',
            'Reporting Person ID',
            'Role',
            'Status',
            'Base Location'
        ];
    }
}
