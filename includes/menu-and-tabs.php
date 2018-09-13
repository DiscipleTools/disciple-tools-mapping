<?php
/**
 * DT_Saturation_Mapping_Menu class for the admin page
 *
 * @class       DT_Saturation_Mapping_Menu
 * @version     0.1.0
 * @since       0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}

/**
 * Initialize menu class
 */
DT_Saturation_Mapping_Menu::instance();

/**
 * Class DT_Saturation_Mapping_Menu
 */
class DT_Saturation_Mapping_Menu {

    public $token = 'dt_saturation_mapping';

    private static $_instance = null;

    /**
     * DT_Saturation_Mapping_Menu Instance
     *
     * Ensures only one instance of DT_Saturation_Mapping_Menu is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return DT_Saturation_Mapping_Menu instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        add_action( "admin_menu", array( $this, "register_menu" ) );

        /**
         * Catch enabling and disabling of the network feature.
         */
        if ( isset( $_POST['enable_network_form'] ) && ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'enable_network'.get_current_user_id() ) ) ) {
            if ( isset( $_POST['enable_network']) ) {
                update_option( 'dt_saturation_mapping_enable_network', 1, false );
            } else {
                update_option( 'dt_saturation_mapping_enable_network', 0, false );
            }
        }

    } // End __construct()

    /**
     * Loads the subnav page
     * @since 0.1
     */
    public function register_menu() {
        add_menu_page( __( 'Extensions (DT)', 'disciple_tools' ), __( 'Extensions (DT)', 'disciple_tools' ),
        'manage_dt', 'dt_extensions', [ $this, 'extensions_menu' ], 'dashicons-admin-generic', 59 );
        add_submenu_page( 'dt_extensions', __( 'Saturation Mapping', 'dt_saturation_mapping' ),
        __( 'Saturation Mapping', 'dt_saturation_mapping' ), 'manage_dt', $this->token, [ $this, 'content' ] );
    }

    /**
     * Menu stub. Replaced when Disciple Tools Theme fully loads.
     */
    public function extensions_menu() {}

    /**
     * Builds page contents
     * @since 0.1
     */
    public function content() {

        if ( !current_user_can( 'manage_dt' ) ) { // manage dt is a permission that is specific to Disciple Tools and allows admins, strategists and dispatchers into the wp-admin
            wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.' ) );
        }

        if ( isset( $_GET["tab"] ) ) {
            $tab = sanitize_key( wp_unslash( $_GET["tab"] ) );
        } else {
            $tab = 'general';
        }

        $link = 'admin.php?page='.$this->token.'&tab=';

        ?>
        <div class="wrap">
            <h2><?php esc_attr_e( 'Saturation Mapping', 'dt_saturation_mapping' ) ?></h2>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_attr( $link ) . 'general' ?>" class="nav-tab
                <?php ( $tab == 'general' || ! isset( $tab ) ) ? esc_attr_e( 'nav-tab-active', 'dt_saturation_mapping' ) : print ''; ?>">
                    <?php esc_attr_e( 'Overview', 'dt_saturation_mapping' ) ?></a>
                <a href="<?php echo esc_attr( $link ) . 'local' ?>" class="nav-tab
                <?php ( $tab == 'local' ) ? esc_attr_e( 'nav-tab-active', 'dt_saturation_mapping' ) : print ''; ?>">
                    <?php esc_attr_e( 'Install Local Locations', 'dt_saturation_mapping' ) ?></a>

                <?php // make tab dependent on network enable.
                if ( get_option( 'dt_saturation_mapping_enable_network' ) ) : ?>

                    <a href="<?php echo esc_attr( $link ) . 'network' ?>" class="nav-tab
                    <?php ( $tab == 'network' ) ? esc_attr_e( 'nav-tab-active', 'dt_saturation_mapping' ) : print ''; ?>">
                        <?php esc_attr_e( 'Install Network Locations', 'dt_saturation_mapping' ) ?></a>
                    <a href="<?php echo esc_attr( $link ) . 'configure-network' ?>" class="nav-tab
                    <?php ( $tab == 'configure-network' ) ? esc_attr_e( 'nav-tab-active', 'dt_saturation_mapping' ) : print ''; ?>">
                        <?php esc_attr_e( 'Configure Network', 'dt_saturation_mapping' ) ?></a>

                <?php endif; ?>
            </h2>

            <?php
            switch ($tab) {
                case "general":
                    $object = new DT_Saturation_Mapping_Tab_General();
                    $object->content();
                    break;
                case "local":
                    $object = new DT_Saturation_Mapping_Tab_Local();
                    $object->content();
                    break;
                case "network":
                    $object = new DT_Saturation_Mapping_Tab_Network();
                    $object->content();
                    break;
                case "configure-network":
                    $object = new DT_Saturation_Mapping_Tab_Configure_Network();
                    $object->content();
                    break;
                default:
                    break;
            }
            ?>

        </div><!-- End wrap -->

        <?php
    }


}

