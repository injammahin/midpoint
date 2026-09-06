<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\SecureTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuyerSellerInviteController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Seller Invite Page
    |--------------------------------------------------------------------------
    */

    public function create(
        Request $request
    ): View {

        $request
            ->session()
            ->put(
                'account_view',
                'buyer'
            );


        return view(
            'buyer.seller-invite'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Open Pasted Seller Invite
    |--------------------------------------------------------------------------
    */

    public function open(
        Request $request
    ): RedirectResponse {

        $validated =
            $request->validate(
                [
                    'invite_link' => [
                        'required',
                        'string',
                        'max:2048',
                    ],
                ],
                [
                    'invite_link.required' =>
                        'Paste the secure seller invite link first.',

                    'invite_link.max' =>
                        'The seller invite link is too long.',
                ]
            );


        $inviteLink =
            trim(
                $validated['invite_link']
            );


        $publicToken =
            $this->extractPublicToken(
                $request,
                $inviteLink
            );


        if (
            !$publicToken
        ) {

            return back()
                ->withErrors([
                    'invite_link' =>
                        'This does not look like a valid Midpoint seller invite. Paste the complete secure link shared by the seller.',
                ])
                ->withInput();
        }


        $transaction =
            SecureTransaction::query()

                ->where(
                    'public_token',
                    $publicToken
                )

                ->first();


        if (
            !$transaction
        ) {

            return back()
                ->withErrors([
                    'invite_link' =>
                        'We could not find a transaction for this seller invite. Check the link and try again.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Marketplace Checkout Is Not A Seller Invite
        |--------------------------------------------------------------------------
        */

        if (
            $transaction->isMarketplaceCheckout()
        ) {

            return back()
                ->withErrors([
                    'invite_link' =>
                        'This transaction was created through the marketplace and is not a seller invite link.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Open Existing Secure Transaction
        |--------------------------------------------------------------------------
        |
        | SecureTransactionController will still validate:
        |
        | - verified email
        | - buyer email match
        | - transaction expiry
        | - buyer claim
        | - account status
        |
        */

        return redirect()
            ->route(
                'secure-transactions.show',
                $transaction
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Extract Public Token
    |--------------------------------------------------------------------------
    */

    private function extractPublicToken(
        Request $request,
        string $value
    ): ?string {

        $value =
            trim(
                $value
            );


        if (
            $value === ''
        ) {

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Raw Token Support
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/^[A-Za-z0-9]{64}$/',
                $value
            )
        ) {

            return $value;
        }


        /*
        |--------------------------------------------------------------------------
        | Parse URL
        |--------------------------------------------------------------------------
        */

        $parts =
            parse_url(
                $value
            );


        if (
            $parts === false
        ) {

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Only HTTP / HTTPS
        |--------------------------------------------------------------------------
        */

        if (
            isset(
                $parts['scheme']
            )
            &&
            !in_array(
                strtolower(
                    $parts['scheme']
                ),
                [
                    'http',
                    'https',
                ],
                true
            )
        ) {

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Protect Against External Redirects
        |--------------------------------------------------------------------------
        */

        if (
            !empty(
                $parts['host']
            )
        ) {

            $pastedHost =
                $this->normalizeHost(
                    (string)
                    $parts['host']
                );


            $requestHost =
                $this->normalizeHost(
                    $request->getHost()
                );


            $configuredHost =
                $this->normalizeHost(
                    (string)
                    parse_url(
                        (string)
                        config(
                            'app.url'
                        ),
                        PHP_URL_HOST
                    )
                );


            $allowedHosts =
                array_values(
                    array_unique(
                        array_filter([
                            $requestHost,
                            $configuredHost,
                        ])
                    )
                );


            if (
                !in_array(
                    $pastedHost,
                    $allowedHosts,
                    true
                )
            ) {

                return null;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Expected Midpoint Transaction URL
        |--------------------------------------------------------------------------
        |
        | /transaction/{64-character-public-token}
        |
        */

        $path =
            (string)
            (
                $parts['path']
                ??
                ''
            );


        if (
            !preg_match(
                '#^/transaction/([A-Za-z0-9]{64})/?$#',
                $path,
                $matches
            )
        ) {

            return null;
        }


        return $matches[1];
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Domain
    |--------------------------------------------------------------------------
    */

    private function normalizeHost(
        string $host
    ): string {

        $host =
            strtolower(
                trim(
                    $host
                )
            );


        return preg_replace(
            '/^www\./i',
            '',
            $host
        )
        ??
        $host;
    }
}