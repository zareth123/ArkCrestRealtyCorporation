<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.login');
    }
    public function sessionCheck()
    {
    return response()->json(['authenticated' => auth()->check()])
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private')
        ->header('Pragma', 'no-cache');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        $passwordValid = $user && Hash::check($request->password, $user->password);

        if (!$user || !$passwordValid) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput($request->only('email', 'remember'));
        }

        if ($user->status === 'pending') {
            return back()->withErrors(['email' => 'Your account is pending admin approval.'])->withInput($request->only('email', 'remember'));
        }

        try {
            if (\Schema::hasColumn('sales_agents', 'is_active')) {
                $query = \App\Models\SalesAgent::where('is_active', false);
                if (\Schema::hasColumn('sales_agents', 'user_id')) {
                    $query->where(function($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->orWhereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($user->name))]);
                    });
                } else {
                    $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($user->name))]);
                }
                $inactiveAgent = $query->first();
                if ($inactiveAgent) {
                    Auth::logout();
                    return back()->with('inactive_agent', true)->withInput($request->only('email'));
                }
            }
        } catch (\Exception $e) {

        }

        Auth::login($user, $request->boolean('remember'));
        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();
        ActivityLog::log('login', 'Auth', "User '{$user->name}' logged in");

        // Push login-time notifications for all users
        if (true) {
            // Today's commission releases — admin only
            if ($user->isAdmin()) {
                $todayReleases = \App\Models\CommissionRequestSales::whereDate('date_released', today())
                    ->where('status', 'Released')->count();
                if ($todayReleases > 0) {
                    $alreadyNotified = \App\Models\SystemNotification::where('user_id', $user->id)
                        ->where('type', 'commission_release')->where('title', 'Commission Releases Today')
                        ->whereDate('created_at', today())->exists();
                    if (!$alreadyNotified) {
                        \App\Models\SystemNotification::notify($user->id, 'commission_release', 'Commission Releases Today', "{$todayReleases} commission release(s) scheduled for today.");
                    }
                }
                $tomorrowReleases = \App\Models\CommissionRequestSales::whereDate('date_released', today()->addDay())
                    ->whereIn('status', ['Not Yet Released', 'Not Released'])->count();
                if ($tomorrowReleases > 0) {
                    $alreadyNotified = \App\Models\SystemNotification::where('user_id', $user->id)
                        ->where('type', 'commission_release')->where('title', 'Commission Releases Tomorrow')
                        ->whereDate('created_at', today())->exists();
                    if (!$alreadyNotified) {
                        \App\Models\SystemNotification::notify($user->id, 'commission_release', 'Commission Releases Tomorrow', "{$tomorrowReleases} commission release(s) scheduled for tomorrow.");
                    }
                }
            }

            $dueNotes = \App\Models\Note::where('user_id', $user->id)
                ->whereDate('note_date', today())
                ->where('reminder_sent', false)
                ->get();
            foreach ($dueNotes as $note) {
                $alreadyNotified = \App\Models\SystemNotification::where('user_id', $user->id)
                    ->where('type', 'note_reminder')
                    ->where('title', 'Note Reminder: ' . $note->title)
                    ->whereDate('created_at', today())
                    ->exists();
                if (!$alreadyNotified) {
                    \App\Models\SystemNotification::notify(
                        $user->id, 'note_reminder',
                        'Note Reminder: ' . $note->title,
                        ($note->body ? \Illuminate\Support\Str::limit($note->body, 80) : 'You have a note scheduled today.') .
                        ($note->reminder_time ? ' at ' . \Carbon\Carbon::parse($note->reminder_time)->format('g:i A') : '')
                    );
                }
            }
        }

        $salesPositions = ['sales agent', 'sales manager', 'sales person', 'salesperson', 'sales team leader', 'sales personnel'];
        if (!$user->isAdmin() && in_array(strtolower(trim($user->position ?? '')), $salesPositions)) {
            return redirect()->route('tripping');
        }

        if ($user->isAdmin()) {
            return redirect()->route('dashboard');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email',
            'position'           => 'required|string|max:255',
            'employee_id'        => 'required|string|max:100',
            'date_hired'         => 'required|date',
            'password'           => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->numbers()->symbols()],
            'preferred_address'  => 'nullable|string|max:100',
        ]);

        if (User::where('employee_id', $request->employee_id)->where('status', 'active')->exists()) {
            return back()->withErrors(['employee_id' => 'This Employee ID already has an account.'])->withInput($request->except('password', 'password_confirmation'));
        }

        $preSet = User::where('employee_id', $request->employee_id)->where('status', 'pre_registered')->first();
        if (!$preSet) {
            return back()->withErrors(['employee_id' => 'Employee ID not found. Please contact your admin.'])->withInput($request->except('password', 'password_confirmation'));
        }

        if ($preSet->date_hired && $preSet->date_hired->format('Y-m-d') !== $request->date_hired) {
            return back()->withErrors(['date_hired' => 'Employee ID and Date Hired do not match our records.'])->withInput($request->except('password', 'password_confirmation'));
        }

        if (strtolower(trim($preSet->name)) !== strtolower(trim($request->name))) {
            return back()->withErrors(['name' => 'Name does not match our records for this Employee ID.'])->withInput($request->except('password', 'password_confirmation'));
        }

        if ($preSet->position && strtolower(trim($preSet->position)) !== strtolower(trim($request->position))) {
            return back()->withErrors(['position' => 'Position does not match our records for this Employee ID.'])->withInput($request->except('password', 'password_confirmation'));
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        \DB::table('email_verification_codes')->where('email', $request->email)->delete();
        \DB::table('email_verification_codes')->insert([
            'email'      => $request->email,
            'code'       => $code,
            'form_data'  => json_encode($request->only('name', 'preferred_address', 'email', 'position', 'employee_id', 'date_hired', 'password', 'password_confirmation', 'security_question', 'security_answer')),
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Mail::to($request->email)->send(new \App\Mail\EmailVerificationCode($code, $request->name));

        return redirect()->route('register.verify', ['email' => $request->email])
            ->with('success', "A verification code has been sent to {$request->email}.");
    }

    public function showVerify(Request $request)
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.verify', ['email' => $request->email]);
    }

    public function verifyAndRegister(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string|size:6',
        ]);

        $record = \DB::table('email_verification_codes')
            ->where('email', $request->email)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return back()->withErrors(['code' => 'Invalid or expired verification code.'])->withInput();
        }

        $data = json_decode($record->form_data, true);

        if (\App\Models\User::where('email', $data['email'])->whereNotIn('status', ['pre_registered'])->exists()) {
            return back()->withErrors(['code' => 'This email is already registered.']);
        }

        $preSet = User::where('employee_id', $data['employee_id'])->where('status', 'pre_registered')->first();
        if ($preSet) {
            $preSet->update([
                'name'               => $data['name'],
                'preferred_address'  => $data['preferred_address'] ?? null,
                'email'              => $data['email'],
                'password'           => Hash::make($data['password']),
                'role'               => 'staff',
                'status'             => 'pending',
                'security_question'  => $data['security_question'] ?? null,
                'security_answer'    => $data['security_answer'] ? Hash::make(strtolower(trim($data['security_answer']))) : null,
            ]);
            $newUser = $preSet->fresh();
        } else {
            $newUser = User::create([
                'name'               => $data['name'],
                'preferred_address'  => $data['preferred_address'] ?? null,
                'email'              => $data['email'],
                'position'           => $data['position'] ?? null,
                'employee_id'        => $data['employee_id'] ?? null,
                'date_hired'         => $data['date_hired'] ?? null,
                'password'           => Hash::make($data['password']),
                'role'               => 'staff',
                'status'             => 'pending',
            ]);
        }

        \DB::table('email_verification_codes')->where('email', $request->email)->delete();

        User::where('role', 'admin')->each(function($admin) use ($newUser) {
            \App\Models\SystemNotification::notify(
                $admin->id, 'user_pending',
                'New User Registration',
                "{$newUser->name} ({$newUser->position}) is waiting for account approval."
            );
        });

        return redirect()->route('register.success');
    }

    public function logout(Request $request)
    {
        $name = auth()->user()->name ?? 'Unknown';
        ActivityLog::log('logout', 'Auth', "User '{$name}' logged out");
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function getSecurityQuestion(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Email not found. Please check and try again.'], 422);
        }

        if ($request->input('check_only')) {
            return response()->json([
                'success'      => true,
                'has_question' => !empty($user->security_question),
                'question'     => $user->security_question,
            ]);
        }

        if (!$user->security_question) {
            return response()->json(['success' => false, 'message' => 'No security question set for this account. Please contact your administrator to reset your password.'], 422);
        }

        return response()->json(['success' => true, 'question' => $user->security_question]);
    }

    public function checkSecurityQuestion(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'answer'   => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->whereNotNull('security_question')
            ->whereNotNull('security_answer')
            ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Account not found or no security question set.'], 422);
        }

        if (!Hash::check(strtolower(trim($request->answer)), $user->security_answer)) {
            return response()->json(['success' => false, 'message' => 'Incorrect answer. Please try again.'], 422);
        }

        $token = bin2hex(random_bytes(16));
        \DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        \DB::table('password_reset_tokens')->insert([
            'email'      => $user->email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        return response()->json([
            'success'   => true,
            'question'  => $user->security_question,
            'token'     => $token,
            'email'     => $user->email,
        ]);
    }

    public function sendPasswordResetEmail(Request $request)
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['success' => false]);
        }

        $token = \Illuminate\Support\Str::random(64);
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $resetUrl = url('/login') . '?reset_token=' . $token . '&email=' . urlencode($email);

        try {
            \Mail::send([], [], function ($m) use ($user, $resetUrl) {
                $m->to($user->email)
                  ->subject('Password Reset - ArkCrest Realty Corporation')
                  ->html('
                    <div style="font-family:Arial,sans-serif;max-width:480px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.08);">
                        <div style="background:#1e4575;padding:24px 32px;">
                            <h1 style="color:#fff;margin:0;font-size:18px;">ArkCrest Realty Corporation</h1>
                        </div>
                        <div style="padding:32px;">
                            <p style="font-size:15px;color:#374151;">Hi <strong>' . $user->name . '</strong>,</p>
                            <p style="font-size:14px;color:#374151;">You requested a password reset. Click the button below to reset your password.</p>
                            <div style="margin:24px 0;">
                                <a href="' . $resetUrl . '" style="background:#1e4575;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600;">Reset Password</a>
                            </div>
                            <p style="font-size:12px;color:#9ca3af;">This link expires in 15 minutes. If you did not request this, ignore this email.</p>
                        </div>
                    </div>
                  ');
            });
        } catch (\Exception $e) {}

        return response()->json(['success' => true]);
    }

    public function resetPasswordByQuestion(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'token'    => 'required|string',
                'password' => ['required', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->numbers()->symbols()],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first();
            return response()->json(['success' => false, 'message' => $msg], 422);
        }

        $record = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !\Hash::check($request->token, $record->token)) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired token. Please start over.'], 422);
        }

        if (\Carbon\Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            \DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['success' => false, 'message' => 'Token expired. Please start over.'], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 422);
        }

        $user->update(['password' => \Hash::make($request->password)]);
        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['success' => true, 'message' => 'Password reset successfully.']);
    }
}
