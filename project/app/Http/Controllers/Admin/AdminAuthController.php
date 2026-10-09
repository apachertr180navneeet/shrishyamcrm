<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Mail, DB, Hash, Validator, Session, File,Exception;

class AdminAuthController extends Controller
{
    
    public function index()
    {
        try{
            if(Auth::user()) {
                $user = Auth::user();
                if($user->role == "admin") {
                    return redirect()->route('admin.dashboard');
                }else{
                    return back()->with("error","Opps! You do not have access this");
                }
            }else{
                return redirect()->route('admin.login');
            }

        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    

    public function login()
    {
        return view("admin.auth.login");
    }

    public function registration()
    {
        return view("admin.auth.registration");
    }

    public function postLogin(Request $request)
    {
        try {
            $request->validate([
                "password" => "required|string",
            ]);

            $loginInput = trim($request->input('email') ?? $request->input('mobile') ?? $request->input('username') ?? $request->input('login') ?? '');
            if ($loginInput === '') {
                return back()->withInput()->with('error', 'कृपया लॉगिन विवरण (ईमेल या मोबाइल नंबर) दर्ज करें।');
            }

            $password = (string)$request->password;
            $loginType = $request->input('login_type'); // 'admin', 'agent', or null

            // Auto-detect login type if not explicitly provided
            if (!$loginType) {
                if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
                    $loginType = 'admin';
                } else {
                    $loginType = 'agent';
                }
            }

            // ==========================================
            // 1. ADMIN LOGIN (Email Only)
            // ==========================================
            if ($loginType === 'admin') {
                if (!filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
                    return back()->withInput()->with("error", "एडमिन लॉगिन के लिए कृपया एक मान्य ईमेल पता दर्ज करें (Admin must log in with Email address).");
                }

                $user = User::where('status', 'active')
                    ->where('email', $loginInput)
                    ->first();

                // If user not found, or password does not match
                if (!$user || !Hash::check($password, $user->password)) {
                    return back()->withInput()->with("error", "अमान्य एडमिन ईमेल या पासवर्ड (Invalid Email or Password).");
                }

                // Verify user is an Admin / Super Admin
                if (!$user->isAdmin()) {
                    if ($user->isAgent()) {
                        return back()->withInput()->with("error", "यह ईमेल एडमिन खाते से संबंधित नहीं है। कार्यकर्ता कृपया कार्यकर्ता (Agent) टैब चुनकर मोबाइल नंबर से लॉगिन करें।");
                    }
                    return back()->with("error", "इस खाते के पास एडमिन पोर्टल का अधिकार नहीं है (Unauthorized access).");
                }

                Auth::login($user, $request->filled('remember'));
                $request->session()->regenerate();
                return redirect()->route('admin.dashboard')->with('success', "स्वागत है, {$user->full_name}!");
            }

            // ==========================================
            // 2. AGENT LOGIN (Mobile Number Only)
            // ==========================================
            if ($loginType === 'agent') {
                // Disallow email format on agent tab
                if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
                    return back()->withInput()->with("error", "कार्यकर्ता लॉगिन के लिए कृपया अपना 10-अंकीय मोबाइल नंबर दर्ज करें (Agent must log in with Mobile Number).");
                }

                $cleanPhone = preg_replace('/[^0-9]/', '', $loginInput);
                if (strlen($cleanPhone) < 7) {
                    return back()->withInput()->with("error", "कृपया सही 10-अंकीय मोबाइल नंबर दर्ज करें (Invalid Mobile Number).");
                }

                $tenDigit = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

                // Search in users table first
                $user = User::where('status', 'active')
                    ->where(function($q) use ($loginInput, $cleanPhone, $tenDigit) {
                        $q->where('phone', $loginInput)
                          ->orWhere('phone', $cleanPhone)
                          ->orWhere('phone', $tenDigit)
                          ->orWhere('phone', 'like', "%{$tenDigit}");
                    })
                    ->first();

                // If not found in users table, find in agents table and link
                if (!$user) {
                    $agent = \App\Models\Agent::where(function($q) use ($loginInput, $cleanPhone, $tenDigit) {
                        $q->where('mobile', $loginInput)
                          ->orWhere('mobile', $cleanPhone)
                          ->orWhere('mobile', $tenDigit)
                          ->orWhere('mobile', 'like', "%{$tenDigit}");
                    })->first();

                    if ($agent) {
                        if ($agent->user_id) {
                            $user = User::where('id', $agent->user_id)->where('status', 'active')->first();
                        }
                        if (!$user) {
                            $user = User::where('agent_id', $agent->id)
                                ->orWhere('phone', $agent->mobile)
                                ->where('status', 'active')
                                ->first();
                        }
                        if ($user && !$user->agent_id) {
                            $user->update(['agent_id' => $agent->id]);
                        }
                    }
                }

                // If user not found, or password does not match
                if (!$user || !Hash::check($password, $user->password)) {
                    return back()->withInput()->with("error", "अमान्य मोबाइल नंबर या पासवर्ड (Invalid Mobile Number or Password).");
                }

                // Check if account is actually an admin trying to use mobile on agent tab
                if ($user->isAdmin() && !$user->isAgent()) {
                    return back()->withInput()->with("error", "यह मोबाइल नंबर कार्यकर्ता खाते से संबंधित नहीं है। एडमिन कृपया एडमिन (Admin) टैब चुनकर ईमेल से लॉगिन करें।");
                }

                // Verify agent access
                if (!$user->isAgent() && !in_array($user->role, ['agent', 'admin', 'super_admin'], true)) {
                    return back()->with("error", "इस खाते के पास कार्यकर्ता पोर्टल का अधिकार नहीं है (Unauthorized access).");
                }

                Auth::login($user, $request->filled('remember'));
                $request->session()->regenerate();
                return redirect()->route('admin.dashboard')->with('success', "स्वागत है, {$user->full_name}!");
            }

            return back()->withInput()->with("error", "अमान्य लॉगिन विवरण (Invalid login credentials).");
        }
        catch(Exception $e){
            return back()->with("error", $e->getMessage());
        }
    }

