<?php


namespace CommonsBooking\Repository;


use CommonsBooking\Plugin;
use CommonsBooking\Wordpress\CustomPostType\Timeframe;
use Exception;
use WP_Post;
use WP_Query;

class Booking extends PostRepository {

	/**
	 * Returns 0:00 timestamp for day of $timestamp.
	 * @param $timestamp
	 *
	 * @return false|int
	 */
	protected static function getStartTimestamp($timestamp) {
		return strtotime( "midnight", $timestamp );
	}

	/**
	 * Returns 23:59 timestamp for day of $timestamp.
	 * @param $startTimestamp
	 *
	 * @return false|int
	 */
	protected static function getEndTimestamp($startTimestamp) {
		return strtotime( '+23 Hours +59 Minutes +59 Seconds', $startTimestamp );
	}

	/**
	 * Returns bookings ending at day of timestamp.
	 * @param int $timestamp
	 * @param array $customArgs
	 *
	 * @return array|int[]|WP_Post[]
	 * @throws Exception
	 */
	public static function getEndingBookingsByDate( int $timestamp, array $customArgs = [] ) {
		$startTimestamp = self::getStartTimestamp($timestamp);
		$endTimestamp   = self::getEndTimestamp($startTimestamp);

		// Default query
		$args = array(
			'post_type'   => Timeframe::getSimilarPostTypes(),
			'meta_query'  => array(
				'relation' => "AND",
				array(
					'key'     => \CommonsBooking\Model\Timeframe::REPETITION_END,
					'value'   => $endTimestamp,
					'compare' => '<=',
					'type'    => 'numeric',
				),
				array(
					'key'     => \CommonsBooking\Model\Timeframe::REPETITION_END,
					'value'   => $startTimestamp,
					'compare' => '>=',
					'type'    => 'numeric'
				),
				array(
					'key'     => 'type',
					'value'   => Timeframe::BOOKING_ID,
					'compare' => '=',
				)
			),
			'post_status' => array( 'confirmed', 'unconfirmed' ),
			'nopaging'    => true,
		);

		// Overwrite args with passed custom args
		$args = array_merge( $args, $customArgs );

		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			$posts = $query->get_posts();

			// Filter by post_status, query seems not to work reliable
			$posts = array_filter( $posts, function ( $post ) use ( $args ) {
				return in_array( $post->post_status, $args['post_status'] );
			} );

			foreach ( $posts as &$post ) {
				$post = new \CommonsBooking\Model\Booking( $post );
			}

			return $posts;
		}

