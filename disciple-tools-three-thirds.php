<?php
/**
 * Plugin Name: Disciple.Tools - 3/3rds Meetings
 * Plugin URI: https://github.com/thecodezone/disciple-tools-three-thirds
 * Description: Disciple.Tools - Provides 3/3rds meeting administration, facilitation, and tracking.
 * Text Domain: disciple-tools-three-thirds
 * Domain Path: /languages
 * Version:  0.1
 * Author URI: https://codezone.io
 * GitHub Plugin URI: https://github.com/thecodezone/disciple-tools-three-thirds
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 5.6
 *
 * @package Disciple_Tools
 * @link    https://github.com/DiscipleTools
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Gets the instance of the `DT_33` class.
 *
 * @return object|bool
 * @since  0.1
 * @access public
 */
function disciple_tools_three_thirds() {
    $disciple_tools_three_thirds_required_dt_theme_version = '1.19';
    $wp_theme = wp_get_theme();
    $version = $wp_theme->version;

    /*
     * Check if the Disciple.Tools theme is loaded and is the latest required version
     */
    $is_theme_dt = class_exists( "Disciple_Tools" );
    if ( $is_theme_dt && version_compare( $version, $disciple_tools_three_thirds_required_dt_theme_version, "<" ) ) {
        add_action( 'admin_notices', 'disciple_tools_three_thirds_hook_admin_notice' );
        add_action( 'wp_ajax_dismissed_notice_handler', 'dt_hook_ajax_notice_handler' );
        return false;
    }
    if ( !$is_theme_dt ) {
        return false;
    }
    /**
     * Load useful function from the theme
     */
    if ( !defined( 'DT_FUNCTIONS_READY' ) ) {
        require_once get_template_directory() . '/dt-core/global-functions.php';
    }

    return DT_33::instance();

}

add_action( 'after_setup_theme', 'disciple_tools_three_thirds', 20 );

/**
 * Singleton class for setting up the plugin.
 *
 * @since  0.1
 * @access public
 */
class DT_33 {
    const DOMAIN = "disciple_tools_three_thirds";
    public static $url;
    public static $dir;

    private static $_instance = null;

    public static function instance() {
        static::$url = plugin_dir_url( __FILE__ );
        static::$dir = plugin_dir_path( __FILE__ );

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        require_once 'repositories/meetings-repository.php';
        require_once 'repositories/groups-repository.php';
        require_once 'services/translations.php';
        require_once 'services/utilities.php';
        require_once 'services/auth.php';
        require_once 'meeting-type/meeting-type.php';
        require_once 'magic-link/transformers.php';
        require_once 'magic-link/magic-link.php';
        require_once 'magic-link/app.php';
        require_once 'magic-link/login.php';
        require_once 'magic-link/redirect.php';
        $this->i18n();
        if ( is_admin() ) { // adds starter admin page and section for plugin
            require_once 'admin/admin-menu-and-tabs.php';
            add_filter( 'plugin_row_meta', [ $this, 'plugin_settings_links' ], 10, 4 );
        }
    }

    /**
     * Filters the array of row meta for each/specific plugin in the Plugins list table.
     * Appends additional links below each/specific plugin on the plugins page.
     */
    public function plugin_description_links( $links_array, $plugin_file_name, $plugin_data, $status ) {
        if ( strpos( $plugin_file_name, basename( __FILE__ ) ) ) {
            $links_array[] = '<a href="https://codezone.io">CodeZone</a>';
        }

        return $links_array;
    }
    public function plugin_settings_links( $links_array, $plugin_file_name, $plugin_data, $status ) {
        if ( strpos( $plugin_file_name, basename( __FILE__ ) ) ) {
            $links_array[] = '<a href="admin.php?page=dt33_settings">Settings</a>';
        }

        return $links_array;
    }


    /**
     * Method that runs only when the plugin is activated.
     *
     * @return void
     * @since  0.1
     * @access public
     */
    public static function activation() {
        // add elements here that need to fire on activation
    }

    /**
     * Method that runs only when the plugin is deactivated.
     *
     * @return void
     * @since  0.1
     * @access public
     */
    public static function deactivation() {
        // add functions here that need to happen on deactivation
        delete_option( 'dismissed-disciple-tools-three-thirds' );
    }