    public function showForgetPasswordForm()
    {
        return view("admin.auth.forgot-password");
    }

    public function submitForgetPasswordForm(Request $request)
    {
        try{
            $request->validate([
                "email" => "required|email|exists:users",
            ]);

            $token = Str::random(64);

            // Remove any previous tokens for this email (single-use, no stacking)
            DB::table("password_reset_tokens")->where("email", $request->email)->delete();

            DB::table("password_reset_tokens")->insert([
                "email" => $request->email,
                "token" => $token,
                "created_at" => Carbon::now(),
            ]);

            $new_link_token = url("admin/reset-password/" . $token);
            Mail::send("admin.email.forgot-password",["token" => $new_link_token, "email" => $request->email, "name" => $request->email],
                function ($message) use ($request) {
                    $message->to($request->email);
                    $message->subject("Reset Password");
                }
            );
            return redirect()->route("admin.login")->with("success","We have e-mailed your password reset link!");
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    
    }

    public function showResetPasswordForm($token)
    {
        try{    
            $record = DB::table("password_reset_tokens")
                ->where("token", $token)
                ->where("created_at", ">=", Carbon::now()->subHours(24))
                ->first();

            // Token missing or expired
            if (!$record) {
                return redirect()->route("admin.forget.password.get")
                    ->with("error", "This password reset link is invalid or has expired. Please request a new link.");
            }

            return view("admin.auth.reset-password", ["token" => $token, "email" => $record->email]);
        }
        catch(\Throwable $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function submitResetPasswordForm(Request $request)
    {
        try{
            $request->validate([
                "token" => "required|string",
                "email" => "required|email",
                "password" => "required|string|min:8|confirmed",
                "password_confirmation" => "required",
            ]);

            // Token must be in the route/request and not expired
            $updatePassword = DB::table("password_reset_tokens")
                ->where(["email" => $request->email, "token" => $request->token])
                ->where("created_at", ">=", Carbon::now()->subHours(24))
                ->first();

            if (!$updatePassword) {
                return back()->withInput()->with("error", "Invalid or expired token!");
            }

            $user = User::where("email", $request->email)->update(["password" => Hash::make($request->password)]);

            // Invalidate all reset tokens for this user (single-use)
            DB::table("password_reset_tokens")->where("email", $request->email)->delete();

            return redirect()->route("admin.login")->with("success","Your password has been changed successfully!");
        }
        catch(\Throwable $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function changePassword()
    {
        return view("admin.auth.change-password");
    }

    public function updatePassword(Request $request)
    {
        try{
            $request->validate([
                "old_password" => "required",
                "new_password" => "required|string|min:8|confirmed",
                "new_password_confirmation" => "required",
            ]);
            #Match The Old Password
            if (!Hash::check($request->old_password, auth()->user()->password)) {
                return back()->with("error", "Old Password Doesn't match!");
            }
            #Update the new Password
            User::whereId(auth()->user()->id)->update([
                "password" => Hash::make($request->new_password),
            ]);
            return back()->with("success", "Password changed successfully!");
        }
        catch(\Throwable $e){
            return back()->with("error",$e->getMessage());
        }
    }

    

    public function logout()
    {
        try{
            Session::flush();
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route("admin.login")->withSuccess('Logout Successful!');
        }
        catch(\Throwable $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function adminProfile()
    {
        try{
            $user = Auth::user();
            return view("admin.auth.profile", compact("user"));

        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function updateAdminProfile(Request $request)
    {
        try
        {
            $user = Auth::user();
            $data = $request->all();
            $validator = Validator::make($data,[
                "first_name" => "required",
                "last_name" => "required",
                "phone" => "required|min:9|unique:users,phone," .$user->id,
                "email" => "required|email|unique:users,email," . $user->id,
                "avatar" => "sometimes|image|mimes:jpeg,jpg,png|max:5000"
            ]);
            
            if($validator->fails()) {
                return redirect()->back()->withInput($request->all())->withErrors($validator->errors());
            }
            
            if($request->file("avatar")) {
                $file = $request->file("avatar");

                // Reject dangerous filenames and generate a random safe name
                $originalName = $file->getClientOriginalName();
                if (!preg_match('/\.(jpe?g|png)$/i', $originalName)) {
                    return redirect()->back()->withErrors(['avatar' => 'Avatar must be a JPG or PNG image.']);
                }

                $extension = $file->getClientOriginalExtension();
                $safeName = 'user_' . auth()->id() . '_' . time() . '_' . Str::random(8) . '.' . strtolower($extension);

                $folder = "uploads/user/";
                $path = public_path($folder);
                if (!File::exists($path)) {
                    File::makeDirectory($path, 0755, true, true);
                }
                $file->move($path, $safeName);
                $user->avatar = $folder . $safeName;
            }
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->full_name = $request->first_name . " " . $request->last_name;
            $user->phone = $request->phone;
            $user->email = $request->email;
            $user->save();
            return redirect()->back()->with("success", "Profile update successfully!");
        }
        catch (Exception $e) {
            return redirect()->back()->with("error", $e->getMessage());
        }
    }

    public function adminDashboard()
    {
        return view("admin.dashboard.index");
    }


}
