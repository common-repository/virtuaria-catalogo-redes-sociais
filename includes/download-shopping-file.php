<?php
/**
 * Handle content from Facebook Shopping file endpoint.
 *
 * @package Virtuaria/Integrations/Facebook
 */

defined( 'ABSPATH' ) || exit;

$fila_path = plugin_dir_path( __FILE__ ) . '../feeds/sites/' . get_current_blog_id() . '/produtos.xml';
if ( file_exists( $fila_path ) ) {
	header( 'Content-type: text/xml' );
	$xml = simplexml_load_file( $fila_path );
	// phpcs:ignore
	echo $xml->asXML();
} else {
	echo 'Feed ainda não gerado!';
}
