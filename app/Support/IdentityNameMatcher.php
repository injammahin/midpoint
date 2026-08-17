<?php

namespace App\Support;

use Illuminate\Support\Str;

class IdentityNameMatcher
{
    /*
    |--------------------------------------------------------------------------
    | Match Two Human Names
    |--------------------------------------------------------------------------
    |
    | Handles:
    |
    | JOHN DOE MUSA
    | MUSA JOHN DOE
    | JOHN MUSA
    |
    | without requiring the exact same word order.
    |
    */

    public static function matches(
        ?string $first,
        ?string $second,
        ?float $threshold = null
    ): bool {

        $threshold =
            $threshold
            ??
            (float)
            config(
                'services.dojah.name_match_threshold',
                0.80
            );


        $firstTokens =
            static::tokens(
                $first
            );


        $secondTokens =
            static::tokens(
                $second
            );


        if (
            empty($firstTokens)
            ||
            empty($secondTokens)
        ) {

            return false;
        }


        /*
         * If either side only has one usable name,
         * require exact normalized equality.
         */
        if (
            count($firstTokens) < 2
            ||
            count($secondTokens) < 2
        ) {

            return
                implode(
                    ' ',
                    $firstTokens
                )
                ===
                implode(
                    ' ',
                    $secondTokens
                );
        }


        $matched =
            array_intersect(
                $firstTokens,
                $secondTokens
            );


        /*
         * Require at least first + last style matching.
         */
        if (
            count($matched) < 2
        ) {

            return false;
        }


        $denominator =
            min(
                count($firstTokens),
                count($secondTokens)
            );


        $score =
            count(
                $matched
            )
            /
            $denominator;


        return
            $score
            >=
            $threshold;
    }


    /*
    |--------------------------------------------------------------------------
    | Match Score
    |--------------------------------------------------------------------------
    */

    public static function score(
        ?string $first,
        ?string $second
    ): float {

        $firstTokens =
            static::tokens(
                $first
            );


        $secondTokens =
            static::tokens(
                $second
            );


        if (
            empty($firstTokens)
            ||
            empty($secondTokens)
        ) {

            return 0;
        }


        $matched =
            array_intersect(
                $firstTokens,
                $secondTokens
            );


        return round(
            count($matched)
            /
            min(
                count($firstTokens),
                count($secondTokens)
            ),
            4
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Name
    |--------------------------------------------------------------------------
    */

    public static function normalize(
        ?string $name
    ): string {

        return implode(
            ' ',
            static::tokens(
                $name
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Tokenize
    |--------------------------------------------------------------------------
    */

    protected static function tokens(
        ?string $name
    ): array {

        if (
            !$name
        ) {

            return [];
        }


        $name =
            Str::ascii(
                Str::upper(
                    trim(
                        $name
                    )
                )
            );


        $name =
            preg_replace(
                '/[^A-Z\s]/',
                ' ',
                $name
            );


        $parts =
            preg_split(
                '/\s+/',
                trim(
                    $name
                )
            )
            ?:
            [];


        $ignored = [
            'MR',
            'MRS',
            'MISS',
            'MS',
            'DR',
            'CHIEF',
            'ALHAJI',
            'ALHAJA',
            'PROF',
        ];


        $parts =
            array_filter(
                $parts,
                function (
                    $part
                ) use (
                    $ignored
                ) {

                    return
                        strlen(
                            $part
                        )
                        >=
                        2

                        &&

                        !in_array(
                            $part,
                            $ignored,
                            true
                        );
                }
            );


        return array_values(
            array_unique(
                $parts
            )
        );
    }
}