<?php
$shortcode_tags = array();
$typecho_archive = null;
global $typecho_archive;
global $shortcode_tags;

function add_shortcode( $tag, $callback ) {
    global $shortcode_tags;

    if ( '' === trim( $tag ) ) {
        $message = __( 'Invalid shortcode name: Empty name given.' );
        echo $message;
        return;
    }

    if ( 0 !== preg_match( '@[<>&/\[\]\x00-\x20=]@', $tag ) ) {
        /* translators: 1: Shortcode name, 2: Space-separated list of reserved characters. */
        $message = sprintf( __( 'Invalid shortcode name: %1$s. Do not use spaces or reserved characters: %2$s' ), $tag, '& / < > [ ] =' );
        echo $message;
        return;
    }

    $shortcode_tags[ $tag ] = $callback;
}

function do_shortcode( $content, $ignore_html = false ,$obj = null) {
    global $shortcode_tags;
    if ( false === strpos( $content, '[' ) ) {
        return $content;
    }

    if ( empty( $shortcode_tags ) || ! is_array( $shortcode_tags ) ) {
        return $content;
    }

    // Find all registered tag names in $content.
    preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );
    $tagnames = array_intersect( array_keys( $shortcode_tags ), $matches[1] );
    if ( empty( $tagnames ) ) {
        return $content;
    }

    $content = do_shortcodes_in_html_tags( $content, $ignore_html, $tagnames );

    $pattern = get_shortcode_regex( $tagnames );
    $content = preg_replace_callback( "/$pattern/", 'do_shortcode_tag', $content);

    // Always restore square braces so we don't break things like <!--[if IE ]>.
    $content = unescape_invalid_shortcodes( $content );

    return $content;
}

function get_shortcode_regex( $tagnames = null ) {
    global $shortcode_tags;

    if ( empty( $tagnames ) ) {
        $tagnames = array_keys( $shortcode_tags );
    }
    $tagregexp = implode( '|', array_map( 'preg_quote', $tagnames ) );

    // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag().
    // Also, see shortcode_unautop() and shortcode.js.

    // phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound -- don't remove regex indentation
    return '\\['                             // Opening bracket.
        . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]].
        . "($tagregexp)"                     // 2: Shortcode name.
        . '(?![\\w-])'                       // Not followed by word character or hyphen.
        . '('                                // 3: Unroll the loop: Inside the opening shortcode tag.
        .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash.
        .     '(?:'
        .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket.
        .         '[^\\]\\/]*'               // Not a closing bracket or forward slash.
        .     ')*?'
        . ')'
        . '(?:'
        .     '(\\/)'                        // 4: Self closing tag...
        .     '\\]'                          // ...and closing bracket.
        . '|'
        .     '\\]'                          // Closing bracket.
        .     '(?:'
        .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags.
        .             '[^\\[]*+'             // Not an opening bracket.
        .             '(?:'
        .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag.
        .                 '[^\\[]*+'         // Not an opening bracket.
        .             ')*+'
        .         ')'
        .         '\\[\\/\\2\\]'             // Closing shortcode tag.
        .     ')?'
        . ')'
        . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]].
    // phpcs:enable
}

function unescape_invalid_shortcodes( $content ) {
    // Clean up entire string, avoids re-parsing HTML.
    $trans = array(
        '&#91;' => '[',
        '&#93;' => ']',
    );

    $content = strtr( $content, $trans );

    return $content;
}

/**
 * Retrieve the regular expression for an HTML element.
 *
 * @since 4.4.0
 *
 * @return string The regular expression
 */
