<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Account\Entities\Store; 

class StoreController extends Controller
{

    public function __construct() {
        // permission
    } 

    /**
     * return all data in json format
     * @return json
     */
    public function index() {
        $resources = Store::get();
        return $resources;
    }
 
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */ 
    public function store(Request $request) {
        $resource = null;
        try { 
              
            //return dump(toClass($data)->api_token);
            $validator = validator($request->json()->all(), [
                "name" =>  "required|unique:account_Stores", 
                "balance" =>  "required", 
                "init_balance" =>  "required", 
            ], [
                "name.unique" => __('name already exist'),
                "balance.required" => __('fill all required data'),
                "init_balance.required" => __('fill all required data'), 
            ]);
            
            if ($validator->fails()) {
                return responseJson(0, $validator->errors()->first());
            }
            $data = $request->all();
            $data['user'] = $request->user->id;
             
            $resource = Store::create($data); 
            watch(__('add Store ') . $resource->name, "fa fa-trophy");
        } catch (\Exception $th) {
            return responseJson(0, $th->getMessage());
        }
        
        return responseJson(1, __('done'), $resource);
    }
  

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, Store $Store) {
        try { 
            $Store->update($request->all());
            watch(__('edit Store ') . $Store->name, "fa fa-trophy");
        } catch (\Exception $th) {
            return responseJson(0, $th->getMessage());
        }
        
        return responseJson(1, __('done'), $Store->fresh());
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy(Store $Store) { 
        try { 
            watch(__('remove Store ') . $Store->name, "fa fa-trophy"); 
           
        } catch (\Exception $th) {
            return responseJson(0, $th->getMessage());
        }
        return responseJson(1, __('done'));
    }
}