<?php
namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'emp_id',
        'emp_name',
        'user_name',
        'email',
        'emp_phonenumber',
        'password',
        'emp_department',
        'emp_branch',
        'emp_baselocation',
        'emp_designation',
        'emp_grade',
        'reporting_person',
        'reporting_person_empid',
        'emp_role',
        'hrms_baselocation_flag',
        'hrms_reporting_person_flag',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }   

    public function branchData()
    {
        return $this->hasOne(Branch::class, 'BranchID','emp_branch');
    } 
    public function branchDetails()
    {
        return $this->belongsTo(Branch::class, 'emp_branch','BranchID');
    } 
    public function gradeDetails()
    {
        return $this->belongsTo(Grades::class, 'emp_grade','GradeID');
    } 
    public function baselocationDetails()
    {
        return $this->belongsTo(Branch::class, 'emp_baselocation','BranchID');
    } 
}