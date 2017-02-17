<?php
/**
 * This file is part of the TaskBreaker WordPress Plugin package.
 *
 * (c) Joseph G. <joseph@useissuestabinstead.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package TaskBreaker\TaskBreakerTransactions
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! is_user_logged_in() ) {
	return;
}

$dbase = TaskBreaker::wpdb();

$term = filter_input( INPUT_GET, 'term', FILTER_SANITIZE_STRING );
$group_id = filter_input( INPUT_GET, 'group_id', FILTER_SANITIZE_NUMBER_INT );
$prefix = $dbase->prefix;

$stmt = $dbase->prepare(
	"SELECT {$prefix}bp_groups_members.user_id as id, {$prefix}users.display_name as text FROM {$prefix}bp_groups_members INNER JOIN {$prefix}users ON {$prefix}bp_groups_members.user_id = {$prefix}users.ID WHERE {$prefix}bp_groups_members.group_id = %d AND {$prefix}users.display_name LIKE %s ORDER BY {$prefix}users.display_name ASC LIMIT 10",
	$group_id, '%' . $dbase->esc_like( $term ) . '%'
);

$results = $dbase->get_results( $stmt, ARRAY_A );

$formatted_results = array();

if ( ! empty( $results ) ) {

	foreach ( $results as $result ) {

		if ( ! empty( $result ['text'] ) ) {

			$image_tag = get_avatar( absint( $result['id'] ) );

			preg_match( '/< *img[^>]*src *= *["\']?([^"\']*)/i', $image_tag, $image_src );

			$formatted_results[] = array(
				'id' => $result['id'],
				'text' => $result['text'],
				'avatar' => $image_src[1],
			);

		}
	}
}

$this->task_breaker_api_message(
	array(
		'results' => $formatted_results,
	)
);
