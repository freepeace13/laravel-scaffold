<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAcessToken;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($result = $this->attemptLogin($request)) {
            if (is_bool($result)) {
                return $this->sendLoginResponse($request);
            }

            return $this->responseJson([
                'accessToken' => $result->plainTextToken
            ], 200);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        if ($this->isSanctum()) {
            return $this->handleSanctumLogout();
        }

        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $request->wantsJson()
            ? $this->responseJson([], 204)
            : redirect('/');
    }

    /**
     * Attempt to login user to the sanctum driver
     *
     * @param  Illuminate\Http\Request $request
     * @return mixed
     */
    protected function handleSanctumLogin(Request $request)
    {
        $user = User::firstWhere(
            $this->username(), $request->get($this->username())
        );

        if ($result = Hash::check($request->password, optional($user)->password)) {
            return $user->createToken($request->header('User-Agent'));
        }

        return $result;
    }

    /**
     * Revoke current user access token.
     *
     * @return \Illuminate\Http\Response
     */
    protected function handleSanctumLogout()
    {
        $user = $this->guard()->user();
        $user->currentAccessToken()->delete();

        return $this->responseJson([], 204);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        if ($this->isSanctum()) {
            return $this->handleSanctumLogin($request);
        }

        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    /**
     * Determine the current guard is sanctum
     *
     * @return boolean
     */
    protected function isSanctum()
    {
        return Auth::getDefaultDriver() === config('auth.guards.sanctum.driver');
    }
}
