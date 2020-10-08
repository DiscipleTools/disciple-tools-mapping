<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Maps_Area extends DT_Network_Dashboard_Metrics_Base
{
    public function __construct() {
        if ( empty( DT_Mapbox_API::get_key() ) ){
            return;
        }
        parent::__construct();

        $this->base_slug = 'maps';
        $this->slug = 'area';
        $this->base_title = __( 'Area Maps', 'disciple_tools' );
        $this->title = __( 'Area Maps', 'disciple_tools' );
        $this->menu_title = 'Area Maps';
        $this->url = $this->root_slug . '/' . $this->base_slug . '/'  . $this->slug;
        $this->key = $this->root_slug . '_' . $this->base_slug . '_' . $this->slug;
        $this->js_file_name = $this->root_slug . '-' . $this->base_slug . '-' . $this->slug . '.js';
        $this->js_object_name = $this->key;

        add_filter( 'dt_network_dashboard_build_menu', [ $this, 'menu' ], 50 );
        add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ], 199 );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );

        if ( $this->url === $this->url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'add_scripts' ], 99 );
        }
    }

    public function add_scripts() {
        wp_enqueue_script( $this->js_object_name .'_script', plugin_dir_url(__FILE__) . $this->js_file_name, [
            'jquery',
        ], filemtime( plugin_dir_path(__FILE__) . $this->js_file_name ), true );
        wp_localize_script(
            $this->js_object_name .'_script', $this->js_object_name, [
                'endpoint' => $this->url,
            ]
        );
    }

    public function menu( $tree ){
        $tree[$this->base_slug]['children'][$this->slug] = [
            'key' => $this->key,
            'label' => $this->menu_title,
            'url' => '/'.$this->url,
            'children' => [
                [
                    'key' => $this->key . '_contacts',
                    'label' => 'Contacts',
                    'url' => '/'.$this->url,
                    'children' => []
                ],
                [
                    'key' => $this->key . '_groups',
                    'label' => 'Groups',
                    'url' => '/'.$this->url,
                    'children' => []
                ],
                [
                    'key' => $this->key . '_churches',
                    'label' => 'Churches',
                    'url' => '/'.$this->url,
                    'children' => []
                ],
                [
                    'key' => $this->key . '_users',
                    'label' => 'Users',
                    'url' => '/'.$this->url,
                    'children' => []
                ],
            ]
        ];
        return $tree;
    }

    public function add_url( $template_for_url) {
        $template_for_url[$this->url] = 'template-metrics.php';
        return $template_for_url;
    }

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->url . '/', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'endpoint' ],
                ],
            ]
        );
    }

    public function endpoint( WP_REST_Request $request ){
        if ( !$this->has_permission() ) {
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }
        dt_write_log(__METHOD__);
        $params = $request->get_params();

        return $params;
    }

}
new DT_Network_Dashboard_Metrics_Maps_Area();