/**
 * Class DT_Starter_Tab_Second
 */
class DT_Saturation_Mapping_Tab_General
{
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->overview_message() ?>
                        <?php $this->enable_network_box() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function enable_network_box() {
        /**
         * Note: post processing is done in the construct of DT_Saturation_Mapping_Menu
         */
        $network = get_option( 'dt_saturation_mapping_enable_network' );

        ?>
        <!-- Box -->
        <form method="post">
            <table class="widefat striped">
                <thead>
                <th>Enable Network</th>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php wp_nonce_field( 'enable_network'.get_current_user_id() ); ?>
                        <label for="enable_network">Enable the network features: </label>
                        <input type="checkbox" class="text" id="enable_network" name="enable_network" <?php $network ? print 'checked' : print ''; ?> />
                        <br>
                        <p><em></em></p>
                        <button type="submit" name="enable_network_form" value="1" class="button">Update</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }

    public function overview_message() {
        ?>
        <style>dt { font-weight:bold;}</style>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <th>Overview of Plugin</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    <dl>
                        <dt>Plugin Purpose</dt>
                        <dd>Collecting reports across many systems is difficult and doing it automatically, even more so. Making sure
                        counts for certain location are counted only once you need a shared database of locations to post counts to.
                        This saturation mapping plugin attempts to set up a globally consistent mapping schema.</dd>

                        <dt>Local vs Network Functions</dt>
                        <dd>This plugin has two functions.
                            <ol>
                                <li> First to extend Disciple Tools with structured mapping data
                                    and to make it easy to install those locations for a team to use as they reach out to a certain area.
                                </li>
                                <li>This plugin also has the ability to add a network (global) dashboard to Disciple Tools for
                                multiple Disciple Tools teams to connect their systems and share reporting (i.e. celebration) of the
                                work between them.
                                </li>
                            </ol>
                        </dd>

                        <dt></dt>
                        <dd></dd>

                        <dt></dt>
                        <dd></dd>

                        <dt></dt>
                        <dd></dd>

                    </dl>

                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }
}


/**
 * Class DT_Starter_Tab_Second
 */
