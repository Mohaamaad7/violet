<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class CustomerPasswordResetController extends Controller
{
    /**
     * Display the password reset form.
     */
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.customer.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Handle an incoming password reset request.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Debug: Log the request data
        \Log::info('Password Reset Attempt', [
            'email' => $request->email,
            'token' => $request->token,
            'broker' => 'customers',
        ]);

        // Check if customer exists
        $customer = Customer::where('email', $request->email)->first();
        \Log::info('Customer Lookup', [
            'found' => $customer ? true : false,
            'customer_id' => $customer?->id,
        ]);

        // Check if token exists
        $tokenRecord = \DB::table('customer_password_reset_tokens')
            ->where('email', $request->email)
            ->first();
        \Log::info('Token Lookup', [
            'found' => $tokenRecord ? true : false,
            'token_exists' => $tokenRecord ? substr($tokenRecord->token, 0, 10) . '...' : null,
        ]);

        // Reset the password
        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Customer $customer, string $password) {
                $customer->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $customer->save();

                event(new PasswordReset($customer));
            }
        );

        \Log::info('Password Reset Status', [
            'status' => $status,
            'translation' => __($status),
        ]);

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('home')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}
