<?php
/**
 * Database access for RSVPs.
 *
 * @package EventRsvp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom table schema and queries.
 */
class Event_RSVP_DB {

	const CACHE_GROUP = 'event_rsvp';

	/**
	 * Columns that may be used for ORDER BY.
	 *
	 * @var string[]
	 */
	const SORTABLE = array( 'id', 'post_id', 'name', 'email', 'guests', 'created_at' );

	/**
	 * Full table name including the site prefix.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'event_rsvps';
	}

	/**
	 * Creates or updates the table with dbDelta().
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table           = self::table();
		$charset_collate = $wpdb->get_charset_collate();

		// dbDelta() is strict about formatting: one field per line, two spaces after PRIMARY KEY.
		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			post_id bigint(20) unsigned NOT NULL DEFAULT 0,
			name varchar(200) NOT NULL DEFAULT '',
			email varchar(200) NOT NULL DEFAULT '',
			guests tinyint(3) unsigned NOT NULL DEFAULT 1,
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY post_id (post_id),
			KEY email (email(191))
		) {$charset_collate};";

		dbDelta( $sql );

		update_option( 'event_rsvp_db_version', EVENT_RSVP_DB_VERSION, false );
	}

	/**
	 * Inserts an RSVP.
	 *
	 * @param int    $post_id Post the RSVP belongs to.
	 * @param string $name    Sanitized name.
	 * @param string $email   Sanitized email.
	 * @param int    $guests  Number of guests (1–5).
	 * @return int|false Inserted row ID or false on failure.
	 */
	public static function insert( $post_id, $name, $email, $guests ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			self::table(),
			array(
				'post_id'    => (int) $post_id,
				'name'       => $name,
				'email'      => $email,
				'guests'     => (int) $guests,
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%d', '%s', '%s', '%d', '%s' )
		);

		if ( false === $result ) {
			return false;
		}

		wp_cache_set_last_changed( self::CACHE_GROUP );

		return (int) $wpdb->insert_id;
	}

	/**
	 * Whether an email has already RSVP'd for a post.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $email   Email address.
	 * @return bool
	 */
	public static function exists( $post_id, $email ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$found = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT id FROM %i WHERE post_id = %d AND email = %s LIMIT 1',
				self::table(),
				$post_id,
				$email
			)
		);

		return null !== $found;
	}

	/**
	 * Number of RSVPs (or total guests) for a post. Cached until the table changes.
	 *
	 * @param int  $post_id    Post ID.
	 * @param bool $sum_guests Return the total number of guests instead of the RSVP count.
	 * @return int
	 */
	public static function count_for_post( $post_id, $sum_guests = false ) {
		global $wpdb;

		$post_id   = (int) $post_id;
		$cache_key = sprintf(
			'%s:%d:%s',
			$sum_guests ? 'guests' : 'count',
			$post_id,
			wp_cache_get_last_changed( self::CACHE_GROUP )
		);

		$cached = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( false !== $cached ) {
			return (int) $cached;
		}

		if ( $sum_guests ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$value = $wpdb->get_var(
				$wpdb->prepare( 'SELECT COALESCE(SUM(guests), 0) FROM %i WHERE post_id = %d', self::table(), $post_id )
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$value = $wpdb->get_var(
				$wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE post_id = %d', self::table(), $post_id )
			);
		}

		$value = (int) $value;
		wp_cache_set( $cache_key, $value, self::CACHE_GROUP );

		return $value;
	}

	/**
	 * Fetches a page of RSVPs.
	 *
	 * @param array $args {
	 *     @type string $orderby  Column to sort by.
	 *     @type string $order    ASC or DESC.
	 *     @type int    $per_page Rows per page. 0 for all rows.
	 *     @type int    $page     1-based page number.
	 * }
	 * @return array[] Rows as associative arrays.
	 */
	public static function get_rows( $args = array() ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'orderby'  => 'created_at',
				'order'    => 'DESC',
				'per_page' => 20,
				'page'     => 1,
			)
		);

		$orderby = in_array( $args['orderby'], self::SORTABLE, true ) ? $args['orderby'] : 'created_at';
		$order   = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

		// $order is whitelisted above; identifiers go through %i.
		$sql = $wpdb->prepare( "SELECT * FROM %i ORDER BY %i {$order}, id {$order}", self::table(), $orderby ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$per_page = (int) $args['per_page'];
		if ( $per_page > 0 ) {
			$offset = max( 0, ( (int) $args['page'] - 1 ) * $per_page );
			$sql   .= $wpdb->prepare( ' LIMIT %d OFFSET %d', $per_page, $offset );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
		return (array) $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Total number of RSVPs in the table.
	 *
	 * @return int
	 */
	public static function count_all() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i', self::table() ) );
	}

	/**
	 * Deletes RSVPs by ID.
	 *
	 * @param int[] $ids Row IDs.
	 * @return int Number of rows deleted.
	 */
	public static function delete( array $ids ) {
		global $wpdb;

		$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
		if ( empty( $ids ) ) {
			return 0;
		}

		$placeholders = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM %i WHERE id IN ({$placeholders})", array_merge( array( self::table() ), $ids ) ) );

		wp_cache_set_last_changed( self::CACHE_GROUP );

		return (int) $deleted;
	}
}
