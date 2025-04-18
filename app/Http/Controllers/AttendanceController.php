<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        return Attendance::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
        ]);

        return Attendance::create($request->all());
    }

    public function show(Attendance $attendance)
    {
        return $attendance;
    }

    public function update(Request $request, Attendance $attendance)
    {
        $attendance->update($request->all());
        return $attendance;
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return response()->noContent();
    }
}