    /**
     * Loads the translation files.
     *
     * @return void
     * @since  0.1
     * @access public
     */
    public function i18n() {
        $domain = 'disciple-tools-three-thirds';
        load_plugin_textdomain( $domain, false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) . 'languages' );
    }

    /**
     * Magic method to output a string if trying to use the object as a string.
     *
     * @return string
     * @since  0.1
     * @access public
     */
    public function __toString() {
        return 'disciple-tools-three-thirds';
    }

    /**
     * Magic method to keep the object from being cloned.
     *
     * @return void
     * @since  0.1
     * @access public
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }

    /**
     * Magic method to keep the object from being unserialized.
     *
     * @return void
     * @since  0.1
     * @access public
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }

    /**
     * Magic method to prevent a fatal error when calling a method that doesn't exist.
     *
     * @param string $method
     * @param array $args
     * @return null
     * @since  0.1
     * @access public
     */
    public function __call( $method = '', $args = [] ) {
        _doing_it_wrong( "disciple_tools_three_thirds::" . esc_html( $method ), 'Method does not exist.', '0.1' );
        unset( $method, $args );
        return null;
    }
}


// Register activation hook.
register_activation_hook( __FILE__, [ 'DT_33', 'activation' ] );
register_deactivation_hook( __FILE__, [ 'DT_33', 'deactivation' ] );


if ( !function_exists( 'disciple_tools_three_thirds_hook_admin_notice' ) ) {
    function disciple_tools_three_thirds_hook_admin_notice() {
        global $disciple_tools_three_thirds_required_dt_theme_version;
        $wp_theme = wp_get_theme();
        $current_version = $wp_theme->version;
        $message = "'Disciple.Tools - 3/3rds Meetings' plugin requires 'Disciple.Tools' theme to work. Please activate 'Disciple.Tools' theme or make sure it is latest version.";
        if ( $wp_theme->get_template() === "disciple-tools-theme" ) {
            $message .= ' ' . sprintf( esc_html( 'Current Disciple.Tools version: %1$s, required version: %2$s' ), esc_html( $current_version ), esc_html( $disciple_tools_three_thirds_required_dt_theme_version ) );
        }
        // Check if it's been dismissed...
        if ( !get_option( 'dismissed-disciple-tools-three-thirds', false ) ) { ?>
            <div class="notice notice-error notice-disciple-tools-three-thirds is-dismissible"
                 data-notice="disciple-tools-three-thirds">
                <p><?php echo esc_html( $message ); ?></p>
            </div>
            <script>
                jQuery(function ($) {
                    $(document).on('click', '.notice-disciple-tools-three-thirds .notice-dismiss', function () {
                        $.ajax(ajaxurl, {
                            type: 'POST',
                            data: {
                                action: 'dismissed_notice_handler',
                                type: 'disciple-tools-three-thirds',
                                security: '<?php echo esc_html( wp_create_nonce( 'wp_rest_dismiss' ) ) ?>'
                            }
                        })
                    });
                });
            </script>
        <?php }
    }
}

/**
 * AJAX handler to store the state of dismissible notices.
 */
if ( !function_exists( "dt_hook_ajax_notice_handler" ) ) {
    function dt_hook_ajax_notice_handler() {
        check_ajax_referer( 'wp_rest_dismiss', 'security' );
        if ( isset( $_POST["type"] ) ) {
            $type = sanitize_text_field( wp_unslash( $_POST["type"] ) );
            update_option( 'dismissed-' . $type, true );
        }
    }
}

/**
 * Plugin Releases and updates
 *
 * This section runs the remote plugin updating service, so you can issue distributed updates to your plugin
 *
 * @note See the instructions for version updating to understand the steps involved.
 * @link https://github.com/DiscipleTools/disciple-tools-three-thirds/wiki/Configuring-Remote-Updating-System
 *
 */

/**
 * Check for plugin updates even when the active theme is not Disciple.Tools
 *
 * Below is the publicly hosted .json file that carries the version information. This file can be hosted
 * anywhere as long as it is publicly accessible. You can download the version file listed below and use it as
 * a template.
 * Also, see the instructions for version updating to understand the steps involved.
 * @see https://github.com/DiscipleTools/disciple-tools-version-control/wiki/How-to-Update-the-Starter-Plugin
 */
add_action( 'plugins_loaded', function () {
    if ( is_admin() && !( is_multisite() && class_exists( "DT_Multisite" ) ) || wp_doing_cron() ) {
        // Check for plugin updates
        if ( !class_exists( 'Puc_v4_Factory' ) ) {
            if ( file_exists( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' ) ) {
                require( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' );
            }
        }
        if ( class_exists( 'Puc_v4_Factory' ) ) {
            Puc_v4_Factory::buildUpdateChecker(
                'https://raw.githubusercontent.com/TheCodeZone/disciple-tools-three-thirds/master/version-control.json',
                __FILE__,
                'disciple-tools-three-thirds'
            );

        }
    }
} );
