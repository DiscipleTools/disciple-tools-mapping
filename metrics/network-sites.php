<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Network_Dashboard_Metrics_Sites extends DT_Network_Dashboard_Metrics_Base
{
    public function __construct() {
        parent::__construct();

        $this->base_slug = 'sites';
        $this->slug = '';
        $this->base_title = __( 'Site Reports', 'disciple_tools' );
        $this->title = __( 'Site Reports', 'disciple_tools' );
        $this->menu_title = 'Site Reports';
        $this->url = $this->root_slug . '/' . $this->base_slug;
        $this->key = $this->root_slug . '_' . $this->base_slug;

        add_filter( 'dt_network_dashboard_build_menu', [ $this, 'menu' ], 4 );
        add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ], 199 );

        if ( $this->url === $this->url_path ) {
            $this->js_file_name = $this->root_slug . '-' . $this->base_slug . '.js';
            $this->js_object_name = $this->key;
            add_action( 'wp_enqueue_scripts', [ $this, 'add_scripts' ], 99 );
        }

    }

    public function add_scripts() {
        wp_enqueue_script( $this->js_object_name .'_script',
            plugin_dir_url( __FILE__ ) . $this->js_file_name,
            [
            'jquery',
            'network_base_script',
            'datatable',
            ],
            filemtime( plugin_dir_path( __FILE__ ) . $this->js_file_name ),
        true );
    }

    public function menu( $tree ){
        // top levels load at 10, sub levels need to load at 50+
        $tree[$this->base_slug] = [
            'key' => $this->key,
            'label' => $this->menu_title,
            'url' => '/'.$this->url,
            'children' => []
        ];
        return $tree;
    }

    public function add_url( $template_for_url) {
        $template_for_url[$this->url] = 'template-metrics.php';
        return $template_for_url;
    }

}
new DT_Network_Dashboard_Metrics_Sites();