class DT_Saturation_Mapping_Tab_Local
{
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->main_column() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php $this->right_column() ?>

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {
        $available_locations = DT_Saturation_Mapping_Installer::get_list_of_available_locations();
        ?>
        <!-- Box -->
        <form method="post">
        <table class="widefat striped">
            <thead>
            <th>Install</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    <select name="selected_country" id="selected_country">
                        <option>Select</option>
                        <?php
                        echo '<option>----</option>';
                        echo '<option value="US">United States of America</option>';
                        echo '<option>----</option>';
                        foreach ( $available_locations as $country_code => $name ) {
                            echo '<option value="' . $country_code . '">'.$name.'</option>';
                        }
                        ?>

                    </select>
                    <a href="javascript:void(0);" onclick="load_list_by_country()" class="button" id="import_button">Load</a>
                    <script>
                        function load_list_by_country() {
                            let button = jQuery('#import_button')
                            let spinner = ' <span><img src="<?php echo plugin_dir_url( __FILE__ ). '/'; ?>spinner.svg" width="12px" /></span>'
                            button.append(spinner)

                            let country_code = jQuery('#selected_country').val()
                            let data = { "country_code": country_code }
                            jQuery.ajax({
                                type: "POST",
                                data: JSON.stringify(data),
                                contentType: "application/json; charset=utf-8",
                                dataType: "json",
                                url: '<?php echo esc_url_raw( rest_url() ); ?>dt/v1/saturation/load_by_country',
                                beforeSend: function(xhr) {
                                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ) ?>');
                                },
                            })
                                .done(function (data) {
                                    button.empty().append('Load')
                                    let result_div = jQuery('#results')
                                    result_div.empty()
                                    result_div.append('<span style="float:right;"><a href="javascript:void(0);" ' +
                                        'onclick="jQuery(\'.subdivision\').toggle();">collapse/expand all subdivisions</a>' +
                                        '</span><span id="toggle-all"></span><br clear="all" />')

                                    jQuery.each(data, function(i,v) {
                                        result_div.append( '<hr><dt><strong style="font-size:1.4em">' + v.name + '</strong> ' +
                                            '<a id="admin1-link-'+v.geonameid+'" class="page-title-action" onclick="install_admin1_geoname(\''+v.geonameid+'\'); jQuery(this).off(\'click\');">Install</a> ' +
                                            '<span id="install-'+v.geonameid+'"></span>  <span style="float:right;">' +
                                            '<a href="javascript:void(0);" onclick="jQuery(\'.adm2-'+v.geonameid+'\').toggle()">collapse/expand</a>' +
                                            '</span></dt>')

                                        jQuery.each(v.adm2, function(ii, vv) {
                                            result_div.append('<dd id="dd-'+vv.geonameid+'" class="adm2-'+v.geonameid+' subdivision"><strong>' + vv.name + '</strong> ' +
                                                '<button type="button" id="button-'+vv.geonameid+'" class="page-title-action" onclick="install_admin2_geoname(\''+vv.geonameid+'\');" >Install</button> ' +
                                                '<span id="install-'+vv.geonameid+'"></span> ' +
                                                '<a class="show-city-link" id="cities-button-'+vv.geonameid+'" ' +
                                                'onclick="load_cities(\''+vv.geonameid+'\')">Show Cities/Places</a> ' +
                                                '<span id="cities-'+vv.geonameid+'"></span></dd>')
                                        })

                                    })

                                    console.log( 'success ')
                                    console.log( data )
                                })
                                .fail(function (err) {
                                    console.log("error");
                                    console.log(err);
                                })
                        }
                        function install_admin2_geoname( geonameid ) {
                            console.log('install_geoname')

                            jQuery('#button-'+ geonameid ).prop("disabled",true)

                            let report_span = jQuery( '#install-' + geonameid )
                            report_span.append(' <span><img src="<?php echo plugin_dir_url( __FILE__ ). '/'; ?>spinner.svg" width="12px" /></span>')

                            let data = { "geonameid": geonameid }
                            jQuery.ajax({
                                type: "POST",
                                data: JSON.stringify(data),
                                contentType: "application/json; charset=utf-8",
                                dataType: "json",
                                url: '<?php echo esc_url_raw( rest_url() ); ?>dt/v1/saturation/install_admin2_geoname',
                                beforeSend: function(xhr) {
                                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ) ?>');
                                },
                            })
                                .done(function (data) {
                                    report_span.empty().append('&#9989;')
                                    load_current_locations()

                                    console.log( 'success for ' + geonameid)
                                    console.log( data )
                                })
                                .fail(function (err) {
                                    report_span.empty().append('( oops. something failed. )')
                                    console.log("error for " + geonameid );
                                    console.log(err);
                                })
                        }
                        function install_admin1_geoname( geonameid ) {
                            console.log('install_geoname')

                            jQuery('#button-'+ geonameid ).prop("disabled",true)

                            let report_span = jQuery( '#install-' + geonameid )
                            report_span.append(' <span><img src="<?php echo plugin_dir_url( __FILE__ ). '/'; ?>spinner.svg" width="12px" /></span>')

                            let data = { "geonameid": geonameid }
                            jQuery.ajax({
                                type: "POST",
                                data: JSON.stringify(data),
                                contentType: "application/json; charset=utf-8",
                                dataType: "json",
                                url: '<?php echo esc_url_raw( rest_url() ); ?>dt/v1/saturation/install_admin1_geoname',
                                beforeSend: function(xhr) {
                                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ) ?>');
                                },
                            })
                                .done(function (data) {
                                    report_span.empty().append('&#9989;')
                                    load_current_locations()

                                    console.log( 'success for ' + geonameid)
                                    console.log( data )
                                })
                                .fail(function (err) {
                                    report_span.empty().append('( oops. something failed. )')
                                    console.log("error for " + geonameid );
                                    console.log(err);
                                })
                        }

                        function load_cities( geonameid ) {
                            console.log('install_all_cities')

                            jQuery('#cities-button-'+ geonameid ).prop("disabled",true)

                            let report_span = jQuery( '#cities-' + geonameid )
                            report_span.append(' <span><img src="<?php echo plugin_dir_url( __FILE__ ). '/'; ?>spinner.svg" width="12px" /></span>')
                            let city_result_div = jQuery('#dd-'+geonameid)

                            let data = { "geonameid": geonameid }
                            jQuery.ajax({
                                type: "POST",
                                data: JSON.stringify(data),
                                contentType: "application/json; charset=utf-8",
                                dataType: "json",
                                url: '<?php echo esc_url_raw( rest_url() ); ?>dt/v1/saturation/load_cities',
                                beforeSend: function(xhr) {
                                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ) ?>');
                                },
                            })
                                .done(function (data) {
                                    city_result_div.append('<br clear="all" /><hr>')

                                    jQuery.each(data.cities, function(i, v) {
                                        city_result_div.append( '<dd><strong>' + v.name + '</strong> <a class="show-city-link" id="city-button-'+v.geonameid+'" ' +
                                            'class="page-title-action" onclick="install_single_city('+v.geonameid+','+data.admin2+');" >add</a>'+
                                            ' <span id="city-install-'+v.geonameid+'"></span> <dd>')
                                    })

                                    report_span.empty()
                                    load_current_locations()

                                    console.log( 'success for ' + geonameid)
                                    console.log( data )
                                })
                                .fail(function (err) {
                                    report_span.empty().append('( oops. something failed. )')
                                    console.log("error for " + geonameid );
                                    console.log(err);
                                })
                        }
                        function install_single_city( geonameid, admin2 ) {
                            console.log('install_geoname')

                            jQuery('#city-button-'+ geonameid ).prop("onclick",'')

                            let report_span = jQuery( '#city-install-' + geonameid )
                            report_span.append(' <span><img src="<?php echo plugin_dir_url( __FILE__ ). '/'; ?>spinner.svg" width="12px" /></span>')

                            let data = { "geonameid": geonameid, "admin2": admin2 }
                            jQuery.ajax({
                                type: "POST",
                                data: JSON.stringify(data),
                                contentType: "application/json; charset=utf-8",
                                dataType: "json",
                                url: '<?php echo esc_url_raw( rest_url() ); ?>dt/v1/saturation/install_single_city',
                                beforeSend: function(xhr) {
                                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ) ?>');
                                },
                            })
                                .done(function (data) {
                                    report_span.empty().append('&#9989;')
                                    load_current_locations()

                                    console.log( 'success for ' + geonameid)
                                    console.log( data )
                                })
                                .fail(function (err) {
                                    report_span.empty().append('( oops. something failed. )')
                                    console.log("error for " + geonameid );
                                    console.log(err);
                                })
                        }
                        function load_current_locations() {
                            let current_locations = jQuery('#current-locations')
                            return jQuery.ajax({
                                type: "POST",
                                contentType: "application/json; charset=utf-8",
                                dataType: "json",
                                url: '<?php echo esc_url_raw( rest_url() ); ?>dt/v1/saturation/load_current_locations',
                                beforeSend: function(xhr) {
                                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ) ?>');
                                },
                            })
                                .done(function (data) {
                                    current_locations.empty().append(data)
                                    console.log("success")
                                })
                                .fail(function (err) {
                                    console.log("error");
                                    console.log(err);
                                })
                        }
                        jQuery(document).ready(function() {
                            load_current_locations()
                        })

                    </script>
                    <style>
                        dd, li {
                            margin-bottom: 15px;
                        }
                        dt, li {
                            margin-bottom: 20px;
                            margin-top: 20px;
                        }
                        #results .page-title-action {
                            vertical-align: middle;
                        }
                        .show-city-link {
                            cursor: pointer;
                        }
                        #results {
                            width: 66%;
                            float: left;
                        }
                        #city-results {
                            width: 33%;
                            float: right;
                        }
                    </style>
                    <div id="results-container">
                        <div id="results"></div>
                        <div id="city-results"></div>
                    </div>

                </td>
            </tr>
            </tbody>
        </table>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <th>Current Locations</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    <div id="current-locations"></div>
                    <hr>
                    <a href="<?php echo esc_url( admin_url( '/edit.php?post_type=locations' ) ) ?>">View Locations</a>
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }
}


