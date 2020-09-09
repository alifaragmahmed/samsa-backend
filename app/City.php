<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model {

    protected $table = 'cities';
    protected $fillable = ['name', 'notes', 'government_id'];

    public function government() {
        return $this->belongsTo('App\Government', 'government_id');
    }

    public function applications() {
        return $this->hasMany('Modules\Adminsion\Entities\Application', 'city_id');
    }

    public function students() {
        return $this->hasMany('Modules\Student\Entities\Student', 'city_id');
    }

}