function get_html_split_regex() {
    static $regex;

    if ( ! isset( $regex ) ) {
        // phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound -- don't remove regex indentation
        $comments =
            '!'             // Start of comment, after the <.
            . '(?:'         // Unroll the loop: Consume everything until --> is found.
            .     '-(?!->)' // Dash not followed by end of comment.
            .     '[^\-]*+' // Consume non-dashes.
            . ')*+'         // Loop possessively.
            . '(?:-->)?';   // End of comment. If not found, match all input.

        $cdata =
            '!\[CDATA\['    // Start of comment, after the <.
            . '[^\]]*+'     // Consume non-].
            . '(?:'         // Unroll the loop: Consume everything until ]]> is found.
            .     '](?!]>)' // One ] not followed by end of comment.
            .     '[^\]]*+' // Consume non-].
            . ')*+'         // Loop possessively.
            . '(?:]]>)?';   // End of comment. If not found, match all input.

        $escaped =
            '(?='             // Is the element escaped?
            .    '!--'
            . '|'
            .    '!\[CDATA\['
            . ')'
            . '(?(?=!-)'      // If yes, which type?
            .     $comments
            . '|'
            .     $cdata
            . ')';

        $regex =
            '/('                // Capture the entire match.
            .     '<'           // Find start of element.
            .     '(?'          // Conditional expression follows.
            .         $escaped  // Find end of escaped element.
            .     '|'           // ...else...
            .         '[^>]*>?' // Find end of normal element.
            .     ')'
            . ')/';
        // phpcs:enable
    }

    return $regex;
}


function wp_html_split( $input ) {
    return preg_split( get_html_split_regex(), $input, -1, PREG_SPLIT_DELIM_CAPTURE );
}

function wp_kses_hair_parse( $attr ) {
    if ( '' === $attr ) {
        return array();
    }

    // phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound -- don't remove regex indentation
    $regex =
        '(?:'
        .     '[_a-zA-Z][-_a-zA-Z0-9:.]*' // Attribute name.
        . '|'
        .     '\[\[?[^\[\]]+\]\]?'        // Shortcode in the name position implies unfiltered_html.
        . ')'
        . '(?:'               // Attribute value.
        .     '\s*=\s*'       // All values begin with '='.
        .     '(?:'
        .         '"[^"]*"'   // Double-quoted.
        .     '|'
        .         "'[^']*'"   // Single-quoted.
        .     '|'
        .         '[^\s"\']+' // Non-quoted.
        .         '(?:\s|$)'  // Must have a space.
        .     ')'
        . '|'
        .     '(?:\s|$)'      // If attribute has no value, space is required.
        . ')'
        . '\s*';              // Trailing space is optional except as mentioned above.
    // phpcs:enable

    // Although it is possible to reduce this procedure to a single regexp,
    // we must run that regexp twice to get exactly the expected result.

    $validation = "%^($regex)+$%";
    $extraction = "%$regex%";

    if ( 1 === preg_match( $validation, $attr ) ) {
        preg_match_all( $extraction, $attr, $attrarr );
        return $attrarr[0];
    } else {
        return false;
    }
}


function wp_kses_attr_parse( $element ) {
    $valid = preg_match( '%^(<\s*)(/\s*)?([a-zA-Z0-9]+\s*)([^>]*)(>?)$%', $element, $matches );
    if ( 1 !== $valid ) {
        return false;
    }

    $begin  = $matches[1];
    $slash  = $matches[2];
    $elname = $matches[3];
    $attr   = $matches[4];
    $end    = $matches[5];

    if ( '' !== $slash ) {
        // Closing elements do not get parsed.
        return false;
    }

    // Is there a closing XHTML slash at the end of the attributes?
    if ( 1 === preg_match( '%\s*/\s*$%', $attr, $matches ) ) {
        $xhtml_slash = $matches[0];
        $attr        = substr( $attr, 0, -strlen( $xhtml_slash ) );
    } else {
        $xhtml_slash = '';
    }

    // Split it.
    $attrarr = wp_kses_hair_parse( $attr );
    if ( false === $attrarr ) {
        return false;
    }

    // Make sure all input is returned by adding front and back matter.
    array_unshift( $attrarr, $begin . $slash . $elname );
    array_push( $attrarr, $xhtml_slash . $end );

    return $attrarr;
}

