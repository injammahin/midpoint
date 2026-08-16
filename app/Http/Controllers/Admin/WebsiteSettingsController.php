<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EnvironmentFileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class WebsiteSettingsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | App Settings
    |--------------------------------------------------------------------------
    */

    public function appSettings()
    {
        /*
        |--------------------------------------------------------------------------
        | Current Effective Settings
        |--------------------------------------------------------------------------
        */

        $settings = [

            'logo_path' =>

                config(
                    'midpoint.logo_path'
                ),


            'service_fee_percent' =>

                (float) config(

                    'secure_transactions.service_fee_percent',

                    5

                ),


            'fee_vat_percent' =>

                (float) config(

                    'secure_transactions.fee_vat_percent',

                    7.5

                ),


            'inspection_hours' =>

                (int) config(

                    'secure_transactions.inspection_hours',

                    8

                ),


            'delivery_auto_complete_hours' =>

                (int) config(

                    'secure_transactions.delivery_auto_complete_hours',

                    72

                ),

        ];


        /*
        |--------------------------------------------------------------------------
        | Current Logo
        |--------------------------------------------------------------------------
        */

        $logoUrl =
            null;


        $logoExists =
            false;


        if (
            !empty(
                $settings['logo_path']
            )
        ) {

            $logoRelativePath =

                ltrim(

                    (string)
                    $settings['logo_path'],

                    '/'

                );


            $logoExists =

                File::exists(

                    public_path(
                        $logoRelativePath
                    )

                );


            if (
                $logoExists
            ) {

                $logoUrl =
                    asset(
                        $logoRelativePath
                    );
            }
        }


        return view(

            'admin.website-settings.app-settings',

            compact(

                'settings',

                'logoUrl',

                'logoExists'

            )

        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update App Settings
    |--------------------------------------------------------------------------
    */

    public function updateAppSettings(

        Request $request,

        EnvironmentFileService $environment

    ) {

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =

            $request->validate([


                /*
                |--------------------------------------------------------------------------
                | Logo
                |--------------------------------------------------------------------------
                */

                'logo' => [

                    'nullable',

                    'file',

                    'mimes:png,jpg,jpeg,webp',

                    'max:3072',

                ],


                /*
                |--------------------------------------------------------------------------
                | Service Fee
                |--------------------------------------------------------------------------
                */

                'service_fee_percent' => [

                    'required',

                    'numeric',

                    'min:0',

                    'max:100',

                ],


                /*
                |--------------------------------------------------------------------------
                | VAT
                |--------------------------------------------------------------------------
                */

                'fee_vat_percent' => [

                    'required',

                    'numeric',

                    'min:0',

                    'max:100',

                ],


                /*
                |--------------------------------------------------------------------------
                | Inspection Hours
                |--------------------------------------------------------------------------
                */

                'inspection_hours' => [

                    'required',

                    'integer',

                    'min:1',

                    'max:168',

                ],


                /*
                |--------------------------------------------------------------------------
                | Delivery Auto Complete
                |--------------------------------------------------------------------------
                */

                'delivery_auto_complete_hours' => [

                    'required',

                    'integer',

                    'min:1',

                    'max:720',

                ],

            ]);


        /*
        |--------------------------------------------------------------------------
        | Logo State
        |--------------------------------------------------------------------------
        */

        $oldLogoPath =
            config(
                'midpoint.logo_path'
            );


        $newLogoPath =
            null;


        $newLogoAbsolutePath =
            null;


        try {

            /*
            |--------------------------------------------------------------------------
            | Upload New Logo
            |--------------------------------------------------------------------------
            */

            if (
                $request->hasFile(
                    'logo'
                )
            ) {

                $directory =

                    public_path(
                        'uploads/app'
                    );


                /*
                |--------------------------------------------------------------------------
                | Ensure Directory
                |--------------------------------------------------------------------------
                */

                File::ensureDirectoryExists(

                    $directory,

                    0755,

                    true

                );


                if (
                    !is_writable(
                        $directory
                    )
                ) {
                    throw new \RuntimeException(
                        'The public/uploads/app directory is not writable.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Uploaded File
                |--------------------------------------------------------------------------
                */

                $file =

                    $request->file(
                        'logo'
                    );


                /*
                |--------------------------------------------------------------------------
                | Extension
                |--------------------------------------------------------------------------
                */

                $extension =

                    strtolower(

                        $file->extension()

                        ?:

                        $file
                            ->getClientOriginalExtension()

                    );


                /*
                |--------------------------------------------------------------------------
                | Unique Filename
                |--------------------------------------------------------------------------
                */

                $filename =

                    'midpoint-logo-'

                    .now()
                        ->format(
                            'YmdHis'
                        )

                    .'-'

                    .Str::lower(
                        Str::random(
                            6
                        )
                    )

                    .'.'

                    .$extension;


                /*
                |--------------------------------------------------------------------------
                | Save Logo
                |--------------------------------------------------------------------------
                */

                $file->move(

                    $directory,

                    $filename

                );


                /*
                |--------------------------------------------------------------------------
                | Public Logo Path
                |--------------------------------------------------------------------------
                */

                $newLogoPath =

                    '/uploads/app/'

                    .$filename;


                $newLogoAbsolutePath =

                    $directory

                    .DIRECTORY_SEPARATOR

                    .$filename;
            }


            /*
            |--------------------------------------------------------------------------
            | Environment Values
            |--------------------------------------------------------------------------
            */

            $envValues = [

                'MIDPOINT_SERVICE_FEE_PERCENT' =>

                    $this->decimalString(

                        $validated[
                            'service_fee_percent'
                        ]

                    ),


                'MIDPOINT_FEE_VAT_PERCENT' =>

                    $this->decimalString(

                        $validated[
                            'fee_vat_percent'
                        ]

                    ),


                'MIDPOINT_INSPECTION_HOURS' =>

                    (string)
                    (
                        (int)
                        $validated[
                            'inspection_hours'
                        ]
                    ),


                'MIDPOINT_DELIVERY_AUTO_COMPLETE_HOURS' =>

                    (string)
                    (
                        (int)
                        $validated[
                            'delivery_auto_complete_hours'
                        ]
                    ),

            ];


            /*
            |--------------------------------------------------------------------------
            | Logo Environment Value
            |--------------------------------------------------------------------------
            */

            if (
                $newLogoPath !== null
            ) {

                $envValues[
                    'MIDPOINT_APP_LOGO_PATH'
                ] =

                    $newLogoPath;
            }


            /*
            |--------------------------------------------------------------------------
            | Write .env
            |--------------------------------------------------------------------------
            */

            $environment->set(
                $envValues
            );


            /*
            |--------------------------------------------------------------------------
            | Delete Previous Managed Logo
            |--------------------------------------------------------------------------
            |
            | Do this only AFTER .env save succeeds.
            |
            */

            if (

                $newLogoPath !== null

                &&

                !empty(
                    $oldLogoPath
                )

                &&

                $oldLogoPath
                !==
                $newLogoPath

            ) {

                $this->deleteManagedLogo(

                    (string)
                    $oldLogoPath

                );
            }

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Remove New File If Save Failed
            |--------------------------------------------------------------------------
            */

            if (

                $newLogoAbsolutePath

                &&

                File::exists(
                    $newLogoAbsolutePath
                )

            ) {

                File::delete(
                    $newLogoAbsolutePath
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Log
            |--------------------------------------------------------------------------
            */

            Log::error(

                'Unable to update MidPoint app settings.',

                [

                    'message' =>

                        $exception
                            ->getMessage(),

                ]

            );


            /*
            |--------------------------------------------------------------------------
            | Return Error
            |--------------------------------------------------------------------------
            */

            return back()

                ->withInput()

                ->withErrors([

                    'settings' =>

                        'App settings could not be saved. '

                        .$exception
                            ->getMessage(),

                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Refresh Configuration
        |--------------------------------------------------------------------------
        */

        $cacheWarning =

            $this
                ->refreshConfigurationCache();


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        $response =

            redirect()

                ->route(
                    'admin.website-settings.app-settings'
                )

                ->with(

                    'success',

                    'App settings saved successfully. The new values are now stored in .env.'

                );


        if (
            $cacheWarning
        ) {

            $response->with(

                'warning',

                $cacheWarning

            );
        }


        return $response;
    }


    /*
    |--------------------------------------------------------------------------
    | Remove Logo
    |--------------------------------------------------------------------------
    */

    public function destroyLogo(

        EnvironmentFileService $environment

    ) {

        $oldLogoPath =

            config(
                'midpoint.logo_path'
            );


        try {

            /*
            |--------------------------------------------------------------------------
            | Remove Environment Setting
            |--------------------------------------------------------------------------
            */

            $environment->set([

                'MIDPOINT_APP_LOGO_PATH' =>

                    '',

            ]);


            /*
            |--------------------------------------------------------------------------
            | Delete Managed File
            |--------------------------------------------------------------------------
            */

            if (
                !empty(
                    $oldLogoPath
                )
            ) {

                $this->deleteManagedLogo(

                    (string)
                    $oldLogoPath

                );
            }

        } catch (
            Throwable $exception
        ) {

            Log::error(

                'Unable to remove MidPoint app logo.',

                [

                    'message' =>
                        $exception
                            ->getMessage(),

                ]

            );


            return back()

                ->withErrors([

                    'logo' =>

                        'The logo could not be removed. '

                        .$exception
                            ->getMessage(),

                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Refresh Configuration
        |--------------------------------------------------------------------------
        */

        $cacheWarning =

            $this
                ->refreshConfigurationCache();


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        $response =

            redirect()

                ->route(
                    'admin.website-settings.app-settings'
                )

                ->with(

                    'success',

                    'Custom logo removed. MidPoint is using the default text logo again.'

                );


        if (
            $cacheWarning
        ) {

            $response->with(

                'warning',

                $cacheWarning

            );
        }


        return $response;
    }


    /*
    |--------------------------------------------------------------------------
    | FAQ Settings
    |--------------------------------------------------------------------------
    */

    public function faqs()
    {
        return view(
            'admin.website-settings.faqs'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Pricing Settings
    |--------------------------------------------------------------------------
    */

    public function pricing()
    {
        return view(
            'admin.website-settings.pricing'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Become Seller
    |--------------------------------------------------------------------------
    */

    public function becomeSeller()
    {
        return view(
            'admin.website-settings.become-seller'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Decimal For Environment File
    |--------------------------------------------------------------------------
    |
    | 5.0000 becomes 5
    | 7.5000 becomes 7.5
    |
    */

    private function decimalString(
        mixed $value
    ): string {

        $formatted =

            number_format(

                (float)
                $value,

                4,

                '.',

                ''

            );


        $result =

            rtrim(

                rtrim(

                    $formatted,

                    '0'

                ),

                '.'

            );


        return

            $result === ''

                ? '0'

                : $result;
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Managed Logo
    |--------------------------------------------------------------------------
    |
    | Security:
    | Only files underneath /uploads/app/ can be deleted.
    |
    */

    private function deleteManagedLogo(

        string $logoPath

    ): void {

        $normalized =

            '/'

            .ltrim(

                str_replace(

                    '\\',

                    '/',

                    $logoPath

                ),

                '/'

            );


        /*
        |--------------------------------------------------------------------------
        | Prevent Arbitrary File Deletion
        |--------------------------------------------------------------------------
        */

        if (

            !Str::startsWith(

                $normalized,

                '/uploads/app/'

            )

        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Absolute Path
        |--------------------------------------------------------------------------
        */

        $absolutePath =

            public_path(

                ltrim(
                    $normalized,
                    '/'
                )

            );


        /*
        |--------------------------------------------------------------------------
        | Delete
        |--------------------------------------------------------------------------
        */

        if (
            File::exists(
                $absolutePath
            )
        ) {

            File::delete(
                $absolutePath
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Refresh Config Cache
    |--------------------------------------------------------------------------
    */

    private function refreshConfigurationCache(): ?string
    {
        /*
        |--------------------------------------------------------------------------
        | Was Config Cached?
        |--------------------------------------------------------------------------
        */

        $wasCached =

            app()
                ->configurationIsCached();


        try {

            /*
            |--------------------------------------------------------------------------
            | Remove Old Cache
            |--------------------------------------------------------------------------
            */

            Artisan::call(
                'config:clear'
            );


            /*
            |--------------------------------------------------------------------------
            | Restore Cache If Project Used It
            |--------------------------------------------------------------------------
            */

            if (
                $wasCached
            ) {

                Artisan::call(
                    'config:cache'
                );
            }


            return null;

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Log Warning
            |--------------------------------------------------------------------------
            */

            Log::warning(

                'App settings were written but configuration cache refresh failed.',

                [

                    'message' =>

                        $exception
                            ->getMessage(),

                ]

            );


            /*
            |--------------------------------------------------------------------------
            | Try To At Least Clear Stale Cache
            |--------------------------------------------------------------------------
            */

            try {

                Artisan::call(
                    'config:clear'
                );

            } catch (
                Throwable $clearException
            ) {

                Log::warning(

                    'Configuration cache could not be cleared after app settings update.',

                    [

                        'message' =>

                            $clearException
                                ->getMessage(),

                    ]

                );
            }


            return

                'The values were saved to .env, but Laravel could not fully refresh the configuration cache. Run php artisan optimize:clear once from the project root.';
        }
    }
}