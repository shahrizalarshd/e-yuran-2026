<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Resident;
use App\Models\House;
use App\Models\HouseMember;
use App\Models\AuditLog;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $houses = House::orderBy('street_name')
            ->orderBy('house_no')
            ->get();

        return view('auth.register', compact('houses'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:20'],
            'ic_number' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'house_id' => ['required', 'exists:houses,id'],
            'relationship' => ['required', 'in:owner,spouse,child,family,tenant'],
        ]);

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'resident',
                'language_preference' => 'bm',
                'is_active' => true,
            ]);

            // Create resident profile
            $resident = Resident::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'ic_number' => $request->ic_number,
                'language_preference' => 'bm',
            ]);

            // Create house member (pending approval)
            $houseMember = HouseMember::create([
                'house_id' => $request->house_id,
                'resident_id' => $resident->id,
                'relationship' => $request->relationship,
                'can_view_bills' => true,
                'can_pay' => in_array($request->relationship, ['owner', 'tenant']),
                'status' => 'pending',
            ]);

            DB::commit();

            AuditLog::log('register', $user, null, $user->toArray(), "New user registered: {$user->email}");

            event(new Registered($user));

            Auth::login($user);

            return redirect(route('dashboard', absolute: false));

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
