<?php

namespace App\Traits;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;

trait SendsPasswordResetEmails
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );;

        return $response == Password::RESET_LINK_SENT
                    ? $this->sendResetLinkResponse($request, $response)
                    : $this->sendResetLinkFailedResponse($request, $response);
        // switch ($response) {
        //     case Password::RESET_LINK_SENT:
        //         return $this->sendResetLinkResponse($request, $response);

        //     case Password::INVALID_USER:
        //     default:
        //         return $this->sendResetLinkFailedResponse($request, $response);
        // }
    }


    protected function validateEmail(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);
    }


    protected function credentials(Request $request)
    {
        return $request->only('email');
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        
        return response()->json(['status'=>trans($response)]);
    }


    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return response()->json(['status'=>trans($response)]);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }
}