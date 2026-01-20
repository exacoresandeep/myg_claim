<?php

namespace App\Exports;

use App\Models\ClaimManagement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClaimExport implements FromCollection, WithHeadings, WithMapping
{
    
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        return ClaimManagement::select([
                'TripClaimID', 'created_at', 'AdvanceAmount', 'SettleAmount', 'Status',
                'user_id', 'TripTypeID', 'VisitBranchID'
            ])->with(['visitbranchdetails', 'triptypedetails', 'userdata', 'tripclaimdetails'])
            ->when($this->request->FromDate, fn($q) => $q->whereDate('created_at', '>=', $this->request->FromDate))
            ->when($this->request->ToDate, fn($q) => $q->whereDate('created_at', '<=', $this->request->ToDate))
            ->when($this->request->TripType, fn($q) => $q->whereHas('triptypedetails', fn($sub) => $sub->where('TripTypeID', $this->request->TripType)))
            ->when($this->request->EmpID, fn($q) => $q->whereHas('userdata', fn($sub) => $sub->where('emp_id', 'like', '%'.$this->request->EmpID.'%')))
            ->when($this->request->Status, fn($q) => $q->where('Status', $this->request->Status))
            ->when($this->request->GradeID, fn($q) => $q->whereHas('userdata', fn($sub) => $sub->where('emp_grade', $this->request->GradeID)))
            ->when($this->request->BranchID, fn($q) => $q->whereHas('visitbranchdetails', fn($sub) => $sub->where('BranchID', $this->request->BranchID)))
            ->get();
    }

    public function headings(): array
    {
        return [
            'Claim ID',
            'Date',
            'Employee ID',
            'Employee Name',
            'Grade',
            'Trip Type',
            'Branch',
            'Status',
            'Total Amount',
            'Advance Amount',
            'Settle Amount',
        ];
    }

    public function map($claim): array
    {
        set_time_limit(0);
        $tripDetails = collect($claim->tripclaimdetails ?? []);
        $TotalAmount = $claim->sumTripClaimDetailsValue1();
        return [
            'Claim ID'       => 'TMG' . substr($claim->TripClaimID, 8),
            'Date'           => optional($claim->created_at)->format('Y-m-d'),
            'Emp ID'         => optional($claim->userdata)->emp_id,
            'Emp Name'       => optional($claim->userdata)->emp_name,
            'Grade'          => optional($claim->userdata)->emp_grade,
            'Trip Type'      => optional($claim->triptypedetails)->TripTypeName,
            'Branch'         => optional($claim->visitbranchdetails)->BranchName,
            'Status'         => $claim->Status,
            'Total Amount'   => $TotalAmount, 
            'Advance Amount' => $claim->AdvanceAmount ?? '-',
            'Settle Amount'  => $claim->SettleAmount ?? '-',
        ];
    }
       

}
