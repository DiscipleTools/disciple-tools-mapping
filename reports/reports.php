<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Class DT_Network_Dashboard_Reports
 */
class DT_Network_Dashboard_Reports
{
    public static function trigger_transfer( $site_post_id, $type ) {

        // Trigger Remote Report from Site
        $site = Site_Link_System::get_site_connection_vars( $site_post_id );
        if ( is_wp_error( $site ) ) {
            return new WP_Error( __METHOD__, 'Error creating site connection details.' );
        }
        $args = [
            'method' => 'POST',
            'body' => [
                'transfer_token' => $site['transfer_token'],
                'type' => $type,
            ]
        ];
        $result = wp_remote_post( 'https://' . $site['url'] . '/wp-json/dt-public/v1/network/trigger_transfer', $args );
        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'failed_remote_post', $result->get_error_message() );
        } else {
            return $result['body'];
        }
    }

    public static function query_site_link_list() {
        global $wpdb;
        $list = $wpdb->get_results("
            SELECT post_title as name, ID as id
            FROM $wpdb->posts
            JOIN $wpdb->postmeta
              ON $wpdb->posts.ID=$wpdb->postmeta.post_id
              AND $wpdb->postmeta.meta_key = 'type'
              AND $wpdb->postmeta.meta_value = 'network_dashboard'
            WHERE post_type = 'site_link_system' 
                AND post_status = 'publish'
        ", ARRAY_A );

        return $list;
    }

    public static function insert_report( $args ) {
        global $wpdb;

        $args = wp_parse_args( $args, [
            'partner_id' => null,
            'total_contacts' => 0,
            'total_groups' => 0,
            'total_users' => 0,
            'date' => current_time( 'mysql' ),
            'raw_response' => '',
        ]);

        $result = $wpdb->insert( $wpdb->dt_network_reports, [
                'partner_id' => $args['partner_id'],
                'total_contacts' => $args['total_contacts'],
                'total_groups' => $args['total_groups'],
                'total_users' => $args['total_users'],
                'date' => $args['date'],
                'raw_response' => $args['raw_response'],
            ]);

        if (! $result ) {
            return new WP_Error(__METHOD__, 'Failed to insert report data. ' . $wpdb->last_error );
        } else {
            return (int) $wpdb->insert_id;
        }

    }

    public static function update_site_profile( $site_id, $site_profile ) {
        $error = new WP_Error;

        if ( empty( $site_profile ) ) {
            $error->add(__METHOD__, 'Empty site profile' );
            return [
                'status' => 'FAIL',
                'action' => $error,
            ];
        }

        foreach ( $site_profile as $key => $value ) {
            update_post_meta( $site_id, $key, $value );
        }

        return [
            'status' => 'OK',
            'action' => 'Updated'
        ];
    }

    public static function update_site_locations( $site_id, $site_locations ) {
        $error = new WP_Error;

        if ( empty( $site_profile ) ) {
            $error->add(__METHOD__, 'Empty site profile' );
            return [
                'status' => 'FAIL',
                'action' => $error,
            ];
        }

        update_post_meta( $site_id, 'partner_locations_check_sum', $site_locations['check_sum'] );

        

        return [
            'status' => 'OK',
            'action' => 'Updated'
        ];
    }
}