function do_shortcodes_in_html_tags( $content, $ignore_html, $tagnames ) {
    // Normalize entities in unfiltered HTML before adding placeholders.
    $trans   = array(
        '&#91;' => '&#091;',
        '&#93;' => '&#093;',
    );
    $content = strtr( $content, $trans );
    $trans   = array(
        '[' => '&#91;',
        ']' => '&#93;',
    );

    $pattern = get_shortcode_regex( $tagnames );
    $textarr = wp_html_split( $content );

    foreach ( $textarr as &$element ) {
        if ( '' === $element || '<' !== $element[0] ) {
            continue;
        }

        $noopen  = false === strpos( $element, '[' );
        $noclose = false === strpos( $element, ']' );
        if ( $noopen || $noclose ) {
            // This element does not contain shortcodes.
            if ( $noopen xor $noclose ) {
                // Need to encode stray '[' or ']' chars.
                $element = strtr( $element, $trans );
            }
            continue;
        }

        if ( $ignore_html || '<!--' === substr( $element, 0, 4 ) || '<![CDATA[' === substr( $element, 0, 9 ) ) {
            // Encode all '[' and ']' chars.
            $element = strtr( $element, $trans );
            continue;
        }

        $attributes = wp_kses_attr_parse( $element );
        if ( false === $attributes ) {
            // Some plugins are doing things like [name] <[email]>.
            if ( 1 === preg_match( '%^<\s*\[\[?[^\[\]]+\]%', $element ) ) {
                $element = preg_replace_callback( "/$pattern/", 'do_shortcode_tag', $element );
            }

            // Looks like we found some crazy unfiltered HTML. Skipping it for sanity.
            $element = strtr( $element, $trans );
            continue;
        }

        // Get element name.
        $front   = array_shift( $attributes );
        $back    = array_pop( $attributes );
        $matches = array();
        preg_match( '%[a-zA-Z0-9]+%', $front, $matches );
        $elname = $matches[0];

        // Look for shortcodes in each attribute separately.
        foreach ( $attributes as &$attr ) {
            $open  = strpos( $attr, '[' );
            $close = strpos( $attr, ']' );
            if ( false === $open || false === $close ) {
                continue; // Go to next attribute. Square braces will be escaped at end of loop.
            }
            $double = strpos( $attr, '"' );
            $single = strpos( $attr, "'" );
            if ( ( false === $single || $open < $single ) && ( false === $double || $open < $double ) ) {
                /*
                 * $attr like '[shortcode]' or 'name = [shortcode]' implies unfiltered_html.
                 * In this specific situation we assume KSES did not run because the input
                 * was written by an administrator, so we should avoid changing the output
                 * and we do not need to run KSES here.
                 */
                $attr = preg_replace_callback( "/$pattern/", 'do_shortcode_tag', $attr );
            } else {
                // $attr like 'name = "[shortcode]"' or "name = '[shortcode]'".
                // We do not know if $content was unfiltered. Assume KSES ran before shortcodes.
                $count    = 0;
                $new_attr = preg_replace_callback( "/$pattern/", 'do_shortcode_tag', $attr, -1, $count );
                if ( $count > 0 ) {
                    // Sanitize the shortcode output using KSES.
                    $new_attr = wp_kses_one_attr( $new_attr, $elname );
                    if ( '' !== trim( $new_attr ) ) {
                        // The shortcode is safe to use now.
                        $attr = $new_attr;
                    }
                }
            }
        }
        $element = $front . implode( '', $attributes ) . $back;

        // Now encode any remaining '[' or ']' chars.
        $element = strtr( $element, $trans );
    }

    $content = implode( '', $textarr );

    return $content;
}

function wp_kses_uri_attributes() {
    $uri_attributes = array(
        'action',
        'archive',
        'background',
        'cite',
        'classid',
        'codebase',
        'data',
        'formaction',
        'href',
        'icon',
        'longdesc',
        'manifest',
        'poster',
        'profile',
        'src',
        'usemap',
        'xmlns',
    );


    return $uri_attributes;
}

