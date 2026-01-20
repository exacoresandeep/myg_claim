<?php

namespace App\Exports;

use App\Models\Tripclaim;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Tripclaim::with([
            'triptypedetails', 
            'userdata', 
            'visitbranchdetails', 
            'gradedetails', 
            'departmentdetails'
        ]);

        if (!empty($this->filters['FromDate'])) {
            $query->whereDate('created_at', '>=', $this->filters['FromDate']);
        }

        if (!empty($this->filters['ToDate'])) {
            $query->whereDate('created_at', '<=', $this->filters['ToDate']);
        }

        if (!empty($this->filters['Status'])) {
            $query->where('Status', $this->filters['Status']);
        }

        if (!empty($this->filters['TripType'])) {
            $query->where('TripTypeID', $this->filters['TripType']);
        }

        if (!empty($this->filters['EmpID'])) {
            $query->whereHas('userdata', function ($q) {
                $q->where('emp_id', $this->filters['EmpID']);
            });
        }

        if (!empty($this->filters['GradeID'])) {
            $query->where('GradeID', $this->filters['GradeID']);
        }

        if (!empty($this->filters['BranchID'])) {
            $query->where('VisitBranch', $this->filters['BranchID']);
        }

        $results = $query->get();

        return $results->map(function ($row) {
            return [
                'Trip ID'        => 'TMG' . substr($row->TripClaimID, 8),
                'Date'           => $row->created_at->format('Y-m-d'),
                'Trip Type'      => optional($row->triptypedetails)->TripTypeName,
                'Employee Name'  => optional($row->userdata)->emp_name,
                'Employee ID'    => optional($row->userdata)->emp_id,
                'Visit Branch'   => optional($row->visitbranchdetails)->BranchName,
                'Grade'          => optional($row->gradedetails)->GradeName,
                'Department'     => optional($row->departmentdetails)->DepartmentName,
                'Amount'         => $row->TotalAmount,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Trip ID',
            'Date',
            'Trip Type',
            'Employee Name',
            'Employee ID',
            'Visit Branch',
            'Grade',
            'Department',
            'Amount',
        ];
    }
}
