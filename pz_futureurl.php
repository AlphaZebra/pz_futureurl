<?php

/*
Plugin Name: FutureURL
Plugin URI: http://www.peakzebra.com
Description: Allows you to create links that don't appear until after a date or after target exists.
Author: Robert Richardson / PeakZebra
Version: 0.0.1
Author URI: http://www.peakzebra.com
License:           GPL v2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
*/
namespace FutureURL;

add_filter( 'the_content', 'handle_future_urls', 1 );
 

/**
 * This function is called as the main content of a page is being prepared for display. If the
 * content contains hyperlinks in a special format, each of these links will be evaluated to see 
 * if the current date is later than a 'golive' date embedded in the link. For each special link, 
 * if it's currently after the golive date, the link is rewritten as a standard html link. 
 * If it's currently before the golive date, the link is stripped down to just the anchor text
 * (in other words, there's no link there, just the text). 
 * 
 * This enables you to place links in a post that you don't want to go live immediately. You 
 * may have a content plan that calls for multiple posts, for example, and this way you can 
 * publish posts that are early in the series and have them link to planned posts, meaning you
 * don't have to go back and add links later. 
 * 
 * The format for this specialized link is:
 * 
 * <-thelink.com/whatever|July 1, 2022->the anchor text<->
 * 
 * There are no spaces in the format. The date can be given in any format that the PHP 
 * strtotime() function can read. 
 */
function handle_future_urls( $content ) {

    // Check if we're inside the main loop in a single Post.
    if ( is_singular() && in_the_loop() && is_main_query() ) {
        while ( $full_string = pz_get_full_link_string( $content ) ) {
            $url = pz_get_link( $full_string );
            if( pz_time_ok( $full_string ) ) {
                // ok to stick in live url
                $content = pz_do_url_write( $full_string, $url, $content );
            } else {
                // just remove future link markup from content
                $content = pz_do_url_write( $full_string, "", $content );
            }
        } // end while
    // we can loop back to look for next one because the one we just handled has changed
    // text and won't be found anymore. 
    }
    return $content;
}

// true if we're now past the golive date, otherwise false
function pz_time_ok( $str ) {
    // a quirk of strtok is that we could just call it with an empty string as
    // a parameter and it would return the remainder of the string, because it stays
    // 'set up' even though we originally called it in another function. On the
    // off chance that this quirk is ever 'corrected', we're "restarting" strtok
    // and just discarding the first part of the string it returns. Chalk it up to
    // defensive programming. 
    $date_str = strtok( $str, "-|"); // returns link, which we ignore.
    $date_str = strtok( "" ); // returns remainder of string, which is the date
    $d = strtotime( $date_str ); // $d will be false if date format not readable
    $right_now = time();
    if ( !$d ) return false;
    else return  $d <= $right_now;
}

// find the first current instance of a special-format link tag and extract it
function pz_get_full_link_string( $content ) {

    $tmpstr = "";

    if ( $pos = stripos( $content, "&lt;-" ) ) {
        // get the full future link string
        $pos2 = stripos( $content, "->", $pos );
        
        $innerpos = $pos + 5;  // skip over first angle bracket
        // sanity check ... 
        if ( $pos2 < 0 ) {
            return "";
        }
        $tmpstr = substr( $content, $innerpos, $pos2-$innerpos ); 
    }
    return $tmpstr;
}

// extract the link from the link|date string
function pz_get_link( $str ) {

    // get the basic url part of the string, then the date
    $start_linkstr = strtok( $str, "-|" );

    if( !stripos( $start_linkstr, "http://" ) ) {
        $linkstr = "http://" . $start_linkstr;
    } else {
        $linkstr = $start_linkstr;
    }
    return $linkstr;

}


// either write the real html link tag or remove everything but the anchor text
function pz_do_url_write( $full_string, $url, $content ) {
    if( $url ) {
        $replacer = "<a href=" . $url . ">";
    } else { 
        $replacer = "";
    }

    $phrase = "&lt;-" . $full_string . "->"; // original special tag to look for
    $content = str_replace_first( $phrase, $replacer, $content );
    // now delete end marker or change to proper html tag close
    $content = str_replace_first( "&lt;->", $replacer ? "</a>" : "", $content );
    return $content;
}


