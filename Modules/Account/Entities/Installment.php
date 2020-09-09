<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    protected $table = "account_installments";
  
    protected $fillable = [
        'student_id',
        'type', // ['old', 'new']
        'value',
        'date',
        'paid'
    ];
     
    public function student() {
        return $this->belongsTo('Modules\Student\Entities\Student', 'student_id');
    }
    
}