function wp_kses_no_null( $string, $options = null ) {
    if ( ! isset( $options['slash_zero'] ) ) {
        $options = array( 'slash_zero' => 'remove' );
    }

    $string = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $string );
    if ( 'remove' === $options['slash_zero'] ) {
        $string = preg_replace( '/\\\\+0+/', '', $string );
    }

    return $string;
}

function wp_kses_one_attr( $string, $element ) {
    $allowed_html      = array(
        'action'         => true,
        'accept'         => true,
        'accept-charset' => true,
        'enctype'        => true,
        'method'         => true,
        'name'           => true,
        'target'         => true,
    );
    $string            = wp_kses_no_null( $string, array( 'slash_zero' => 'keep' ) );

    // Preserve leading and trailing whitespace.
    $matches = array();
    preg_match( '/^\s*/', $string, $matches );
    $lead = $matches[0];
    preg_match( '/\s*$/', $string, $matches );
    $trail = $matches[0];
    if ( empty( $trail ) ) {
        $string = substr( $string, strlen( $lead ) );
    } else {
        $string = substr( $string, strlen( $lead ), -strlen( $trail ) );
    }

    // Parse attribute name and value from input.
    $split = preg_split( '/\s*=\s*/', $string, 2 );
    $name  = $split[0];
    if ( count( $split ) == 2 ) {
        $value = $split[1];

        // Remove quotes surrounding $value.
        // Also guarantee correct quoting in $string for this one attribute.
        if ( '' === $value ) {
            $quote = '';
        } else {
            $quote = $value[0];
        }
        if ( '"' === $quote || "'" === $quote ) {
            if ( substr( $value, -1 ) != $quote ) {
                return '';
            }
            $value = substr( $value, 1, -1 );
        } else {
            $quote = '"';
        }

        // Sanitize quotes, angle braces, and entities.
        $value = esc_attr( $value );

//        // Sanitize URI values.
//        if ( in_array( strtolower( $name ), $uris, true ) ) {
//            $value = wp_kses_bad_protocol( $value, $allowed_protocols );
//        }

        $string = "$name=$quote$value$quote";
        $vless  = 'n';
    } else {
        $value = '';
        $vless = 'y';
    }

    // Sanitize attribute by name.
    wp_kses_attr_check( $name, $value, $string, $vless, $element, $allowed_html );

    // Restore whitespace.
    return $lead . $string . $trail;
}

function wp_check_invalid_utf8( $string, $strip = false ) {
    $string = (string) $string;

    if ( 0 === strlen( $string ) ) {
        return '';
    }

    // Store the site charset as a static to avoid multiple calls to get_option().
    static $is_utf8 = null;
    if ( ! isset( $is_utf8 ) ) {
        $is_utf8 = in_array( 'utf8', array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ), true );
    }
    if ( ! $is_utf8 ) {
        return $string;
    }

    // Check for support for utf8 in the installed PCRE library once and store the result in a static.
    static $utf8_pcre = null;
    if ( ! isset( $utf8_pcre ) ) {
        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
        $utf8_pcre = @preg_match( '/^./u', 'a' );
    }
    // We can't demand utf8 in the PCRE installation, so just return the string in those cases.
    if ( ! $utf8_pcre ) {
        return $string;
    }

    // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- preg_match fails when it encounters invalid UTF8 in $string.
    if ( 1 === @preg_match( '/^./us', $string ) ) {
        return $string;
    }

    // Attempt to strip the bad chars if requested (not recommended).
    if ( $strip && function_exists( 'iconv' ) ) {
        return iconv( 'utf-8', 'utf-8', $string );
    }

    return '';
}
function _wp_specialchars( $string, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false ) {
    $string = (string) $string;

    if ( 0 === strlen( $string ) ) {
        return '';
    }

    // Don't bother if there are no specialchars - saves some processing.
    if ( ! preg_match( '/[&<>"\']/', $string ) ) {
        return $string;
    }

    // Account for the previous behaviour of the function when the $quote_style is not an accepted value.
    if ( empty( $quote_style ) ) {
        $quote_style = ENT_NOQUOTES;
    } elseif ( ENT_XML1 === $quote_style ) {
        $quote_style = ENT_QUOTES | ENT_XML1;
    } elseif ( ! in_array( $quote_style, array( ENT_NOQUOTES, ENT_COMPAT, ENT_QUOTES, 'single', 'double' ), true ) ) {
        $quote_style = ENT_QUOTES;
    }

    // Store the site charset as a static to avoid multiple calls to wp_load_alloptions().
    if ( ! $charset ) {
        $charset = 'UTF-8';

    }


    $_quote_style = $quote_style;

    if ( 'double' === $quote_style ) {
        $quote_style  = ENT_COMPAT;
        $_quote_style = ENT_COMPAT;
    } elseif ( 'single' === $quote_style ) {
        $quote_style = ENT_NOQUOTES;
    }

    $string = htmlspecialchars( $string, $quote_style, $charset, $double_encode );

    // Back-compat.
    if ( 'single' === $_quote_style ) {
        $string = str_replace( "'", '&#039;', $string );
    }

    return $string;
}