		return [];
	}

	/**
	 * Returns bookings beginning at day of timestamp.
	 * @param int $timestamp
	 * @param array $customArgs
	 *
	 * @return array|int[]|WP_Post[]
	 * @throws Exception
	 */
	public static function getBeginningBookingsByDate( int $timestamp, array $customArgs = [] ) {
		$startTimestamp = self::getStartTimestamp($timestamp);
		$endTimestamp   = self::getEndTimestamp($startTimestamp);

		// Default query
		$args = array(
			'post_type'   => Timeframe::getSimilarPostTypes(),
			'meta_query'  => array(
				'relation' => "AND",
				array(
					'key'     => \CommonsBooking\Model\Timeframe::REPETITION_START,
					'value'   => $endTimestamp,
					'compare' => '<=',
					'type'    => 'numeric',
				),
				array(
					'key'     => \CommonsBooking\Model\Timeframe::REPETITION_START,
					'value'   => $startTimestamp,
					'compare' => '>=',
					'type'    => 'numeric'
				),
				array(
					'key'     => 'type',
					'value'   => Timeframe::BOOKING_ID,
					'compare' => '=',
				)
			),
			'post_status' => array( 'confirmed', 'unconfirmed' ),
			'nopaging'    => true,
		);

		// Overwrite args with passed custom args
		$args = array_merge( $args, $customArgs );

		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			$posts = $query->get_posts();
			foreach ( $posts as &$post ) {
				$post = new \CommonsBooking\Model\Booking( $post );
			}

			return $posts;
		}

		return [];
	}

	/**
	 * @param $startDate
	 * @param $endDate
	 * @param $location
	 * @param $item
	 *
	 * @return null|\CommonsBooking\Model\Booking
	 * @throws Exception
	 */
	public static function getByDate( $startDate, $endDate, $location, $item ): ?\CommonsBooking\Model\Booking {
		// Default query
		$args = array(
			'post_type'   => Timeframe::getSimilarPostTypes(),
			'meta_query'  => array(
				'relation' => "AND",
				array(
					'key'     => 'repetition-start',
					'value'   => intval( $startDate ),
					'compare' => '=',
					'type'    => 'numeric',
				),
				array(
					'key'     => \CommonsBooking\Model\Timeframe::REPETITION_END,
					'value'   => $endDate,
					'compare' => '=',
				),
				array(
					'key'     => 'type',
					'value'   => Timeframe::BOOKING_ID,
					'compare' => '=',
				),
				array(
					'key'     => 'location-id',
					'value'   => $location,
					'compare' => '=',
				),
				array(
					'key'     => 'item-id',
					'value'   => $item,
					'compare' => '=',
				),
			),
			'post_status' => array( 'confirmed', 'unconfirmed' ),
			'nopaging'    => true,
		);

		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			$posts = $query->get_posts();
			$posts = array_filter( $posts, function ( $post ) {
				return in_array( $post->post_status, array( 'confirmed', 'unconfirmed' ) );
			} );

			// If there is exactly one result, return it.
			if ( count( $posts ) == 1 ) {
				$booking = new \CommonsBooking\Model\Booking( $posts[0] );
				if ( in_array( $booking->getPost()->post_status, array( 'confirmed', 'unconfirmed' ) ) ) {
					return $booking;
				}
			}

			// This shouldn't happen.
			if ( count( $posts ) > 1 ) {
				throw new Exception( __CLASS__ . "::" . __LINE__ . ": Found more than one bookings" );
			}
		}

		return null;
	}

	/**
	 * @param $startDate int
	 * @param $endDate int
	 * @param $locationId
	 * @param $itemId
	 * @param array $customArgs
	 *
	 * @return \CommonsBooking\Model\Booking[]|null
	 * @throws Exception
	 */
	public static function getByTimerange(
		int $startDate,
		int $endDate,
		$locationId,
		$itemId,
		array $customArgs = []
	): ?array {
		// Default query
		$args = array(
			'post_type'   => Timeframe::getSimilarPostTypes(),
			'meta_query'  => array(
				'relation' => "AND",
				array(
					'key'     => \CommonsBooking\Model\Timeframe::REPETITION_START,
					'value'   => $endDate,
					'compare' => '<=',
					'type'    => 'numeric',
				),
				array(
					'key'     => \CommonsBooking\Model\Timeframe::REPETITION_END,
					'value'   => $startDate,
					'compare' => '>=',
					'type'    => 'numeric'
				),
				array(
					'key'     => 'type',
					'value'   => Timeframe::BOOKING_ID,
					'compare' => '=',
				)
			),
			'post_status' => array( 'confirmed', 'unconfirmed' ),
			'nopaging'    => true,
		);

		if ( $locationId ) {
			$args['meta_query'][] = array(
				'key'     => 'location-id',
				'value'   => $locationId,
				'compare' => '=',
			);
		}

		if ( $itemId ) {
			$args['meta_query'][] = array(
				'key'     => 'item-id',
				'value'   => $itemId,
				'compare' => '=',
			);
		}

		// Overwrite args with passed custom args
		$args = array_merge( $args, $customArgs );

		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			$posts = $query->get_posts();

			// Filter by post_status, query seems not to work reliable
			$posts = array_filter( $posts, function ( $post ) use ( $args ) {
				return in_array( $post->post_status, $args['post_status'] );
			} );

			foreach ( $posts as &$post ) {
				$post = new \CommonsBooking\Model\Booking( $post );
			}

			return $posts;
		}

		return [];
	}

	/**
	 * Returns all bookings, allowed to see/edit for current user.
	 *
	 * @param bool $asModel
	 * @param null $startDate
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function getForCurrentUser( bool $asModel = false, $startDate = null ): array {
		if ( ! is_user_logged_in() ) {
			return [];
		}

		$current_user = wp_get_current_user();
		$customId     = $current_user->ID;

		if ( Plugin::getCacheItem( $customId ) ) {
			return Plugin::getCacheItem( $customId );
		} else {
			$posts = \CommonsBooking\Repository\Timeframe::get(
				[],
				[],
				[ Timeframe::BOOKING_ID ],
				null,
				$asModel,
				$startDate,
				[ 'canceled', 'confirmed', 'unconfirmed' ]
			);
			if ( $posts ) {
				// Check if it is the main query and one of our custom post types
				$posts = array_filter( $posts, function ( $post ) {
					return commonsbooking_isCurrentUserAllowedToEdit( $post );
				} );
			}
			Plugin::setCacheItem( $posts, $customId );
		}

		return $posts;
	}

	/**
	 * @param \CommonsBooking\Model\Restriction $restriction
	 *
	 * @return \WP_Post[]|null
	 * @throws Exception
	 */
	public static function getByRestriction( \CommonsBooking\Model\Restriction $restriction ): ?array {
		return self::getByTimerange(
			$restriction->getStartDate(),
			$restriction->getEndDate(),
			$restriction->getLocationId(),
			$restriction->getItemId()
		);
	}

	/**
	 * @param \CommonsBooking\Model\Restriction $restriction
	 *
	 * @return WP_Post[]|null
	 */
	public static function getCanceledByRestriction( \CommonsBooking\Model\Restriction $restriction ): ?array {
		try {
			return self::getByTimerange(
				$restriction->getStartDate(),
				$restriction->getEndDate(),
				$restriction->getLocationId(),
				$restriction->getItemId(),
				[
					'post_status' => array( 'canceled' ),
				]
			);
		} catch ( Exception $exception ) {
			return [];
		}
	}

}
