<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $students=Student::all();
    return view('student_view', compact('students'));
       
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('students');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validatedData = $request->validate([
            'registration_number' => 'required|unique:students',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',   
            'gender' => 'required|string|max:10',
            'date_of_birth' => 'required|date',
            'email' => 'required|email|unique:students',
            'phone_number' => 'nullable|string|max:11',
            'address' => 'nullable|string|max:255'
        ]);
        Student::create($request->all());   
     
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        //

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        //
    }
}