function esc_attr( $text ) {
    $safe_text = wp_check_invalid_utf8( $text );
    $safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );
    /**
     * Filters a string cleaned and escaped for output in an HTML attribute.
     *
     * Text passed to esc_attr() is stripped of invalid or special characters
     * before output.
     *
     * @since 2.0.6
     *
     * @param string $safe_text The text after it has been escaped.
     * @param string $text      The text prior to being escaped.
     */
    return $safe_text;
}

function wp_kses_attr_check( &$name, &$value, &$whole, $vless, $element, $allowed_html ) {
    $name_low    = strtolower( $name );
    $element_low = strtolower( $element );

    if ( ! isset( $allowed_html[ $element_low ] ) ) {
        $name  = '';
        $value = '';
        $whole = '';
        return false;
    }

    $allowed_attr = $allowed_html[ $element_low ];

    if ( ! isset( $allowed_attr[ $name_low ] ) || '' === $allowed_attr[ $name_low ] ) {
        /*
         * Allow `data-*` attributes.
         *
         * When specifying `$allowed_html`, the attribute name should be set as
         * `data-*` (not to be mixed with the HTML 4.0 `data` attribute, see
         * https://www.w3.org/TR/html40/struct/objects.html#adef-data).
         *
         * Note: the attribute name should only contain `A-Za-z0-9_-` chars,
         * double hyphens `--` are not accepted by WordPress.
         */
        if ( strpos( $name_low, 'data-' ) === 0 && ! empty( $allowed_attr['data-*'] ) && preg_match( '/^data(?:-[a-z0-9_]+)+$/', $name_low, $match ) ) {
            /*
             * Add the whole attribute name to the allowed attributes and set any restrictions
             * for the `data-*` attribute values for the current element.
             */
            $allowed_attr[ $match[0] ] = $allowed_attr['data-*'];
        } else {
            $name  = '';
            $value = '';
            $whole = '';
            return false;
        }
    }

    if ( 'style' === $name_low ) {
//        $new_value = safecss_filter_attr( $value );
        $new_value = $value;
        if ( empty( $new_value ) ) {
            $name  = '';
            $value = '';
            $whole = '';
            return false;
        }

        $whole = str_replace( $value, $new_value, $whole );
        $value = $new_value;
    }

    if ( is_array( $allowed_attr[ $name_low ] ) ) {
        // There are some checks.
        foreach ( $allowed_attr[ $name_low ] as $currkey => $currval ) {
            if ( ! wp_kses_check_attr_val( $value, $vless, $currkey, $currval ) ) {
                $name  = '';
                $value = '';
                $whole = '';
                return false;
            }
        }
    }

    return true;
}

