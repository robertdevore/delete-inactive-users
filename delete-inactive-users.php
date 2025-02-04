<?php

/**
  * The plugin bootstrap file
  *
  * @link              https://robertdevore.com
  * @since             1.0.0
  * @package           Delete_Inactive_Users
  *
  * @wordpress-plugin
  *
  * Plugin Name: Delete Inactive Users
  * Description: Deletes users based on role and inactivity date.
  * Plugin URI:  https://github.com/robertdevore/delete-inactive-users/
  * Version:     1.0.1
  * Author:      Robert DeVore
  * Author URI:  https://robertdevore.com/
  * License:     GPL-2.0+
  * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
  * Text Domain: delete-inactive-users
  * Domain Path: /languages
  * Update URI:  https://github.com/robertdevore/delete-inactive-users/
  */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

require 'includes/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/robertdevore/delete-inactive-users/',
	__FILE__,
	'delete-inactive-users'
);

// Set the branch that contains the stable release.
$myUpdateChecker->setBranch( 'main' );

/**
 * Current plugin version.
 */
define( 'DIU_VERSION', '1.0.1' );

// Check if Composer's autoloader is already registered globally.
if ( ! class_exists( 'RobertDevore\WPComCheck\WPComPluginHandler' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use RobertDevore\WPComCheck\WPComPluginHandler;

new WPComPluginHandler( plugin_basename( __FILE__ ), 'https://robertdevore.com/why-this-plugin-doesnt-support-wordpress-com-hosting/' );

/**
 * Load plugin text domain for translations
 * 
 * @since  1.0.1
 * @return void
 */
function diu_load_textdomain() {
    load_plugin_textdomain( 
        'delete-inactive-users',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );
}
add_action( 'plugins_loaded', 'diu_load_textdomain' );

/**
 * Add settings page to the admin menu.
 * 
 * @since  1.0.0
 * @return void
 */
function diu_add_settings_page() {
    add_submenu_page(
        'tools.php',
        __( 'Delete Inactive Users', 'delete-inactive-users' ),
        __( 'Delete Users', 'delete-inactive-users' ),
        'manage_options',
        'delete-inactive-users',
        'diu_render_settings_page'
    );
}
add_action( 'admin_menu', 'diu_add_settings_page' );

/**
 * Enqueue scripts for the settings page.
 * 
 * @since  1.0.0
 * @return void
 */
function diu_enqueue_scripts( $hook ) {
    if ( 'tools_page_delete-inactive-users' !== $hook ) {
        return;
    }

    wp_enqueue_script(
        'diu-async-delete',
        plugin_dir_url( __FILE__ ) . 'js/async-delete.js',
        [ 'jquery', 'jquery-ui-datepicker' ],
        '1.6.0',
        true
    );

    wp_enqueue_style(
        'diu-styles',
        plugin_dir_url( __FILE__ ) . 'css/diu-styles.css',
        [],
        '1.6.0'
    );

    wp_localize_script(
        'diu-async-delete',
        'diu_ajax_params',
        [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'diu_delete_users' )
        ]
    );

    wp_enqueue_style(
        'jquery-ui-style',
        plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css',
        [],
        null
    );
}
add_action( 'admin_enqueue_scripts', 'diu_enqueue_scripts' );

/**
 * Render the settings page.
 * 
 * @since  1.0.0
 * @return void
 */
function diu_render_settings_page() {
    $roles = get_editable_roles();
    ?>
    <div class="wrap">
        <h1 class="text-2xl font-bold mb-6 text-center">
            <?php esc_html_e( 'Delete Inactive Users', 'delete-inactive-users' ); ?>
            <a id="diu-support-btn" href="https://robertdevore.com/contact/" target="_blank" class="button button-alt" style="margin-left: 10px;">
                <span class="dashicons dashicons-format-chat" style="vertical-align: middle;"></span> <?php esc_html_e( 'Support', 'delete-inactive-users' ); ?>
            </a>
            <a id="diu-docs-btn" href="https://robertdevore.com/articles/delete-inactive-users/" target="_blank" class="button button-alt" style="margin-left: 5px;">
                <span class="dashicons dashicons-media-document" style="vertical-align: middle;"></span> <?php esc_html_e( 'Documentation', 'delete-inactive-users' ); ?>
            </a>
        </h1>
        <div class="diu-wrap bg-white shadow-lg rounded-lg p-8 max-w-3xl mx-auto">
            <form id="diu-delete-users-form" class="space-y-6">
                <div>
                    <label for="diu-user-role" class="block text-sm font-medium text-gray-700">User Role:</label>
                    <select id="diu-user-role" name="user_role" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <?php foreach ( $roles as $role_key => $role_details ) : ?>
                            <option value="<?php echo esc_attr( $role_key ); ?>">
                                <?php echo esc_html( $role_details['name'] ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="diu-cutoff-date" class="block text-sm font-medium text-gray-700">Cutoff Date:</label>
                    <input type="text" id="diu-cutoff-date" name="cutoff_date" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" placeholder="YYYY-MM-DD" />
                </div>
                <div>
                    <button id="diu-start-delete" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" type="button">
                        <?php esc_html_e( 'Start Deletion', 'delete-inactive-users' ); ?>
                    </button>
                </div>
            </form>
            <div id="diu-progress" class="mt-6 hidden">
                <div class="text-gray-700 mb-2" id="diu-status-message"></div>
                <progress value="0" max="100" id="diu-progress-bar" class="w-full h-4"></progress>
            </div>
            <div id="diu-success-message" class="hidden text-green-600 mt-4"></div>
        </div>
    </div>
    <?php
}

/**
 * Prepare the batch of users for deletion.
 * 
 * @since  1.0.0
 * @return void
 */
function diu_prepare_deletion() {
    check_ajax_referer( 'diu_delete_users', 'nonce' );

    $role        = sanitize_text_field( $_POST['user_role'] );
    $cutoff_date = sanitize_text_field( $_POST['cutoff_date'] );

    if ( empty( $role ) || empty( $cutoff_date ) ) {
        wp_send_json_error( __( 'Invalid parameters.', 'delete-inactive-users' ) );
    }

    // Ensure the cutoff_date is in the format 'YYYY-MM-DD HH:MM:SS' for comparison.
    $cutoff_datetime = date( 'Y-m-d H:i:s', strtotime( $cutoff_date . ' 00:00:00' ) );

    $user_query = new WP_User_Query(
        [
            'role'    => $role,
            'fields'  => 'ID',
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key'     => 'last_login',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => 'last_login',
                    'value'   => $cutoff_datetime,
                    'compare' => '<',
                    'type'    => 'DATETIME',
                ]
            ]
        ]
    );

    $user_ids = $user_query->get_results();

    if ( ! empty( $user_ids ) ) {
        set_transient( 'diu_user_ids_to_delete', $user_ids, HOUR_IN_SECONDS );
        wp_send_json_success( [ 'total' => count( $user_ids ) ] );
    } else {
        wp_send_json_error( __( 'No users found for deletion.', 'delete-inactive-users' ) );
    }
}
add_action( 'wp_ajax_diu_prepare_deletion', 'diu_prepare_deletion' );

