<?php
/**
 * CF7 Email Domain Filter
 *
 * @author      Per Soderlind
 * @copyright   2018 Per Soderlind
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: CF7 Email Domain Filter
 * Plugin URI: https://github.com/soderlind/cf7-email-domain-filter
 * GitHub Plugin URI: https://github.com/soderlind/cf7-email-domain-filter
 * Description: With the CF7 Email Domain Filter you can limit the which email domains you want a response from.
 * Version:     0.0.2
 * Author:      Per Soderlind
 * Author URI:  https://soderlind.no
 * Text Domain: cf7-email-domain-filter
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * Credit: Awesomplete (https://leaverou.github.io/awesomplete) has an MIT licese and is Copyright (c) 2015 Lea Verou.
 */

namespace Soderlind\CF7\Email\Filter;

define( 'CF7_EMAIL_DOMAIN_FILTER_VERSION', '0.0.1' );

$email_filter_domains = [
	'aol.com',
	'att.net',
	'comcast.net',
	'facebook.com',
	'gmail.com',
	'gmx.com',
	'googlemail.com',
	'google.com',
	'hotmail.com',
	'hotmail.co.uk',
	'mac.com',
	'me.com',
	'mail.com',
	'msn.com',
	'live.com',
	'sbcglobal.net',
	'verizon.net',
	'yahoo.com',
	'yahoo.co.uk',
];

add_action( 'wpcf7_enqueue_scripts', __NAMESPACE__ . '\on_wpcf7_enqueue_scripts' );
add_filter( 'wpcf7_validate_email*', __NAMESPACE__ . '\email_domain_validation_filter', 20, 2 );
add_filter( 'wpcf7_validate_email', __NAMESPACE__ . '\email_domain_validation_filter', 20, 2 );

function on_wpcf7_enqueue_scripts() {
	global $email_filter_domains;
	wp_enqueue_style( 'awesomplete-css', 'https://cdnjs.cloudflare.com/ajax/libs/awesomplete/1.1.2/awesomplete.min.css', [], CF7_EMAIL_DOMAIN_FILTER_VERSION );

	wp_enqueue_script( 'awesomplete-js', '//cdnjs.cloudflare.com/ajax/libs/awesomplete/1.1.2/awesomplete.min.js', [], CF7_EMAIL_DOMAIN_FILTER_VERSION, true );
	wp_localize_script( 'awesomplete-js', 'oEmailFilterDomains', $email_filter_domains );

	$script = <<<EOSCRIPT
	new Awesomplete('input.domainfilter', {
		list: oEmailFilterDomains,
		data: function (text, input) {
			return input.slice(0, input.indexOf("@")) + "@" + text;
		},
		filter: Awesomplete.FILTER_STARTSWITH,
		sort: false,
		maxItems: 20
	});
EOSCRIPT;
	wp_add_inline_script( 'awesomplete-js', $script );
}


/**
 * Check if the email domain is a valid one.
 *
 * @param WPCF7_Validation $result
 * @param WPCF7_FormTag $tag
 * @return $result
 */
function email_domain_validation_filter( $result, $tag ) {
	global $email_filter_domains;

	if ( in_array( 'class:domainfilter', $tag->options ) ) {
		$your_email = isset( $_POST[ $tag->name ] ) ? sanitize_email( $_POST[ $tag->name ] ) : '';
		if ( '' != $your_email ) {
			list( $local, $your_email_domain ) = explode( '@', $your_email, 2 );

			if ( ! in_array( $your_email_domain, $email_filter_domains ) ) {
				$result->invalidate( $tag, sprintf( __( 'Valid domains are: %s', 'cf7-email-domain-filter' ), implode( ' ,', $email_filter_domains ) ) );
			}
		}
	}

	return $result;
}

