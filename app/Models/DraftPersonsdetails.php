<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DraftTripclaimdetails;
class DraftPersonsdetails extends Model
{
    use HasFactory;
    public $table="myg_15_draft_persons_details";
    protected $fillable = [
        'PersonDetailsID',
        'TripClaimDetailID',
        'Grade',
        'EmployeeID',
        'ClaimOwner',
        'user_id'
    ];

    public function userDetails()
    {
        return $this->hasMany(User::class, 'id','EmployeeID');
    }

    public function userData()
    {
        return $this->belongsTo(User::class, 'EmployeeID', 'id');
    }

    public function tripClaimDetail()
    {
        return $this->belongsTo(DraftTripclaimdetails::class, 'TripClaimDetailID', 'TripClaimDetailID');
    }
}
