<?php
/*
Copyright 2009-2017 John Blackbourn

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

class QM_Collector_Caps extends QM_Collector {

	public $id = 'caps';

	public function name() {
		return __( 'Capability Checks', 'query-monitor' );
	}

	public function __construct() {
		parent::__construct();
		add_filter( 'user_has_cap', array( $this, 'filter_user_has_cap' ), 9999, 4 );
	}

	public function filter_user_has_cap( array $user_caps, array $caps, array $args, WP_User $user ) {
		$trace  = new QM_Backtrace;
		$result = true;

		foreach ( $caps as $cap ) {
			if ( empty( $user_caps[ $cap ] ) ) {
				$result = false;
				break;
			}
		}

		$this->data['caps'][] = array(
			'args'   => $args,
			'trace'  => $trace,
			'result' => $result,
		);

		return $user_caps;
	}

	public function process() {
		$all_parts = array();
		$components = array();

		foreach ( $this->data['caps'] as $i => $cap ) {
			$name = $cap['args'][0];
			$parts = array_filter( preg_split( '#[_/-]#', $name ) );
			$this->data['caps'][ $i ]['parts'] = $parts;
			$this->data['caps'][ $i ]['name']  = $name;
			$this->data['caps'][ $i ]['args']  = array_slice( $cap['args'], 2 );
			$all_parts = array_merge( $all_parts, $parts );
			$component = $cap['trace']->get_component();
			$components[$component->name] = $component->name;
		}

		$this->data['parts'] = array_unique( array_filter( $all_parts ) );
		$this->data['components'] = $components;
	}

}

function register_qm_collector_caps( array $collectors, QueryMonitor $qm ) {
	$collectors['caps'] = new QM_Collector_Caps;
	return $collectors;
}

add_filter( 'qm/collectors', 'register_qm_collector_caps', 20, 2 );
