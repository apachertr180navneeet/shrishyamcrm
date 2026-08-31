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
        try{
            $request->validate([
                "email" => "required|email",
                "password" => "required",
            ]);

            // Ensure only admin-role users can authenticate.
            if (Auth::attempt([
                'email' => $request->email,
                'password' => $request->password,
                'role' => 'admin',
                'status' => 'active',
            ])) {
                return redirect()->route("admin.dashboard")->with("success", "Welcome to your dashboard.");
            }

            return back()->with("error","Invalid credentials");
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
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
