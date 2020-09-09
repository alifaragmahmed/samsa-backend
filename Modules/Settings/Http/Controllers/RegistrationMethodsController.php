<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Settings\Entities\RegistrationMethod;
use Modules\Settings\Http\Requests\RegisterationMethodsRequest;

class RegistrationMethodsController extends Controller {

    public function __construct() {
        $this->middleware(['permission:registeration-methods_read'])->only('index');
        $this->middleware(['permission:registeration-methods_create'])->only('create');
        $this->middleware(['permission:registeration-methods_update'])->only('edit');
        $this->middleware(['permission:registeration-methods_delete'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index() {
        $registerationMethods = RegistrationMethod::OrderBy('created_at', 'desc')->get();
        return view('settings::registeration_methods.index', compact('registerationMethods'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create() {
        return view('settings::registeration_methods.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(RegisterationMethodsRequest $request) {
        try {
            $registerationMethods = RegistrationMethod::create($request->all());
            if ($registerationMethods) {
                notify()->success(__('data created successfully'), "", "bottomLeft");
            } else {
                notify()->error("هناك خطأ ما يرجى المحاولة فى وقت لاحق", "", "bottomLeft");
            }
        } catch (\Exception $th) {
            notify()->error($th->getMessage(), "", "bottomLeft");
        }
        return redirect()->route('registeration-methods.index');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id) {
        return view('settings::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id) {
        $registerationMethod = RegistrationMethod::find($id);
        if (!$registerationMethod) {
            notify()->warning(__('data not found'), "", "bottomLeft");
            return redirect()->route('registeration-methods.index');
        }
        return view('settings::registeration_methods.edit', compact('registerationMethod'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(RegisterationMethodsRequest $request, $id) {
        try {
            $registerationMethods = RegistrationMethod::find($id);
            if (!$registerationMethods) {
                notify()->warning(__('data not found'), "", "bottomLeft");
            } else {
                $registerationMethods->update($request->all());
                notify()->success(__('data updated successfully'), "", "bottomLeft");
            }
        } catch (\Exception $ex) {
            notify()->error($ex->getMessage(), "", "bottomLeft");
        }
        return redirect()->route('registeration-methods.index');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id) {
        try {
            $registerationMethods = RegistrationMethod::find($id);
            if (!$registerationMethods) {
                notify()->warning(__('data not found'), "", "bottomLeft");
            }
            $registerationMethods->delete();
            notify()->success(__('data deleted successsfully'), "", "bottomLeft");
        } catch (\Exception $ex) {
            notify()->error($ex->getMessage(), "", "bottomLeft");
        }
        return redirect()->route('registeration-methods.index');
    }

}
