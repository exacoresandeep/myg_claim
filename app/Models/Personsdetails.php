<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tripclaimdetails;
class Personsdetails extends Model
{
    use HasFactory;
    public $table="myg_10_persons_details";
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
        return $this->belongsTo(Tripclaimdetails::class, 'TripClaimDetailID', 'TripClaimDetailID');
    }
}
