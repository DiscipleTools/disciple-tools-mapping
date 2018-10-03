<?php
/**
 * Rest endpoints
 *
 * @package  Disciple_Tools
 * @category Plugin
 * @since    0.1
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Class DT_Network_Dashboard_Reports_Endpoints
 */
class DT_Network_Dashboard_Reports_Endpoints
{

    private $version = 1;
    private $namespace;
    private $public_namespace;

    /**
     * DT_Network_Dashboard_Reports_Endpoints The single instance of DT_Network_Dashboard_Reports_Endpoints.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Main DT_Network_Dashboard_Reports_Endpoints Instance
     * Ensures only one instance of DT_Network_Dashboard_Reports_Endpoints is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return DT_Network_Dashboard_Reports_Endpoints instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        $this->namespace = "dt" . "/v" . intval( $this->version );
        $this->public_namespace = "dt-public" . "/v" . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    public function add_api_routes() {
        register_rest_route(
            $this->public_namespace, '/network/collect/project_totals', [
                'methods'  => 'POST',
                'callback' => [ $this, 'project_totals' ],
            ]
        );
        register_rest_route(
            $this->public_namespace, '/network/collect/site_profile', [
                'methods'  => 'POST',
                'callback' => [ $this, 'site_profile' ],
            ]
        );
        register_rest_route(
            $this->public_namespace, '/network/collect/site_locations', [
                'methods'  => 'POST',
                'callback' => [ $this, 'site_locations' ],
            ]
        );
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return int|WP_Error
     */
    public function project_totals( WP_REST_Request $request ) {

        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return $params;
        }

        if ( ! current_user_can( 'network_dashboard_transfer' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        if ( isset( $params['report_data'] ) ) {
            $result = DT_Network_Dashboard_Reports::insert_report( $params['report_data'] );
            return $result;
        } else {
            return new WP_Error( __METHOD__, 'Missing required parameter: report_data.' );
        }

    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|int|\WP_Error
     */
    public function site_profile( WP_REST_Request $request ) {

        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return $params;
        }

        if ( ! current_user_can( 'network_dashboard_transfer' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        if ( isset( $params['report_data'] ) ) {
            $result = 'Site Profile Success';
            return $result;
        } else {
            return new WP_Error( __METHOD__, 'Missing required parameter: report_data.' );
        }

    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|int|\WP_Error
     */
    public function site_locations( WP_REST_Request $request ) {

        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return $params;
        }

        if ( ! current_user_can( 'network_dashboard_transfer' ) ) {
            return new WP_Error( __METHOD__, 'Permission error.' );
        }

        if ( isset( $params['report_data'] ) ) {
            $result = 'Site Locations Success';
            return $result;
        } else {
            return new WP_Error( __METHOD__, 'Missing required parameter: report_data.' );
        }

    }

    public function process_token( WP_REST_Request $request ) {

        $params = $request->get_params();

        // required token parameter challenge
        if ( ! isset( $params['transfer_token'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }

        $valid_token = Site_Link_System::verify_transfer_token( $params['transfer_token'] );

        // required valid token challenge
        if ( ! $valid_token ) {
            dt_write_log( $valid_token );
            return new WP_Error( __METHOD__, 'Invalid transfer token' );
        }

        // required permission challenge (that this token comes from an approved network report site link)
        if ( ! current_user_can( 'network_dashboard_transfer' ) ) {
            return new WP_Error( __METHOD__, 'Network report permission error.' );
        }

        return $params;
    }
}
DT_Network_Dashboard_Reports_Endpoints::instance();