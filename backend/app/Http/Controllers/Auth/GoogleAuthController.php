<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        if (!config('services.google.client_id') || !config('services.google.client_secret')) {
            return $this->redirectToFrontend([
                'oauth_error' => 'Google login is not configured on the server.',
            ]);
        }

        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        if ($request->filled('error')) {
            $message = $request->input('error_description') ?: $request->input('error');
            return $this->redirectToFrontend([
                'oauth_error' => "Google denied access: {$message}",
            ]);
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            if (!$googleUser->getEmail()) {
                return $this->redirectToFrontend([
                    'oauth_error' => 'Google account did not provide an email address.',
                ]);
            }

            $user = User::query()
                ->where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName() ?: '',
                    'email' => $googleUser->getEmail(),
                    'phone' => '',
                    'password' => Hash::make(Str::random(40)),
                    'role' => 'patient',
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ]);
            }

            $token = $user->createToken('default')->plainTextToken;
            $profileRequired = $this->isProfileRequired($user);

            return $this->redirectToFrontend([
                'token' => $token,
                'role' => $user->role,
                'profile_required' => $profileRequired ? 1 : 0,
            ]);
        } catch (\Throwable $e) {
            Log::error('Google OAuth callback failed', [
                'message' => $e->getMessage(),
                'type' => get_class($e),
                'query' => $request->query(),
            ]);

            return $this->redirectToFrontend([
                'oauth_error' => 'Google authentication failed. Check server log for details.',
            ]);
        }
    }

    private function redirectToFrontend(array $params)
    {
        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://127.0.0.1:3000'), '/');
        $query = http_build_query($params);

        return redirect()->away("{$frontendUrl}/login?{$query}");
    }

    private function isProfileRequired(User $user): bool
    {
        $name = trim((string) $user->name);
        $phone = trim((string) $user->phone);

        return $name === '' || $phone === '';
    }
}
