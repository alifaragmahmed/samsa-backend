<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Student\Entities\Student as StudentOrigin;

use DB;

class Student extends StudentOrigin
{

    protected $table = "students";

    protected $appends = [
        'is_current_installed',
        'is_old_installed',
        'current_balance',
        'old_balance',
        'paid_value',
        'paids',
        'gpa',
        'services',
        'image',
        'can_convert_to_student',
        'payment_details',
        'old_balance_notes',
        'can_edit_old_balance'
    ];

    public function getCanEditOldBalanceAttribute() {
        $can = true;
        if (Payment::where('model_type', 'old_academic_year_expense')->where('student_id', $this->id)->count() > 0) {
            $can = false;
        }

        if (Installment::where('type', 'old')->where('student_id', $this->id)->count() > 0) {
            $can = false;
        }

        return $can;
    }

    public function getOldBalanceNotesAttribute() {
        return optional(StudentOldBalance::where('student_id', $this->id)->first())->notes;
    }

    public function getPaymentDetailsAttribute() {
        $payments = [];
        foreach($this->payments()->get() as $payment) {
            if (!isset($payments[$payment->date]))
                $payments[$payment->date] = [ 'total' => 0, "payments" => [] ];


            $payments[$payment->date]['date'] = $payment->date;
            $payments[$payment->date]['total'] += $payment->value;
            $payments[$payment->date]['payments'][] = $payment;
        }
        $arr = [];
        foreach($payments as $key => $value) {
            $arr[] = $value;
        }

        return $arr;
    }

    public function getCanConvertToStudentAttribute() {
        $ids = DB::table("account_academic_year_expenses_details")->where('priorty', 1)->pluck('id')->toArray();
        $paymentCounts = DB::table('account_payments')
        ->where('model_type', 'academic_year_expense')
        ->where('student_id', $this->id)
        ->whereIn('model_id', $ids)->count();

        return $paymentCounts > 0? true : false;
    }

    public function getOldBalanceAttribute() {
        return $this->getStudentBalance()->getOldBalance();
    }

    public function getPaidValueAttribute() {
        return $this->getStudentBalance()->getPaidValue();
    }

    public function getCurrentBalanceAttribute() {
        // $this->getStudentBalance()->getCurrentBalance();
        return $this->getStudentBalance()->getPaidValue();
    }

    public function getPaidsAttribute() {
        return $this->payments()->sum("value");
    }

    public function getGpaAttribute() {
        return 1.5;
    }

    public function getServicesAttribute() {
        $ids = StudentService::where('student_id', $this->id)->pluck('service_id')->toArray();
        return Service::whereIn('id', $ids)->get();
    }

    public function getImageAttribute() {
        $studentImage = DB::table('students')->find($this->id);

        $path = $studentImage->personal_photo;
        return $path? url($path) : '/assets/img/avatar.png';
    }

    public function getStudentBalance() {
        return StudentBalance::find($this->id);
    }

    public function getIsCurrentInstalledAttribute() {
        $installment = DB::table('account_installments')
                ->where('student_id', $this->id)
                ->where('type', 'new')
                ->where('paid', '0')
                ->first();

        return $installment? true : false;
    }

    public function getIsOldInstalledAttribute() {
        $installment = DB::table('account_installments')
                ->where('student_id', $this->id)
                ->where('type', 'old')
                ->where('paid', '0')
                ->first();

        return $installment? true : false;
    }

    public function installments() {
        return $this->hasMany("Modules\Account\Entities\Installment", "student_id")->orderBy('date');
    }

    public function payments() {
        return $this->hasMany("Modules\Account\Entities\Payment", "student_id")->with(['store']);
    }

    public function level() {
        return $this->belongsTo("Modules\Divisions\Entities\Level", "level_id")->select(['id', 'name']);
    }

    public function division() {
        return $this->belongsTo("Modules\Divisions\Entities\Division", "division_id")->select(['id', 'name']);
    }

    public function canGetService() {

    }

    public function getAvailableServices() {
        $ids = [];
        $services = Service::all();

        foreach ($services as $service) {
            $res = AccountSetting::canStudentGetService($service, $this);

            $service->valid = $res['valid'];
            $service->reason = $res['reason'];
            //if ($res['valid']) {
            //    $ids[] = $service->id;
            //}
        }

        return $services;//Service::whereIn('id', $ids)->get(['id', 'name', 'value', 'additional_value']);
    }

    public function changeStudentCaseConstraint() {
        if ($this->case_constraint_id == 1 && $this->can_convert_to_student) {
            $this->case_constraint_id = 2;
            $this->is_application = 0;
            $this->update();
        }
    }



}
