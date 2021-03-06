<?php

function mm_add_plugin_search_patterns( $patterns ) {
	$response = mm_api_cache( 'https://api.mojomarketplace.com/mojo-plugin-assets/json/search-patterns.json' );
	if ( ! is_wp_error( $response ) && $patterns = json_decode( $response['body'], 1 ) ) {
		return $patterns;
	}
}
add_filter( 'mm_plugin_search_patterns', 'mm_add_plugin_search_patterns' );

function mm_check_plugin_search_value( $search ) {
	$patterns = apply_filters( 'mm_plugin_search_patterns', array() );
	foreach ( $patterns as $pattern => $plugin ) {
		if ( preg_match( $pattern, $search ) ) {
			return $plugin;
		}
	}
	return false;
}

function mm_plugin_search_result() {
	if ( isset( $_GET['s'] ) && $plugin = mm_check_plugin_search_value( $_GET['s'] ) ) {
		$link = $plugin['url'];
		if ( strlen( $plugin['id'] ) > 5 ) {
			$link = add_query_arg( array( 'page' => 'mojo-single-item', 'item_id' => $plugin['id'] ), admin_url( 'admin.php' ) );
		}
		$link = mm_build_link( $link, array( 'utm_medium' => 'plugin_admin', 'utm_content' => 'plugin_search_more_details' ) );
		$buy_now_btn = ( isset( $plugin['buy_now_btn'] ) ) ? $plugin['buy_now_btn']  : 'Buy Now';
		$buy_now = ( isset( $plugin['id'] ) ) ? add_query_arg( array( 'item_id' => $plugin['id'] ), 'https://www.mojomarketplace.com/cart' ) : '';
		if ( '' === $buy_now ) {
			$buy_now = ( isset( $plugin['buy_now_link'] ) ) ? $plugin['buy_now_link'] : '';
		}
		$buy_now = mm_build_link( $buy_now, array( 'utm_medium' => 'plugin_admin', 'utm_content' => 'plugin_search_cta' ) );
		$image = ( isset( $plugin['img'] ) ) ? $plugin['img'] : 'https://marketplace-images-production.s3-us-west-2.amazonaws.com/vault/items/thumbnail-54340948-aff4-41d1-b5d4-0cf10a141528-260x156-i5o1C.jpg';
		$title = ( isset( $plugin['title'] ) ) ? $plugin['title'] : $plugin['name'];
		$short_desc = ( isset( $plugin['short_desc'] ) ) ? $plugin['short_desc'] : '';
		$author = ( isset( $plugin['author'] ) ) ? $plugin['author'] : 'MOJO Marketplace';
		$author_url = ( isset( $plugin['author_url'] ) ) ? $plugin['author_url'] : 'http://www.mojomarketplace.com';
		$author_url = mm_build_link( $author_url, array( 'utm_medium' => 'plugin_admin', 'utm_content' => 'plugin_search_author_link' ) );
		$plugin_html = '<div class="plugin-card">
							<div class="plugin-card-top">
								<a target="_blank" href="' . $link . '" class="plugin-icon" style="background-image: url(' . $image . ');background-position: center;">&nbsp;</a>
								<div class="name column-name">
									<h3><a href="' . $link . '">' . $title . '</a></h3>
									<div class="action-links">
										<ul class="plugin-action-buttons">';

		if ( strlen( $plugin['id'] ) > 5 ) {
			$plugin_html .= '<li>
				<a href="' . $buy_now . '" class="button">' . $buy_now_btn . '</a>
			</li>';
		}

		$plugin_html .= '<li><a href="' . $link . '">More Details</a></li>';

		$plugin_html .= '</ul>
									</div>
								</div>
								<div class="desc column-description">
									<p>' . $short_desc . '</p>
									<p class="authors"> <cite>By <a href="' . $author_url . '" target="_blank">' . $author . '</a></cite></p>
								</div>
							</div>
							<div class="plugin-card-bottom">
				<div class="column-compatibility">
					<span class="">Professional Plugin Service</div>
				</div>
			</div>';
		$plugin_html = mm_minify( $plugin_html );

		echo "
		<script type='text/javascript'>
			jQuery( document ).ready( function( $ ) {
				$( '#the-list' ).prepend( '" . $plugin_html . "' );
				$( '.plugin-card:last-of-type' ).hide();
				$( '.no-plugin-results' ).hide();
			} );
		</script>";

	}
}
add_action( 'admin_notices', 'mm_plugin_search_result' );
