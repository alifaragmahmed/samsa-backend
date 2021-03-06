<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use Modules\Account\Entities\Student;
use Modules\Account\Entities\StudentBalance;
use Modules\Account\Entities\AccountSetting;
use Modules\Account\Entities\Payment;
use Modules\Account\Entities\Store;
use Modules\Account\Entities\StudentPay;
use Modules\Account\Entities\DiscountRequest;
use Modules\Account\Entities\BalanceReset;

use App\User;
use DB;

class AccountController extends Controller
{

    /**
     * return student info
     *
     * @return type
     */
    public function getStudentAccounting() {
        $student = null;
        if (request()->student_id) {
            $student = Student::query()
            ->with(['level', 'division', 'case_constraint', 'constraint_status', 'installments', 'payments', 'registerationStatus', 'nationality', 'discount_requests', 'balanceResets'])
            ->find(request()->student_id);
        }

        $student->date = date("Y-m-d");
        return $student;
    }

    /**
     * pay money in store
     *
     */
    public function pay(Request $request)
    {
        $user = $request->user;

        $resource = null;
        try {
            $validator = validator($request->all(), [
                "value" =>  "required",
                "student_id" =>  "required"
            ]);
            if ($validator->failed()) {
                return responseJson(0, __('fill all required data'));
            }

            $student = Student::find($request->student_id);
            $resource = StudentPay::pay($request);

            $message = __('student {name} pay {value} in store');
            $message = str_replace("{name}", $student->name, $message);
            $message = str_replace("{value}", $request->value, $message);
            watch($message, "fa fa-money");
            return responseJson(1, $message, $resource);
        } catch (\Exception $th) {
            return responseJson(0, $th->getMessage());
        }

        return responseJson(1, __('done'), $resource);
    }

    /**
     * pay money in store
     *
     */
    public function refund(Request $request)
    {
        $user = $request->user;

        $resource = null;
        try {
            $validator = validator($request->all(), [
                "payment_id" =>  "required"
            ]);
            if ($validator->failed()) {
                return responseJson(0, __('fill all required data'));
            }

            $resource = StudentPay::payRefund($request);

            $message = __('student {name} refund {value} in store');
            $message = str_replace("{name}", optional($resource->student)->name, $message);
            $message = str_replace("{value}", $resource->value, $message);
            watch($message, "fa fa-money");
            return responseJson(1, $message, array($resource));
        } catch (\Exception $th) {
            return responseJson(0, $th->getMessage());
        }

        return responseJson(1, __('done'), $resource);
    }

    /**
     * pay money in store
     *
     */
    public function removePayment(Request $request)
    {
        $user = $request->user;

        $resource = null;
        try {
            $validator = validator($request->all(), [
                "payment_id" =>  "required"
            ]);
            if ($validator->failed()) {
                return responseJson(0, __('fill all required data'));
            }

            $resource = StudentPay::removePayment($request);

            $message = __('payment value removed from store ') . optional($resource->store)->name;
            watch($message, "fa fa-money");
            return responseJson(1, $message);
        } catch (\Exception $th) {
            return responseJson(0, $th->getMessage());
        }

        return responseJson(1, __('done'), $resource);
    }

    /**
     * edit payment info
     *
     */
    public function editPayment(Request $request)
    {
        $user = $request->user;

        $resource = null;
        try {
            $validator = validator($request->all(), [
                "id" =>  "required"
            ]);
            if ($validator->failed()) {
                return responseJson(0, __('fill all required data'));
            }
            $data = $request->all();
            $payment = Payment::find($request->id);

            if ($payment) {
                $request->payment_id = $request->id;
                // remove old
                StudentPay::removePayment($request);

                // add new
                $payment = Payment::addPayment($data);

            }

            watch(__('edit payment info of number ') . $payment->id, "fa fa-money");
        } catch (\Exception $th) {
            return responseJson(0, $th->getMessage());
        }

        return responseJson(1, __('done'), $resource);
    }

    /**
     * pay money in store
     *
     */
    public function updateAccountSetting(Request $request)
    {
        $user = $request->user;

        $resource = null;
        try {
            $validator = validator($request->all(), [
                "id" =>  "required",
                "value" =>  "required",
                "name" =>  "required"
            ]);
            if ($validator->failed()) {
                return responseJson(0, __('fill all required data'));
            }

            $resource = AccountSetting::updateSetting($request->id, $request->name, $request->value);

            watch(__('update old balance store settings '), "fa fa-cogs");
        } catch (\Exception $th) {
            return responseJson(0, $th->getMessage());
        }

        return responseJson(1, __('done'), $resource);
    }

    public function getSettings() {
        return AccountSetting::all();
    }

    public function searchStudent(Request $request) {
        return Student::query()//DB::table('students')
                ->where('name', 'like', '%'.$request->key.'%')
                ->orWhere('code', 'like', '%'.$request->key.'%')
                ->orWhere('national_id', 'like', '%'.$request->key.'%')
                ->take(10)
                ->get(["id", "name", "code"]);
    }


    public function getStudentAvailableServices(Request $request) {
        $user = $request->user;
        $validator = validator($request->all(), [
            "student_id" =>  "required"
        ]);
        if ($validator->failed()) {
            return [];
        }

        $student = Student::find($request->student_id);
        return $student->getAvailableServices();

    }

    public function writeStudentNote(Request $request) {
        $validator = validator($request->all(), [
            "notes" =>  "required",
            "student_id" =>  "required"
        ]);
        if ($validator->failed()) {
            return responseJson(0, __('write some notes'));
        }

        $student = Student::find($request->student_id);

        if ($student->notes)
            $student->notes .=  "\n" . $request->notes;
        else
            $student->notes = $request->notes;

        $student->update();
        return responseJson(1, __('done'));
    }

    public function updateStudentInfo(Request $request) {
        $validator = validator($request->all(), [
            "student_id" =>  "required"
        ]);
        if ($validator->failed()) {
            return responseJson(0, __('write some notes'));
        }

        $student = Student::find($request->student_id);

        $student->update($request->all());
        return responseJson(1, __('done'));
    }
 
    public function createBalanceReset(Request $request) {
        $validator = validator($request->all(), [
            "student_id" =>  "required",
            "value" =>  "required" 
        ]);
        if ($validator->failed()) {
            return responseJson(0, __('fill all required data'));
        }

        try {
            $data = $request->all();
            $data['user_id'] = $request->user->id;
            $data['date'] = date('Y-m-d');

            $student = Student::find($request->student_id);

            if ($student->old_balance > 0)
                $data['type'] = 'old';
            else
                $data['type'] = 'new';

            $resource = BalanceReset::create($data);

            return responseJson(1, __('done'), $resource);
        } catch (\Exception $e) {
            return responseJson(0, $e->getMessage());
        }
    }
}
