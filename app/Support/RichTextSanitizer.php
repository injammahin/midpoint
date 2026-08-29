<?php

namespace App\Support;

final class RichTextSanitizer
{
    /*
    |--------------------------------------------------------------------------
    | Limits
    |--------------------------------------------------------------------------
    |
    | MAX_TEXT_LENGTH is the maximum number of visible characters allowed
    | in the item description.
    |
    | MAX_HTML_LENGTH is higher because Summernote adds HTML formatting tags
    | around the visible text.
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

    public static function sanitize(string $html): string
    {
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
        | Keep Only Supported Formatting Tags
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
        | Remove Attributes; Preserve Only Safe Link URLs
        |--------------------------------------------------------------------------
        |
        | All attributes such as style, class, onclick, onerror and other event
        | handlers are removed. Only safe http, https and mailto links remain.
        |
        */

        $html = preg_replace_callback(
            '/<([a-z0-9]+)(\s[^>]*)?>/i',

            function (array $matches): string {
                $tag = strtolower($matches[1]);

                if ($tag === 'a') {
                    $attributes = $matches[2] ?? '';

                    $href = null;

                    if (
                        preg_match(
                            '/href\s*=\s*(["\'])(.*?)\1/i',
                            $attributes,
                            $hrefMatch
                        )
                    ) {
                        $candidate = trim($hrefMatch[2]);

                        if (
                            preg_match(
                                '#^(https?://|mailto:)#i',
                                $candidate
                            )
                        ) {
                            $href = $candidate;
                        }
                    }

                    if ($href !== null) {
                        return '<a href="'
                            . htmlspecialchars(
                                $href,
                                ENT_QUOTES | ENT_SUBSTITUTE,
                                'UTF-8'
                            )
                            . '" target="_blank" rel="noopener noreferrer">';
                    }

                    return '<a>';
                }

                return '<' . $tag . '>';
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
    | Summernote sends formatted HTML. This method converts that HTML into
    | visible text for validation and character counting.
    |
    */

    public static function plainText(?string $html): string
    {
        $html = (string) $html;

        /*
         * Add line breaks before removing block-level tags so words from
         * different paragraphs and list items are not joined together.
         */
        $html = preg_replace(
            '#<(br\s*/?|/p|/li|/h[2-5]|/tr)>#i',
            "\n",
            $html
        ) ?? $html;

        $text = html_entity_decode(
            strip_tags($html),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        /*
         * Convert non-breaking spaces to regular spaces.
         */
        $text = str_replace(
            "\u{00A0}",
            ' ',
            $text
        );

        /*
         * Remove invisible zero-width spaces that Summernote may insert.
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
    | Count Visible Characters
    |--------------------------------------------------------------------------
    */

    public static function textLength(?string $html): int
    {
        $text = self::plainText($html);

        if (function_exists('mb_strlen')) {
            return mb_strlen(
                $text,
                'UTF-8'
            );
        }

        return strlen($text);
    }
}