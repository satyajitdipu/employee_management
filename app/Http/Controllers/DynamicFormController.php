<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Models\Employee;
use Illuminate\Http\Request;



class DynamicFormController extends Controller
{

    public function store(Request $request)
    {
        $jsonData = json_decode($request->json_data);
        foreach ($jsonData as $jsonObject) {
            Settings::create([
                'custom_employee_fields' => json_encode($jsonObject),
                'field_id' => $request->uuid,
                'field_section' => $request->section

            ]);
        }
        // return response()->json(['redirect' => '/cookie-dashboard']);
    }

    public function retrieve()
    {
        $data = Settings::all();
        return response()->json(['data' => $data]);
    }

    public function delete(Request $request)
    {
        $data = Settings::where('field_id', $request->uuid)->first();
        if ($data) {
            $data->delete();
        }
    }


    public function update(Request $request)
    {
        $jsonData = json_decode($request->json_data);
        $data = Settings::where('field_id', $request->uuid)->first();
        if ($data) {
            foreach ($jsonData as $jsonObject) {
                $data->update([
                    'custom_employee_fields' => json_encode($jsonObject),
                    'field_section' => $request->section
                ]);
            }
        }
    }
}
