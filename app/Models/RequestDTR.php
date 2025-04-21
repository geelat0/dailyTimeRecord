<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestDTR extends Model
{

    protected $table = 'request_dtr';
    protected $fillable =
    [
        'user_id', 
        'approver_id', 
        'subject',
        'attachment',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
    
}