/**
 * Class DT_Starter_Tab_Second
 */
class DT_Saturation_Mapping_Tab_Network
{
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->main_column() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php $this->right_column() ?>

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {
        $available_locations = DT_Saturation_Mapping_Installer::get_list_of_available_locations();
        ?>
        <!-- Box -->
        <form method="post">
            <table class="widefat striped">
                <thead>
                <th>Install</th>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <select name="selected_country" id="selected_country">
                            <option>Select</option>
                            <?php
                            echo '<option>----</option>';
                            echo '<option value="US">United States of America</option>';
                            echo '<option>----</option>';
                            foreach ( $available_locations as $country_code => $name ) {
                                echo '<option value="' . $country_code . '">'.$name.'</option>';
                            }
                            ?>

                        </select>
                        <a href="javascript:void(0);" onclick="load_list_by_country()" class="button" id="import_button">Load</a>
                        <script>
                            function load_list_by_country() {
                                let button = jQuery('#import_button')
                                let spinner = ' <span><img src="<?php echo plugin_dir_url( __FILE__ ). '/'; ?>spinner.svg" width="12px" /></span>'
                                button.append(spinner)

                                let country_code = jQuery('#selected_country').val()
                                let data = { "country_code": country_code }
                                jQuery.ajax({
                                    type: "POST",
                                    data: JSON.stringify(data),
                                    contentType: "application/json; charset=utf-8",
                                    dataType: "json",
                                    url: '<?php echo esc_url_raw( rest_url() ); ?>dt/v1/saturation/load_by_country',
                                    beforeSend: function(xhr) {
                                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ) ?>');
                                    },
                                })
                                    .done(function (data) {
                                        button.empty().append('Load')
                                        let result_div = jQuery('#results')
                                        result_div.empty()
                                        result_div.append('<span style="float:right;"><a href="javascript:void(0);" ' +
                                            'onclick="jQuery(\'.subdivision\').toggle();">collapse/expand all subdivisions</a>' +
                                            '</span><span id="toggle-all"></span><br clear="all" />')

                                        jQuery.each(data, function(i,v) {
                                            result_div.append( '<hr><dt><strong style="font-size:1.4em">' + v.name + '</strong> ' +
                                                '<a id="admin1-link-'+v.geonameid+'" class="page-title-action" onclick="install_admin1_geoname(\''+v.geonameid+'\'); jQuery(this).off(\'click\');">Install</a> ' +
                                                '<span id="install-'+v.geonameid+'"></span>  <span style="float:right;">' +
                                                '<a href="javascript:void(0);" onclick="jQuery(\'.adm2-'+v.geonameid+'\').toggle()">collapse/expand</a>' +
                                                '</span></dt>')

                                            jQuery.each(v.adm2, function(ii, vv) {
                                                result_div.append('<dd class="adm2-'+v.geonameid+' subdivision"><strong>' + vv.name + '</strong> ' +
                                                    '<button type="button" id="button-'+vv.geonameid+'" class="page-title-action" onclick="install_admin2_geoname(\''+vv.geonameid+'\');" >Install</button> ' +
                                                    '<span id="install-'+vv.geonameid+'"></span> <a class="page-title-action" ' +
                                                    'onclick="install_all_cities(\''+vv.geonameid+'\')">Install All Cities</a> ' +
                                                    '<span id="cities-'+vv.geonameid+'"></span></dd>')
                                            })

                                        })

                                        console.log( 'success ')
                                        console.log( data )
                                    })
                                    .fail(function (err) {
                                        console.log("error");
                                        console.log(err);
                                    })
                            }
                            function install_admin2_geoname( geonameid ) {
                                console.log('install_geoname')

                                jQuery('#button-'+ geonameid ).prop("disabled",true)

                                let report_span = jQuery( '#install-' + geonameid )
                                report_span.append(' <span><img src="<?php echo plugin_dir_url( __FILE__ ). '/'; ?>spinner.svg" width="12px" /></span>')

                                let data = { "geonameid": geonameid }
                                jQuery.ajax({
                                    type: "POST",
                                    data: JSON.stringify(data),
                                    contentType: "application/json; charset=utf-8",
                                    dataType: "json",
                                    url: '<?php echo esc_url_raw( rest_url() ); ?>dt/v1/saturation/install_admin2_geoname',
                                    beforeSend: function(xhr) {
                                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ) ?>');
                                    },
                                })
                                    .done(function (data) {
                                        report_span.empty().append('&#9989;')
                                        load_current_locations()

                                        console.log( 'success for ' + geonameid)
                                        console.log( data )
                                    })
                                    .fail(function (err) {
                                        report_span.empty().append('( oops. something failed. )')
                                        console.log("error for " + geonameid );
                                        console.log(err);
                                    })
                            }
                            function install_admin1_geoname( geonameid ) {
                                console.log('install_geoname')

                                jQuery('#button-'+ geonameid ).prop("disabled",true)

                                let report_span = jQuery( '#install-' + geonameid )
                                report_span.append(' <span><img src="<?php echo plugin_dir_url( __FILE__ ). '/'; ?>spinner.svg" width="12px" /></span>')

                                let data = { "geonameid": geonameid }
                                jQuery.ajax({
                                    type: "POST",
                                    data: JSON.stringify(data),
                                    contentType: "application/json; charset=utf-8",
                                    dataType: "json",
                                    url: '<?php echo esc_url_raw( rest_url() ); ?>dt/v1/saturation/install_admin1_geoname',
                                    beforeSend: function(xhr) {
                                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ) ?>');
                                    },
                                })
                                    .done(function (data) {
                                        report_span.empty().append('&#9989;')
                                        load_current_locations()

                                        console.log( 'success for ' + geonameid)
                                        console.log( data )
                                    })
                                    .fail(function (err) {
                                        report_span.empty().append('( oops. something failed. )')
                                        console.log("error for " + geonameid );
                                        console.log(err);
                                    })
                            }
                            function install_admin1_next_levels( geonameid ){
                                console.log('install_admin1_next_levels')
                                console.log('Get geoname record and install. Check for parent and install. Get list of children admin2 and install.')
                            }
                            function install_all_cities( geonameid ) {
                                console.log('install_all_cities')
                                console.log('Get admin2 geo name. Download places file. Install places file. Log install. Get list of places and install all places.')
                            }
                            function load_current_locations() {
                                let current_locations = jQuery('#current-locations')
                                return jQuery.ajax({
                                    type: "POST",
                                    contentType: "application/json; charset=utf-8",
                                    dataType: "json",
                                    url: '<?php echo esc_url_raw( rest_url() ); ?>dt/v1/saturation/load_current_locations',
                                    beforeSend: function(xhr) {
                                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ) ?>');
                                    },
                                })
                                    .done(function (data) {
                                        current_locations.empty().append(data)
                                    })
                                    .fail(function (err) {
                                        console.log("error");
                                        console.log(err);
                                    })
                            }
                            jQuery(document).ready(function() {
                                load_current_locations()
                            })

                        </script>
                        <style>
                            dd, li {
                                margin-bottom: 15px;
                            }
                            dt, li {
                                margin-bottom: 20px;
                                margin-top: 20px;
                            }
                            #results .page-title-action {
                                vertical-align: middle;
                            }
                            .disabled-grey {
                                color: grey;
                                background: lightgrey;
                            }
                        </style>
                        <div id="results"></div>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
            <th>Current Locations</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    <div id="current-locations"></div>
                    <hr>
                    <a href="<?php echo esc_url( admin_url( '/edit.php?post_type=locations' ) ) ?>">View Locations</a>
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }
}

