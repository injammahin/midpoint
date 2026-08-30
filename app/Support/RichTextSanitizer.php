<?php

namespace App\Support;

final class RichTextSanitizer
{
    /*
    |--------------------------------------------------------------------------
    | Limits
    |--------------------------------------------------------------------------
    |
    | Actual visible description:
    | maximum 20,000 characters.
    |
    | Summernote HTML:
    | allowed extra room for formatting tags.
    |
    */

    public const MAX_TEXT_LENGTH = 20000;

    public const MAX_HTML_LENGTH = 120000;

    public const MAX_DELIVERY_TEXT_LENGTH = 3000;

    public const MAX_DELIVERY_HTML_LENGTH = 15000;

    /*
    |--------------------------------------------------------------------------
    | Sanitize Summernote HTML
    |--------------------------------------------------------------------------
    */

    public static function sanitize(
        string $html
    ): string {
        /*
        |--------------------------------------------------------------------------
        | Remove Dangerous Elements Completely
        |--------------------------------------------------------------------------
        */

        $html = preg_replace(
            '#<(script|style|iframe|object|embed|form|input|button)[^>]*>.*?</\1>#is',
            '',
            $html
        ) ?? '';

        /*
        |--------------------------------------------------------------------------
        | Keep Supported Formatting Tags
        |--------------------------------------------------------------------------
        */

        $html = strip_tags(
            $html,
            '<p><br><strong><b><em><i><u><s>'
            . '<ul><ol><li>'
            . '<blockquote>'
            . '<h2><h3><h4><h5>'
            . '<a>'
            . '<hr>'
            . '<table><thead><tbody><tfoot><tr><th><td>'
        );

        /*
        |--------------------------------------------------------------------------
        | Remove Unsafe Attributes
        |--------------------------------------------------------------------------
        |
        | All style / class / onclick / onerror etc. attributes are removed.
        |
        | Only safe:
        |
        | http://
        | https://
        | mailto:
        |
        | links are preserved.
        |
        */

        $html = preg_replace_callback(
            '/<([a-z0-9]+)(\s[^>]*)?>/i',

            function (array $matches): string {
                $tag =
                    strtolower(
                        $matches[1]
                    );

                if ($tag === 'a') {
                    $attributes =
                        $matches[2] ?? '';

                    $href = null;

                    if (
                        preg_match(
                            '/href\s*=\s*(["\'])(.*?)\1/i',
                            $attributes,
                            $hrefMatch
                        )
                    ) {
                        $candidate =
                            trim(
                                $hrefMatch[2]
                            );

                        if (
                            preg_match(
                                '#^(https?://|mailto:)#i',
                                $candidate
                            )
                        ) {
                            $href =
                                $candidate;
                        }
                    }

                    if ($href !== null) {
                        return '<a href="'
                            . htmlspecialchars(
                                $href,
                                ENT_QUOTES
                                |
                                ENT_SUBSTITUTE,
                                'UTF-8'
                            )
                            . '" target="_blank" rel="noopener noreferrer">';
                    }

                    return '<a>';
                }

                return '<'
                    . $tag
                    . '>';
            },

            $html
        ) ?? '';

        return trim($html);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Visible Plain Text
    |--------------------------------------------------------------------------
    |
    | Summernote sends HTML.
    |
    | This converts the HTML into what the user actually sees so that
    | formatting tags do not count toward the 20,000-character limit.
    |
    */

    public static function plainText(
        ?string $html
    ): string {
        $html =
            (string) $html;

        /*
        |--------------------------------------------------------------------------
        | Preserve Logical Line Breaks
        |--------------------------------------------------------------------------
        */

        $html = preg_replace(
            '#<(br\s*/?|/p|/li|/h[2-5]|/tr)>#i',
            "\n",
            $html
        ) ?? $html;

        /*
        |--------------------------------------------------------------------------
        | Remove HTML
        |--------------------------------------------------------------------------
        */

        $text =
            html_entity_decode(
                strip_tags($html),
                ENT_QUOTES
                |
                ENT_HTML5,
                'UTF-8'
            );

        /*
        |--------------------------------------------------------------------------
        | Convert Non-breaking Spaces
        |--------------------------------------------------------------------------
        */

        $text =
            str_replace(
                "\u{00A0}",
                ' ',
                $text
            );

        /*
        |--------------------------------------------------------------------------
        | Remove Zero-width Spaces
        |--------------------------------------------------------------------------
        */

        $text = preg_replace(
            '/\x{200B}/u',
            '',
            $text
        ) ?? $text;

        return trim($text);
    }

    /*
    |--------------------------------------------------------------------------
    | Count Actual Visible Characters
    |--------------------------------------------------------------------------
    */

    public static function textLength(
        ?string $html
    ): int {
        $text =
            self::plainText(
                $html
            );

        if (
            function_exists(
                'mb_strlen'
            )
        ) {
            return mb_strlen(
                $text,
                'UTF-8'
            );
        }

        return strlen(
            $text
        );
    }
}