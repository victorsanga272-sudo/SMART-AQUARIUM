<?php

namespace App\Http\Controllers;

use App\Models\VivoUsers;
use App\Mail\RegistrationSuccessful;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class VivoUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $vivo_users=VivoUsers::all();
        return response()->json([
            'users'=>$vivo_users
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return  view('log-in');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validate_vivo_users=$request->validate([
            'username'=> 'required|string|max:15',
            'email'=> [
                'required',
                'string',
                'email:rfc,dns',
                'unique:vivo_users,email',
            ],
            'password'=>'required|string|min:8|confirmed',
            'terms'=>'accepted',
        ]);

        $vivo_users = DB::transaction(function () use ($validate_vivo_users) {
            $user = VivoUsers::create([
                'username' => $validate_vivo_users['username'],
                'email' => strtolower($validate_vivo_users['email']),
                'password' => Hash::make($validate_vivo_users['password']),
            ]);

            return $user->load('aquarium');
        });

        $emailSent = true;

        try {
            Mail::to($vivo_users->email)->send(new RegistrationSuccessful($vivo_users));
        } catch (\Throwable $exception) {
            $emailSent = false;
            report($exception);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message'=> 'user created successfully...',
                'user'=> $vivo_users,
                'email_sent' => $emailSent,
            ], 201);
        }

        return redirect()->route('vivo_users.create')
            ->with('status', $emailSent
                ? 'Account created successfully. Please log in.'
                : 'Account created, but the welcome email could not be sent. Please log in.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'identifier' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        $rememberMe = $request->boolean('remember_me');
        $identifier = $credentials['identifier'];
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $value = $field === 'email' ? strtolower($identifier) : $identifier;

        if (!Auth::attempt([$field => $value, 'password' => $credentials['password']], $rememberMe)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The provided credentials are incorrect.',
                    'errors' => [
                        'identifier' => ['The provided credentials are incorrect.'],
                    ],
                ], 422);
            }

            return back()->withErrors(['identifier' => 'The provided credentials are incorrect.']);
        }

        $request->session()->regenerate();
        $user = Auth::user()->loadMissing('aquarium');

        return response()->json([
            'message' => 'login successful',
            'user' => $user,
        ]);
    }

    public function logoutAllDevices(Request $request)
    {
        DB::table(config('session.table'))
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('vivo_users.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(VivoUsers $vivoUsers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VivoUsers $vivoUsers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VivoUsers $vivoUsers)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VivoUsers $vivoUsers)
    {
        //
    }
}
