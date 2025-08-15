<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id', 'voter_phone', 'amount', 'operator', 'payment_status',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
