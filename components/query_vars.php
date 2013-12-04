<?php
/*
Copyright 2013 John Blackbourn

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

class QM_Component_Query_Vars extends QM_Component {

	var $id = 'query_vars';

	function name() {
		return __( 'Query Vars', 'query-monitor' );
	}

	function __construct() {
		parent::__construct();
		add_filter( 'query_monitor_menus', array( $this, 'admin_menu' ), 90 );
	}

	function process() {

		$plugin_qvars = array_flip( apply_filters( 'query_vars', array() ) );
		$qvars        = $GLOBALS['wp_query']->query_vars;
		$query_vars   = array();

		foreach ( $qvars as $k => $v ) {
			if ( isset( $plugin_qvars[$k] ) ) {
				if ( '' !== $v )
					$query_vars[$k] = $v;
			} else {
				if ( !empty( $v ) )
					$query_vars[$k] = $v;
			}
		}

		ksort( $query_vars );

		# First add plugin vars to $this->data['qvars']:
		foreach ( $query_vars as $k => $v ) {
			if ( isset( $plugin_qvars[$k] ) ) {
				$this->data['qvars'][$k] = $v;
				$this->data['plugin_qvars'][$k] = $v;
			}
		}

		# Now add all other vars to $this->data['qvars']:
		foreach ( $query_vars as $k => $v ) {
			if ( !isset( $plugin_qvars[$k] ) )
				$this->data['qvars'][$k] = $v;
		}

	}

	function admin_menu( array $menu ) {

		$count = isset( $this->data['plugin_qvars'] ) ? count( $this->data['plugin_qvars'] ) : 0;

		$title = ( empty( $count ) )
			? __( 'Query Vars', 'query-monitor' )
			: __( 'Query Vars (+%s)', 'query-monitor' );

		$menu[] = $this->menu( array(
			'title' => sprintf( $title, number_format_i18n( $count ) )
		) );
		return $menu;

	}

}

function register_qm_query_vars( array $qm ) {
	$qm['query_vars'] = new QM_Component_Query_Vars;
	return $qm;
}

add_filter( 'query_monitor_components', 'register_qm_query_vars', 70 );
