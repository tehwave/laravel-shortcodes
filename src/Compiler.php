<?php

namespace tehwave\Shortcodes;

use Illuminate\Support\Collection;

class Compiler
{
    /**
     * Compile content with shortcodes.
     *
     * @param  string $content
     *
     * @return string
     */
    public static function compile(string $content, Collection $shortcodes = null): string
    {
        return ($shortcodes ?? Shortcode::all())
            ->reduce([static::class, 'parse'], $content);
    }

    /**
     * Parse content by matching up against shortcodes
     * and dispatching them to handle the input.
     *
     * @param  string    $content
     * @param  \tehwave\Shortcodes\Shortcode $shortcode
     *
     * @return string
     */
    public static function parse(string $content, $shortcode): string
    {
        $pattern = static::shortcodeRegex($shortcode->getTag());

        $parsedContent = preg_replace_callback(
            "/$pattern/",
            [$shortcode, 'dispatch'],
            $content
        );

        return $parsedContent;
    }

    /**
     * Retrieve the regular expression used to match shortcodes. Thanks Wordpress!
     *
     * @link https://developer.wordpress.org/reference/functions/get_shortcode_regex/
     *
     * @return string
     */
    private static function shortcodeRegex(string $tag): string
    {
        // phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound -- don't remove regex indentation
        return
            '\\['                                // Opening bracket
            .'(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            ."($tag)"                           // 2: Shortcode name
            .'(?![\\w-])'                       // Not followed by word character or hyphen
            .'('                                // 3: Unroll the loop: Inside the opening shortcode tag
            .'[^\\]\\/]*'                   // Not a closing bracket or forward slash
            .'(?:'
            .'\\/(?!\\])'               // A forward slash not followed by a closing bracket
            .'[^\\]\\/]*'               // Not a closing bracket or forward slash
            .')*?'
            .')'
            .'(?:'
            .'(\\/)'                        // 4: Self closing tag ...
            .'\\]'                          // ... and closing bracket
            .'|'
            .'\\]'                          // Closing bracket
            .'(?:'
            .'('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            .'[^\\[]*+'             // Not an opening bracket
            .'(?:'
            .'\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            .'[^\\[]*+'         // Not an opening bracket
            .')*+'
            .')'
            .'\\[\\/\\2\\]'             // Closing shortcode tag
            .')?'
            .')'
            .'(\\]?)';
    }

    /**
     * Resolve key-value array from string. Thanks Wordpress!
     *
     * @link https://developer.wordpress.org/reference/functions/shortcode_parse_atts/
     *
     * @param  string $attributesText
     *
     * @return array
     */
    public static function resolveAttributes($attributesText): ?array
    {
        $attributesText = preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', $attributesText);

        $attributes = collect([]);

        if (preg_match_all(static::attributeRegex(), $attributesText, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (! empty($match[1])) {
                    $attributes[strtolower($match[1])] = stripcslashes($match[2]);
                } elseif (! empty($match[3])) {
                    $attributes[strtolower($match[3])] = stripcslashes($match[4]);
                } elseif (! empty($match[5])) {
                    $attributes[strtolower($match[5])] = stripcslashes($match[6]);
                } elseif (isset($match[7]) && strlen($match[7])) {
                    $attributes[] = stripcslashes($match[7]);
                } elseif (isset($match[8]) && strlen($match[8])) {
                    $attributes[] = stripcslashes($match[8]);
                } elseif (isset($match[9])) {
                    $attributes[] = stripcslashes($match[9]);
                }
            }

            // Reject any unclosed HTML elements.
            $filteredAttributes = $attributes->filter(function ($attribute) {
                if (strpos($attribute, '<') === false) {
                    return true;
                }

                return preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $attribute);
            });

            if ($filteredAttributes->isEmpty()) {
                return null;
            }

            return $filteredAttributes->toArray();
        }

        return null;
    }

    /**
     * Retrieve the regular expression used to match attributes. Thanks Wordpress!
     *
     * @link https://developer.wordpress.org/reference/functions/get_shortcode_atts_regex/
     *
     * @return string
     */
    private static function attributeRegex(): string
    {
        return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';
    }
}
