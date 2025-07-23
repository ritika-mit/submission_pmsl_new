<?php

namespace App\Http\Controllers;

use App\Enums\Section;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\Password\ResetRequest;
use App\Http\Requests\Auth\Password\SendResetLinkRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Author;
use App\Models\Country;
use App\Models\GuestAuthor;
use App\Models\ResearchArea;
use App\Models\Role;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('auth/login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->safe()->only('username', 'password');
        $credentials['email'] = $credentials['username'];
        unset($credentials['username']);

        if ($this->guard()->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect($request->session()->get('url.intended', RouteServiceProvider::HOME));
        }

        return back()->withErrors(['username' => trans('auth.failed')]);
    }

    public function showRegister(?GuestAuthor $author)
    {
        $author?->with('country');

        $countries = Country::query()->active()->get();
        $research_areas = ResearchArea::query()->active()->get();

        return Inertia::render('auth/register', compact('author', 'research_areas', 'countries'));
    }

    public function register(RegisterRequest $request)
    {
        $author = new Author;

        $inputs = $request->safe()->except('research_areas');
        $inputs['password'] = Hash::make($inputs['password']);

        $author->fill($inputs);
        $author->privacy_policy_accepted = $request->boolean('privacy_policy');
        $author->subscribed_for_notifications = $request->boolean('subscribe_to_notifications');
        $author->accept_review_request = $request->boolean('accept_review_request');
        $author->country()->associate($inputs['country']);
        $author->save();

        $author->researchAreas()->sync(
            $request->input('research_areas', [])
        );

        $author->roles()->sync(
            Role::query()->default()->get()
        );

        event(new Registered($author));

        return to_route('auth.index')->with(
            'success',
            __('Congratulations, your account has been successfully created. Before proceeding, please check your email for a verification link.')
        );
    }

    public function notice()
    {
        return Inertia::render('auth/notice');
    }

    public function verify(Request $request, Author $author, string $hash)
    {
        if (!hash_equals((string) $hash, sha1($author->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        if ($author->hasVerifiedEmail()) {
            return to_route('index');
        }

        if ($author->markEmailAsVerified()) {
            event(new Verified($author));
        }

        return to_route(Auth::user() ? 'index' : 'auth.index')
            ->with(
                'success',
                __('Email verified successfully.')
            );
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return to_route('index');
        }

        $request->user()
            ->sendEmailVerificationNotification();

        return back()
            ->with(
                'success',
                __('A fresh verification link has been sent to your email address.')
            );
    }

    public function showLinkRequest()
    {
        return Inertia::render('auth/email');
    }

    public function sendResetLink(SendResetLinkRequest $request)
    {
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        if ($response == Password::RESET_LINK_SENT) {
            return back()->with('success', trans($response));
        }

        return back()->withErrors(['email' => trans($response)]);
    }

    public function showReset(Request $request, string $token)
    {
        $email = $request->input('email');

        return Inertia::render('auth/reset', compact('email'));
    }

    public function reset(ResetRequest $request, string $token)
    {
        $credentials = array_merge($request->only('email', 'password', 'password_confirmation'), compact('token'));

        $response = $this->broker()->reset($credentials, function ($user, $password) {
            $user->password = Hash::make($password);
            $user->setRememberToken(Str::random(60));
            $user->save();
            event(new PasswordReset($user));
        });

        if ($response == Password::PASSWORD_RESET) {
            return to_route('auth.index')->with('success', trans($response));
        }

        return back()->withErrors(['email' => trans($response)]);
    }

    public function switch(Request $request, Section $section)
    {
        $request->user()->update(compact('section'));

        return redirect(RouteServiceProvider::HOME);
    }

    public function logout(LogoutRequest $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return to_route('auth.index');
    }

    protected function guard($name = null): Guard | StatefulGuard
    {
        return Auth::guard($name);
    }

    protected function broker()
    {
        return Password::broker();
    }
}
