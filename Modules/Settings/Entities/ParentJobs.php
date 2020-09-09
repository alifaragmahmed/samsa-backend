<?php

namespace Modules\Settings\Entities;

use Illuminate\Database\Eloquent\Model;

class ParentJobs extends Model
{
    protected $table = 'parent_jobs';
    protected $fillable = ['name','notes'];
    public function applications() {
        return $this->hasMany('Modules\Adminsion\Entities\Application', 'parent_job_id');
    }

    public function students() {
        return $this->hasMany('Modules\Student\Entities\Student', 'parent_job_id');
    }
}
