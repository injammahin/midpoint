<?php

namespace App\Services;

use RuntimeException;

class EnvironmentFileService
{
    /**
     * Update only selected .env keys.
     *
     * Existing environment variables, API keys, database
     * passwords and other secrets remain untouched.
     */
    public function set(array $values): void
    {
        $envPath =
            base_path('.env');


        if (
            !is_file(
                $envPath
            )
        ) {
            throw new RuntimeException(
                'The .env file could not be found.'
            );
        }


        if (
            !is_readable(
                $envPath
            )
            ||
            !is_writable(
                $envPath
            )
        ) {
            throw new RuntimeException(
                'The .env file is not readable/writable by PHP.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Safety Backup
        |--------------------------------------------------------------------------
        |
        | This backup is stored in the Laravel project root,
        | not inside /public.
        |
        */

        @copy(
            $envPath,
            base_path(
                '.env.backup'
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Open + Lock
        |--------------------------------------------------------------------------
        */

        $handle =
            fopen(
                $envPath,
                'c+'
            );


        if (
            $handle === false
        ) {
            throw new RuntimeException(
                'The .env file could not be opened for writing.'
            );
        }


        try {

            if (
                !flock(
                    $handle,
                    LOCK_EX
                )
            ) {
                throw new RuntimeException(
                    'The .env file could not be locked for writing.'
                );
            }


            rewind(
                $handle
            );


            $content =
                stream_get_contents(
                    $handle
                );


            if (
                $content === false
            ) {
                throw new RuntimeException(
                    'The .env file could not be read.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Preserve Current Line Ending
            |--------------------------------------------------------------------------
            */

            $lineEnding =
                str_contains(
                    $content,
                    "\r\n"
                )
                    ? "\r\n"
                    : "\n";


            /*
            |--------------------------------------------------------------------------
            | Update Values
            |--------------------------------------------------------------------------
            */

            foreach (
                $values
                as
                $key => $value
            ) {

                /*
                |--------------------------------------------------------------------------
                | Environment Variable Key Safety
                |--------------------------------------------------------------------------
                */

                if (
                    !preg_match(
                        '/^[A-Z0-9_]+$/',
                        (string) $key
                    )
                ) {
                    throw new RuntimeException(
                        'Invalid environment variable key: '
                        .$key
                    );
                }


                $line =
                    $key
                    .'='
                    .$this->encodeValue(
                        $value
                    );


                /*
                |--------------------------------------------------------------------------
                | Find Existing Key
                |--------------------------------------------------------------------------
                */

                $pattern =
                    '/^[ \t]*'
                    .preg_quote(
                        (string) $key,
                        '/'
                    )
                    .'[ \t]*=.*$/m';


                if (
                    preg_match(
                        $pattern,
                        $content
                    )
                ) {

                    $content =
                        preg_replace_callback(

                            $pattern,

                            static fn () =>
                                $line,

                            $content,

                            1

                        );

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Add Missing Key
                    |--------------------------------------------------------------------------
                    */

                    $content =
                        rtrim(
                            $content,
                            "\r\n"
                        )
                        .$lineEnding
                        .$line
                        .$lineEnding;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Rewrite .env
            |--------------------------------------------------------------------------
            */

            rewind(
                $handle
            );


            if (
                !ftruncate(
                    $handle,
                    0
                )
            ) {
                throw new RuntimeException(
                    'The .env file could not be prepared for writing.'
                );
            }


            $written =
                fwrite(
                    $handle,
                    $content
                );


            if (
                $written === false
                ||
                $written < strlen(
                    $content
                )
            ) {
                throw new RuntimeException(
                    'The .env file could not be fully written.'
                );
            }


            fflush(
                $handle
            );

        } finally {

            @flock(
                $handle,
                LOCK_UN
            );


            fclose(
                $handle
            );
        }
    }


    /**
     * Encode values for Laravel dotenv.
     */
    private function encodeValue(
        mixed $value
    ): string {

        if (
            $value === null
            ||
            $value === ''
        ) {
            return '""';
        }


        if (
            is_bool(
                $value
            )
        ) {
            return $value
                ? 'true'
                : 'false';
        }


        $value =
            (string) $value;


        /*
        |--------------------------------------------------------------------------
        | Simple Value
        |--------------------------------------------------------------------------
        |
        | Numbers and paths such as:
        |
        | 5
        | 7.5
        | /uploads/app/logo.png
        |
        | do not need quotes.
        |
        */

        if (
            preg_match(
                '/^[A-Za-z0-9_.\-\/]+$/',
                $value
            )
        ) {
            return $value;
        }


        /*
        |--------------------------------------------------------------------------
        | Quoted Value
        |--------------------------------------------------------------------------
        */

        $escaped =
            str_replace(

                [
                    '\\',
                    '"',
                ],

                [
                    '\\\\',
                    '\\"',
                ],

                $value

            );


        return
            '"'
            .$escaped
            .'"';
    }
}