/**
 * Process a batch of users.
 * 
 * @since  1.0.0
 * @return void
 */
function diu_process_batch() {
    check_ajax_referer( 'diu_delete_users', 'nonce' );

    $user_ids = get_transient( 'diu_user_ids_to_delete' );

    if ( empty( $user_ids ) ) {
        wp_send_json_error( __( 'No users left to delete.', 'delete-inactive-users' ) );
    }

    $batch_size = 50;
    $batch      = array_splice( $user_ids, 0, $batch_size );

    foreach ( $batch as $user_id ) {
        wp_delete_user( $user_id );
    }

    set_transient( 'diu_user_ids_to_delete', $user_ids, HOUR_IN_SECONDS );

    $remaining  = count( $user_ids );
    $total      = isset( $_POST['total_users' ] ) ? (int) $_POST['total_users'] : $remaining + $batch_size;
    $processed  = $total - $remaining;
    $percentage = $total > 0 ? floor( ( $processed / $total ) * 100 ) : 0;

    wp_send_json_success( [ 'remaining' => $remaining, 'processed' => $processed, 'percentage' => $percentage ] );
}
add_action( 'wp_ajax_diu_process_batch', 'diu_process_batch' );

/**
 * Track user last login time.
 * 
 * @since  1.0.0
 * @return void
 */
function diu_track_last_login( $user_login, $user ) {
    update_user_meta( $user->ID, 'last_login', current_time( 'timestamp' ) );
}
add_action( 'wp_login', 'diu_track_last_login', 10, 2 );