function wp_kses_check_attr_val( $value, $vless, $checkname, $checkvalue ) {
    $ok = true;

    switch ( strtolower( $checkname ) ) {
        case 'maxlen':
            /*
             * The maxlen check makes sure that the attribute value has a length not
             * greater than the given value. This can be used to avoid Buffer Overflows
             * in WWW clients and various Internet servers.
             */

            if ( strlen( $value ) > $checkvalue ) {
                $ok = false;
            }
            break;

        case 'minlen':
            /*
             * The minlen check makes sure that the attribute value has a length not
             * smaller than the given value.
             */

            if ( strlen( $value ) < $checkvalue ) {
                $ok = false;
            }
            break;

        case 'maxval':
            /*
             * The maxval check does two things: it checks that the attribute value is
             * an integer from 0 and up, without an excessive amount of zeroes or
             * whitespace (to avoid Buffer Overflows). It also checks that the attribute
             * value is not greater than the given value.
             * This check can be used to avoid Denial of Service attacks.
             */

            if ( ! preg_match( '/^\s{0,6}[0-9]{1,6}\s{0,6}$/', $value ) ) {
                $ok = false;
            }
            if ( $value > $checkvalue ) {
                $ok = false;
            }
            break;

        case 'minval':
            /*
             * The minval check makes sure that the attribute value is a positive integer,
             * and that it is not smaller than the given value.
             */

            if ( ! preg_match( '/^\s{0,6}[0-9]{1,6}\s{0,6}$/', $value ) ) {
                $ok = false;
            }
            if ( $value < $checkvalue ) {
                $ok = false;
            }
            break;

        case 'valueless':
            /*
             * The valueless check makes sure if the attribute has a value
             * (like `<a href="blah">`) or not (`<option selected>`). If the given value
             * is a "y" or a "Y", the attribute must not have a value.
             * If the given value is an "n" or an "N", the attribute must have a value.
             */

            if ( strtolower( $checkvalue ) != $vless ) {
                $ok = false;
            }
            break;
    } // End switch.

    return $ok;
}

function get_shortcode_atts_regex() {
    return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';
}

function shortcode_parse_atts( $text ) {
    $atts    = array();
    $pattern = get_shortcode_atts_regex();
    $text    = preg_replace( "/[\x{00a0}\x{200b}]+/u", ' ', $text );
    if ( preg_match_all( $pattern, $text, $match, PREG_SET_ORDER ) ) {
        foreach ( $match as $m ) {
            if ( ! empty( $m[1] ) ) {
                $atts[ strtolower( $m[1] ) ] = stripcslashes( $m[2] );
            } elseif ( ! empty( $m[3] ) ) {
                $atts[ strtolower( $m[3] ) ] = stripcslashes( $m[4] );
            } elseif ( ! empty( $m[5] ) ) {
                $atts[ strtolower( $m[5] ) ] = stripcslashes( $m[6] );
            } elseif ( isset( $m[7] ) && strlen( $m[7] ) ) {
                $atts[] = stripcslashes( $m[7] );
            } elseif ( isset( $m[8] ) && strlen( $m[8] ) ) {
                $atts[] = stripcslashes( $m[8] );
            } elseif ( isset( $m[9] ) ) {
                $atts[] = stripcslashes( $m[9] );
            }
        }

        // Reject any unclosed HTML elements.
        foreach ( $atts as &$value ) {
            if ( false !== strpos( $value, '<' ) ) {
                if ( 1 !== preg_match( '/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value ) ) {
                    $value = '';
                }
            }
        }
    } else {
        $atts = ltrim( $text );
    }

    return $atts;
}

function do_shortcode_tag( $m ) {
    global $shortcode_tags;
    global $typecho_archive;

    // Allow [[foo]] syntax for escaping a tag.
    if ( '[' === $m[1] && ']' === $m[6] ) {
        return substr( $m[0], 1, -1 );
    }

    $tag  = $m[2];
    $attr = shortcode_parse_atts( $m[3] );

    if ( ! is_callable( $shortcode_tags[ $tag ] ) ) {
        /* translators: %s: Shortcode tag. */
        $message = sprintf( __( 'Attempting to parse a shortcode without a valid callback: %s' ), $tag );
        echo $message;
        return $m[0];
    }



    $content = isset( $m[5] ) ? $m[5] : null;
    $output = $m[1] . call_user_func( $shortcode_tags[ $tag ], $attr, $content, $tag ) . $m[6];

    return $output;
}
