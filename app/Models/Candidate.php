<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'photo', 'phone', 'status',
    ];

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