/**
 * Class DT_Starter_Tab_Second
 */
class DT_Saturation_Mapping_Tab_Configure_Network
{
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->partner_profile_metabox() ?>
                        <?php $this->population_metabox() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function population_metabox() {
        // process post action
        if ( isset( $_POST['population_division'] ) && ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'population_division'.get_current_user_id() ) ) ) {
            $new = (int) sanitize_text_field( wp_unslash( $_POST['population_division'] ) );
            update_option( 'dt_saturation_mapping_pd', $new, false );
        }
        $population_division = get_option( 'dt_saturation_mapping_pd' );
        if ( empty( $population_division ) ) {
            update_option( 'dt_saturation_mapping_pd', 5000, false );
            $population_division = 5000;
        }
        ?>
        <!-- Box -->
        <form method="post">
            <table class="widefat striped">
                <thead>
                <th>Groups Per Population</th>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php wp_nonce_field( 'population_division'.get_current_user_id() ); ?>
                        <label for="population_division">Size of population for each group: </label>
                        <input type="number" class="text" id="population_division" name="population_division" value="<?php echo esc_attr( $population_division ); ?>" /><br>
                        <p><em>Default is a population of 5,000 for each group. This must be a number and must not be blank. </em></p>
                        <button type="submit" class="button">Update</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }

    public function partner_profile_metabox() {
        // process post action
        if ( isset( $_POST['partner_profile_form'] ) && ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'partner_profile'.get_current_user_id() ) ) ) {

            $partner_profile = [
                'partner_name' => sanitize_text_field( wp_unslash( $_POST['partner_name'] ) ),
                'partner_description' => sanitize_text_field( wp_unslash( $_POST['partner_description'] ) ),
                'partner_id' => sanitize_text_field( wp_unslash( $_POST['partner_id'] ) ),
            ];
            update_option( 'dt_site_partner_profile', $partner_profile, false );

        }
        $partner_profile = get_option( 'dt_site_partner_profile' );


        ?>
        <!-- Box -->
        <form method="post">
            <?php wp_nonce_field( 'partner_profile'.get_current_user_id() ); ?>
            <table class="widefat striped">
                <thead>
                <th>Your Partner Profile</th>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <table class="widefat">
                            <tbody>
                            <tr>
                                <td><label for="partner_name">Your Group Name</label></td>
                                <td><input type="text" class="regular-text" name="partner_name"
                                           id="partner_name" value="<?php echo $partner_profile['partner_name'] ?>" /></td>
                            </tr>
                            <tr>
                                <td><label for="partner_description">Your Group Description</label></td>
                                <td><input type="text" class="regular-text" name="partner_description"
                                           id="partner_description" value="<?php echo $partner_profile['partner_description'] ?>" /></td>
                            </tr>
                            <tr>
                                <td><label for="partner_id">Site ID</label></td>
                                <td><?php echo $partner_profile['partner_id'] ?>
                                    <input type="hidden" class="regular-text" name="partner_id"
                                           id="partner_id" value="<?php echo $partner_profile['partner_id'] ?>" /></td>
                            </tr>
                            </tbody>
                        </table>

                        <p><br>
                            <button type="submit" id="partner_profile_form" name="partner_profile_form" class="button">Update</button>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }
}

