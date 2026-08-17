<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerWithdrawal;
use App\Models\SellerWithdrawalAccount;
use App\Models\User;
use App\Services\PaystackService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class SellerWithdrawalAccountController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Verify Bank Account
    |--------------------------------------------------------------------------
    |
    | Production:
    |   Uses Paystack /bank/resolve.
    |
    | Local testing:
    |   Can simulate verification without contacting Paystack.
    |
    */

    public function resolve(
        Request $request,
        PaystackService $paystack
    ) {

        $seller =
            $request->user();


        $validated =
            $request->validate([
                'bank_code' => [
                    'required',
                    'string',
                    'max:50',
                ],

                'account_number' => [
                    'required',
                    'regex:/^[0-9]{10}$/',
                ],
            ]);


        $bankCode =
            (string)
            $validated[
                'bank_code'
            ];


        $accountNumber =
            preg_replace(
                '/\D+/',
                '',
                (string)
                $validated[
                    'account_number'
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Maximum 2 Accounts
        |--------------------------------------------------------------------------
        */

        if (
            SellerWithdrawalAccount::query()

                ->where(
                    'seller_id',
                    $seller->id
                )

                ->count()
            >=
            2
        ) {

            throw ValidationException::withMessages([
                'bank_code' =>
                    'You already have the maximum of 2 withdrawal accounts.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Local Fake Verification
        |--------------------------------------------------------------------------
        |
        | NEVER runs outside APP_ENV=local.
        |
        | This lets you test:
        |
        | Zenith Bank
        | bank code 057
        | account 0000000000
        |
        | without calling Paystack /bank/resolve.
        |
        */

        if (
            $this
                ->shouldUseLocalFakeBankVerification(
                    $bankCode,
                    $accountNumber
                )
        ) {

            $bankName =
                $this
                    ->localTestBankName(
                        $bankCode
                    );


            $accountName =
                'MIDPOINT TEST SELLER';


            /*
            |--------------------------------------------------------------------------
            | Save Local Verification In Session
            |--------------------------------------------------------------------------
            */

            $request
                ->session()
                ->put(
                    $this
                        ->verificationSessionKey(
                            $seller->id
                        ),
                    [
                        'bank_code' =>
                            $bankCode,

                        'bank_name' =>
                            $bankName,

                        'account_number_hash' =>
                            hash(
                                'sha256',
                                $accountNumber
                            ),

                        'account_name' =>
                            $accountName,

                        'verified_at' =>
                            now()->timestamp,

                        'is_local_test' =>
                            true,
                    ]
                );


            return response()->json([
                'success' =>
                    true,

                'test_mode' =>
                    true,

                'bank_name' =>
                    $bankName,

                'bank_code' =>
                    $bankCode,

                'account_number' =>
                    $accountNumber,

                'account_name' =>
                    $accountName,

                'message' =>
                    'Local test bank verification successful. No Paystack bank resolve was called.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Find Real Paystack Bank
        |--------------------------------------------------------------------------
        */

        $bank =
            $this
                ->findBank(
                    $paystack,
                    $bankCode
                );


        /*
        |--------------------------------------------------------------------------
        | Resolve Real Bank Account Through Paystack
        |--------------------------------------------------------------------------
        */

        try {

            $resolved =
                $paystack
                    ->resolveBankAccount(
                        $accountNumber,
                        $bankCode
                    );

        } catch (
            RequestException $exception
        ) {

            $response =
                $exception
                    ->response;


            $message =
                $response

                    ? data_get(
                        $response->json(),
                        'message'
                    )

                    : null;


            throw ValidationException::withMessages([
                'account_number' =>
                    $message
                    ?:
                    'Paystack could not verify this account. Please try again.',
            ]);

        } catch (
            Throwable $exception
        ) {

            throw ValidationException::withMessages([
                'account_number' =>
                    $exception
                        ->getMessage()
                    ?:
                    'Unable to verify this bank account.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Paystack Response
        |--------------------------------------------------------------------------
        */

        $accountName =
            trim(
                (string) (
                    $resolved[
                        'account_name'
                    ]
                    ??
                    ''
                )
            );


        if (
            $accountName === ''
        ) {

            throw ValidationException::withMessages([
                'account_number' =>
                    'Paystack could not resolve the account holder name.',
            ]);
        }


        $resolvedAccountNumber =
            preg_replace(
                '/\D+/',
                '',
                (string) (
                    $resolved[
                        'account_number'
                    ]
                    ??
                    $accountNumber
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Save Verification In Session
        |--------------------------------------------------------------------------
        |
        | store() will use this.
        |
        | It will NOT call /bank/resolve a second time.
        |
        */

        $request
            ->session()
            ->put(
                $this
                    ->verificationSessionKey(
                        $seller->id
                    ),
                [
                    'bank_code' =>
                        $bankCode,

                    'bank_name' =>
                        (string)
                        $bank[
                            'name'
                        ],

                    'account_number_hash' =>
                        hash(
                            'sha256',
                            $resolvedAccountNumber
                        ),

                    'account_name' =>
                        $accountName,

                    'verified_at' =>
                        now()->timestamp,

                    'is_local_test' =>
                        false,
                ]
            );


        return response()->json([
            'success' =>
                true,

            'test_mode' =>
                false,

            'bank_name' =>
                $bank[
                    'name'
                ],

            'bank_code' =>
                $bank[
                    'code'
                ],

            'account_number' =>
                $resolvedAccountNumber,

            'account_name' =>
                $accountName,
        ]);
    }



    /*
    |--------------------------------------------------------------------------
    | Store Verified Bank Account
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        PaystackService $paystack
    ) {

        $seller =
            $request->user();


        $validated =
            $request->validate([
                'bank_code' => [
                    'required',
                    'string',
                    'max:50',
                ],

                'account_number' => [
                    'required',
                    'regex:/^[0-9]{10}$/',
                ],
            ]);


        $accountNumber =
            preg_replace(
                '/\D+/',
                '',
                (string)
                $validated[
                    'account_number'
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Maximum 2 Accounts
        |--------------------------------------------------------------------------
        */

        if (
            SellerWithdrawalAccount::query()

                ->where(
                    'seller_id',
                    $seller->id
                )

                ->count()
            >=
            2
        ) {

            throw ValidationException::withMessages([
                'bank_code' =>
                    'You can save a maximum of 2 withdrawal bank accounts. Delete one before adding another.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Get Previous Verification
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | No Paystack /bank/resolve request occurs here.
        |
        */

        $verification =
            $request
                ->session()
                ->get(
                    $this
                        ->verificationSessionKey(
                            $seller->id
                        )
                );


        if (
            !$verification
        ) {

            throw ValidationException::withMessages([
                'account_number' =>
                    'Please click "Verify account" before adding this bank account.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Verification Expiry
        |--------------------------------------------------------------------------
        |
        | Verification is valid for 10 minutes.
        |
        */

        $verifiedAt =
            (int) (
                $verification[
                    'verified_at'
                ]
                ??
                0
            );


        if (
            !$verifiedAt
            ||
            now()->timestamp
            -
            $verifiedAt
            >
            600
        ) {

            $request
                ->session()
                ->forget(
                    $this
                        ->verificationSessionKey(
                            $seller->id
                        )
                );


            throw ValidationException::withMessages([
                'account_number' =>
                    'Your bank verification expired. Please verify the account again.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Ensure Bank Was Not Changed
        |--------------------------------------------------------------------------
        */

        if (
            (string) (
                $verification[
                    'bank_code'
                ]
                ??
                ''
            )
            !==
            (string)
            $validated[
                'bank_code'
            ]
        ) {

            throw ValidationException::withMessages([
                'bank_code' =>
                    'The selected bank changed after verification. Please verify the account again.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Ensure Account Number Was Not Changed
        |--------------------------------------------------------------------------
        */

        $currentAccountHash =
            hash(
                'sha256',
                $accountNumber
            );


        $verifiedAccountHash =
            (string) (
                $verification[
                    'account_number_hash'
                ]
                ??
                ''
            );


        if (
            $verifiedAccountHash === ''
            ||
            !hash_equals(
                $verifiedAccountHash,
                $currentAccountHash
            )
        ) {

            throw ValidationException::withMessages([
                'account_number' =>
                    'The account number changed after verification. Please verify it again.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Get Verified Details From Session
        |--------------------------------------------------------------------------
        */

        $bankName =
            trim(
                (string) (
                    $verification[
                        'bank_name'
                    ]
                    ??
                    ''
                )
            );


        $accountName =
            trim(
                (string) (
                    $verification[
                        'account_name'
                    ]
                    ??
                    ''
                )
            );


        $isLocalTest =
            (bool) (
                $verification[
                    'is_local_test'
                ]
                ??
                false
            );


        if (
            $bankName === ''
            ||
            $accountName === ''
        ) {

            throw ValidationException::withMessages([
                'account_number' =>
                    'The verified bank information is incomplete. Please verify again.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Never Allow Local Test Outside Local Environment
        |--------------------------------------------------------------------------
        */

        if (
            $isLocalTest
            &&
            !app()
                ->environment(
                    'local'
                )
        ) {

            $request
                ->session()
                ->forget(
                    $this
                        ->verificationSessionKey(
                            $seller->id
                        )
                );


            throw ValidationException::withMessages([
                'account_number' =>
                    'Local test bank verification cannot be used outside the local environment.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Duplicate Check
        |--------------------------------------------------------------------------
        */

        $duplicate =
            SellerWithdrawalAccount::query()

                ->where(
                    'seller_id',
                    $seller->id
                )

                ->where(
                    'bank_code',
                    $validated[
                        'bank_code'
                    ]
                )

                ->where(
                    'account_number_hash',
                    $currentAccountHash
                )

                ->exists();


        if (
            $duplicate
        ) {

            throw ValidationException::withMessages([
                'account_number' =>
                    'This bank account is already saved.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Create Transfer Recipient
        |--------------------------------------------------------------------------
        |
        | LOCAL:
        |
        | Create fake local recipient.
        | Do NOT call Paystack.
        |
        | REAL:
        |
        | Create actual Paystack transfer recipient.
        |
        */

        if (
            $isLocalTest
        ) {

            $recipientCode =
                'RCP_LOCAL_'
                .
                $seller->id
                .
                '_'
                .
                Str::upper(
                    Str::random(
                        12
                    )
                );

        } else {

            try {

                $recipient =
                    $paystack
                        ->createTransferRecipient([
                            'type' =>
                                'nuban',

                            'name' =>
                                $accountName,

                            'account_number' =>
                                $accountNumber,

                            'bank_code' =>
                                $validated[
                                    'bank_code'
                                ],

                            'currency' =>
                                'NGN',

                            'description' =>
                                'Midpoint seller withdrawal account',
                        ]);

            } catch (
                RequestException $exception
            ) {

                $response =
                    $exception
                        ->response;


                $message =
                    $response

                        ? data_get(
                            $response->json(),
                            'message'
                        )

                        : null;


                throw ValidationException::withMessages([
                    'account_number' =>
                        $message
                        ?:
                        'Paystack could not create a transfer recipient.',
                ]);

            } catch (
                Throwable $exception
            ) {

                throw ValidationException::withMessages([
                    'account_number' =>
                        $exception
                            ->getMessage()
                        ?:
                        'Unable to create the transfer recipient.',
                ]);
            }


            $recipientCode =
                trim(
                    (string) (
                        $recipient[
                            'recipient_code'
                        ]
                        ??
                        ''
                    )
                );


            if (
                $recipientCode === ''
            ) {

                throw new RuntimeException(
                    'Paystack did not return a transfer recipient code.'
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Save Bank
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $seller,
                $validated,
                $accountNumber,
                $accountName,
                $bankName,
                $recipientCode,
                $currentAccountHash
            ) {

                /*
                 * Lock seller so concurrent requests cannot
                 * bypass maximum 2 accounts.
                 */

                User::query()

                    ->whereKey(
                        $seller->id
                    )

                    ->lockForUpdate()

                    ->firstOrFail();


                $existingAccounts =
                    SellerWithdrawalAccount::query()

                        ->where(
                            'seller_id',
                            $seller->id
                        )

                        ->lockForUpdate()

                        ->get();


                if (
                    $existingAccounts
                        ->count()
                    >=
                    2
                ) {

                    throw ValidationException::withMessages([
                        'bank_code' =>
                            'You can save a maximum of 2 withdrawal bank accounts.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Recheck Duplicate While Locked
                |--------------------------------------------------------------------------
                */

                $duplicate =
                    SellerWithdrawalAccount::query()

                        ->where(
                            'seller_id',
                            $seller->id
                        )

                        ->where(
                            'bank_code',
                            $validated[
                                'bank_code'
                            ]
                        )

                        ->where(
                            'account_number_hash',
                            $currentAccountHash
                        )

                        ->exists();


                if (
                    $duplicate
                ) {

                    throw ValidationException::withMessages([
                        'account_number' =>
                            'This bank account is already saved.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | First Account Automatically Active
                |--------------------------------------------------------------------------
                */

                $makeActive =
                    $existingAccounts
                        ->count()
                    ===
                    0;


                /*
                |--------------------------------------------------------------------------
                | Save Verified Bank
                |--------------------------------------------------------------------------
                */

                SellerWithdrawalAccount::create([
                    'seller_id' =>
                        $seller->id,

                    'bank_name' =>
                        $bankName,

                    'bank_code' =>
                        $validated[
                            'bank_code'
                        ],

                    'account_name' =>
                        $accountName,

                    'account_number' =>
                        $accountNumber,

                    'paystack_recipient_code' =>
                        $recipientCode,

                    'is_verified' =>
                        true,

                    'is_active' =>
                        $makeActive,

                    'verified_at' =>
                        now(),
                ]);

            },
            3
        );


        /*
        |--------------------------------------------------------------------------
        | Remove Used Verification
        |--------------------------------------------------------------------------
        */

        $request
            ->session()
            ->forget(
                $this
                    ->verificationSessionKey(
                        $seller->id
                    )
            );


        return redirect()

            ->route(
                'seller.wallet'
            )

            ->with(
                'success',

                $isLocalTest

                    ? 'Local test bank account verified and added successfully. No Paystack bank resolve or transfer-recipient API was called.'

                    : 'Bank account verified and added successfully. Verified bank details cannot be edited.'
            );
    }



    /*
    |--------------------------------------------------------------------------
    | Activate Bank
    |--------------------------------------------------------------------------
    */

    public function activate(
        Request $request,
        SellerWithdrawalAccount $withdrawalAccount
    ) {

        $this
            ->authorizeAccount(
                $request,
                $withdrawalAccount
            );


        if (
            !$withdrawalAccount
                ->is_verified
        ) {

            return back()
                ->with(
                    'error',
                    'Only a verified bank account can be activated.'
                );
        }


        DB::transaction(
            function () use (
                $request,
                $withdrawalAccount
            ) {

                /*
                |--------------------------------------------------------------------------
                | Disable Active State For All Seller Banks
                |--------------------------------------------------------------------------
                */

                SellerWithdrawalAccount::query()

                    ->where(
                        'seller_id',
                        $request
                            ->user()
                            ->id
                    )

                    ->lockForUpdate()

                    ->update([
                        'is_active' =>
                            false,
                    ]);


                /*
                |--------------------------------------------------------------------------
                | Make Selected Bank Active
                |--------------------------------------------------------------------------
                */

                SellerWithdrawalAccount::query()

                    ->whereKey(
                        $withdrawalAccount
                            ->id
                    )

                    ->update([
                        'is_active' =>
                            true,
                    ]);

            },
            3
        );


        return back()
            ->with(
                'success',
                'Active withdrawal account changed successfully.'
            );
    }



    /*
    |--------------------------------------------------------------------------
    | Delete Bank
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        SellerWithdrawalAccount $withdrawalAccount
    ) {

        $this
            ->authorizeAccount(
                $request,
                $withdrawalAccount
            );


        /*
        |--------------------------------------------------------------------------
        | Don't Delete Bank Used By Pending Withdrawal
        |--------------------------------------------------------------------------
        */

        $hasPendingWithdrawal =
            SellerWithdrawal::query()

                ->where(
                    'seller_id',
                    $request
                        ->user()
                        ->id
                )

                ->where(
                    'seller_withdrawal_account_id',
                    $withdrawalAccount
                        ->id
                )

                ->whereIn(
                    'status',
                    [
                        SellerWithdrawal::STATUS_PENDING,
                        SellerWithdrawal::STATUS_PROCESSING,
                        SellerWithdrawal::STATUS_OTP,
                    ]
                )

                ->exists();


        if (
            $hasPendingWithdrawal
        ) {

            return back()
                ->with(
                    'error',
                    'This bank account cannot be deleted while a withdrawal to it is still pending.'
                );
        }


        DB::transaction(
            function () use (
                $request,
                $withdrawalAccount
            ) {

                $wasActive =
                    (bool)
                    $withdrawalAccount
                        ->is_active;


                /*
                |--------------------------------------------------------------------------
                | Delete Account
                |--------------------------------------------------------------------------
                */

                SellerWithdrawalAccount::query()

                    ->whereKey(
                        $withdrawalAccount
                            ->id
                    )

                    ->lockForUpdate()

                    ->firstOrFail()

                    ->delete();


                /*
                |--------------------------------------------------------------------------
                | Activate Remaining Account Automatically
                |--------------------------------------------------------------------------
                */

                if (
                    $wasActive
                ) {

                    $replacement =
                        SellerWithdrawalAccount::query()

                            ->where(
                                'seller_id',
                                $request
                                    ->user()
                                    ->id
                            )

                            ->where(
                                'is_verified',
                                true
                            )

                            ->latest(
                                'id'
                            )

                            ->lockForUpdate()

                            ->first();


                    if (
                        $replacement
                    ) {

                        $replacement
                            ->update([
                                'is_active' =>
                                    true,
                            ]);
                    }
                }

            },
            3
        );


        return back()
            ->with(
                'success',
                'Withdrawal bank account deleted successfully.'
            );
    }



    /*
    |--------------------------------------------------------------------------
    | Authorize Account
    |--------------------------------------------------------------------------
    */

    protected function authorizeAccount(
        Request $request,
        SellerWithdrawalAccount $account
    ): void {

        abort_unless(
            (int)
            $account
                ->seller_id
            ===
            (int)
            $request
                ->user()
                ->id,
            403
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Find Real Bank
    |--------------------------------------------------------------------------
    */

    protected function findBank(
        PaystackService $paystack,
        string $bankCode
    ): array {

        /*
         * Paystack test bank may not always appear
         * in the regular Nigerian bank list.
         */

        if (
            $this
                ->isPaystackTestMode()
            &&
            $bankCode
            ===
            '001'
        ) {

            return [
                'name' =>
                    'Paystack Test Bank',

                'code' =>
                    '001',
            ];
        }


        $bank =
            collect(
                $paystack
                    ->listBanks(
                        'nigeria'
                    )
            )
                ->first(
                    function (
                        $item
                    ) use (
                        $bankCode
                    ) {

                        return
                            (string) (
                                $item[
                                    'code'
                                ]
                                ??
                                ''
                            )
                            ===
                            (string)
                            $bankCode;
                    }
                );


        if (
            !$bank
        ) {

            throw ValidationException::withMessages([
                'bank_code' =>
                    'The selected bank is not supported by Paystack.',
            ]);
        }


        return $bank;
    }



    /*
    |--------------------------------------------------------------------------
    | Decide Whether To Fake Verification
    |--------------------------------------------------------------------------
    |
    | Rule 1:
    | NEVER fake outside local.
    |
    | Rule 2:
    | If PAYSTACK_FAKE_BANK_VERIFICATION=true,
    | fake any local verification.
    |
    | Rule 3:
    | Even if config is missing, allow:
    |
    | account 0000000000
    | bank 001 or 057
    |
    | while using sk_test_.
    |
    */

    protected function shouldUseLocalFakeBankVerification(
        string $bankCode,
        string $accountNumber
    ): bool {

        /*
        |--------------------------------------------------------------------------
        | Never Fake Production
        |--------------------------------------------------------------------------
        */

        if (
            !app()
                ->environment(
                    'local'
                )
        ) {

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Explicit Fake Mode
        |--------------------------------------------------------------------------
        */

        if (
            (bool)
            config(
                'services.paystack.fake_bank_verification',
                false
            )
        ) {

            return true;
        }


        /*
        |--------------------------------------------------------------------------
        | Automatic Local Test Account Fallback
        |--------------------------------------------------------------------------
        |
        | This solves your current 429 issue even if the config flag
        | has not been added yet.
        |
        */

        return
            $this
                ->isPaystackTestMode()

            &&

            $accountNumber
            ===
            '0000000000'

            &&

            in_array(
                $bankCode,
                [
                    '001',
                    '057',
                ],
                true
            );
    }



    /*
    |--------------------------------------------------------------------------
    | Local Test Bank Names
    |--------------------------------------------------------------------------
    */

    protected function localTestBankName(
        string $bankCode
    ): string {

        $banks = [

            '001' =>
                'Paystack Test Bank',

            '057' =>
                'Zenith Bank',

            '058' =>
                'Guaranty Trust Bank',

            '044' =>
                'Access Bank',

            '011' =>
                'First Bank of Nigeria',

            '033' =>
                'United Bank For Africa',

            '032' =>
                'Union Bank of Nigeria',

            '070' =>
                'Fidelity Bank',

            '232' =>
                'Sterling Bank',
        ];


        return
            $banks[
                $bankCode
            ]
            ??
            'Nigerian Test Bank';
    }



    /*
    |--------------------------------------------------------------------------
    | Paystack Test Mode
    |--------------------------------------------------------------------------
    */

    protected function isPaystackTestMode(): bool
    {

        return Str::startsWith(
            (string)
            config(
                'services.paystack.secret_key'
            ),
            'sk_test_'
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Verification Session Key
    |--------------------------------------------------------------------------
    */

    protected function verificationSessionKey(
        int $sellerId
    ): string {

        return
            'seller_withdrawal_bank_verification.'
            .
            $sellerId;
    }
}