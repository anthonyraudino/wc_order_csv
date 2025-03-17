<?php
/**
 * Plugin Name: WooCommerce Order CSV Export
 * Plugin URI:  https://anthonyraudino.com/
 * Description: Allows customers and shop managers to download their WooCommerce orders as a CSV file.
 * Version:     1.2.1
 * Author:      Anthony Raudino
 * Author URI:  https://anthonyraudino.com/
 * License:     GPL2
 * Text Domain: wc-order-csv-export
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WC_Order_CSV_Export {

    public function __construct() {
        // Hook to display the download button on order details page for customers
        add_action('woocommerce_order_details_after_order_table', [$this, 'add_csv_download_link']);

        // Hook to process CSV download for customers
        add_action('init', [$this, 'handle_csv_download']);

        // Add "Download CSV" to the quick actions in admin
        add_filter('bulk_actions-edit-shop_order', [$this, 'add_admin_download_csv_action']);
        add_action('admin_action_download_order_csv', [$this, 'handle_admin_csv_download']);

        // Hook to add a "Download CSV" button to the order edit page in the admin area
add_action('woocommerce_admin_order_actions_end', [$this, 'add_admin_order_page_download_button']);
    }

    /**
     * Adds a "Download CSV" button on the order details page for completed orders (for customers).
     */
    public function add_csv_download_link($order) {
        if (get_current_user_id() === $order->get_user_id()) {
            // Show the button for customers only if the order is completed
            if ($order->has_status('completed')) {
                $order_id = $order->get_id();
                $csv_url = wp_nonce_url(add_query_arg('order_csv_download', $order_id, home_url()), 'download_order_csv_' . $order_id);
                echo '<p class="download-csv"><a href="' . esc_url($csv_url) . '" class="button">Download CSV</a></p>';
            }
        }
    }

    /**
     * Handles the CSV download request for customers.
     */
    public function handle_csv_download() {
        if (isset($_GET['order_csv_download'])) {
            $order_id = intval($_GET['order_csv_download']);

            // Security check: Verify nonce
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'download_order_csv_' . $order_id)) {
                wp_die(__('Security check failed.', 'wc-order-csv-export'));
            }

            // Ensure user has permission to download their own order CSV
            $order = wc_get_order($order_id);
            if (!$order || $order->get_user_id() != get_current_user_id()) {
                wp_die(__('You do not have permission to download this file.', 'wc-order-csv-export'));
            }

            // Ensure the order is completed
            if (!$order->has_status('completed')) {
                wp_die(__('You can only download the CSV for completed orders.', 'wc-order-csv-export'));
            }

            // Generate and export CSV
            $this->generate_csv($order_id);
        }
    }

    /**
     * Generates and forces the download of a CSV file for a given order.
     */
    private function generate_csv($order_id) {
    // Get the order
    $order = wc_get_order($order_id);
    if (!$order) return;

    // Retrieve the order number (this might be different from the post ID)
    $order_number = $order->get_order_number();

    // Define CSV headers
    $csv_headers = ['SKU', 'Product Name', 'RRP', 'Wholesale Price', 'Quantity', 'Barcode'];

    // Open output buffer
    ob_start();
    $output = fopen('php://output', 'w');

    // Write CSV header
    fputcsv($output, $csv_headers);

    // Loop through order items
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();

        // Get product data
        $sku = $product->get_sku();
        $product_name = $product->get_name();
        $regular_price = $product->get_regular_price();
        $wholesale_price = get_post_meta($product->get_id(), 'wcwp_wholesale', true);
        $quantity = $item->get_quantity();
        $barcode = get_post_meta($product->get_id(), '_global_unique_id', true);

        // Write data to CSV
        fputcsv($output, [$sku, $product_name, $regular_price, $wholesale_price, $quantity, $barcode]);
    }

    fclose($output);

    // Get the output buffer contents
    $csv_data = ob_get_clean();

    // Send headers to force download with the order number in the filename
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="order_' . $order_number . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo $csv_data;
    exit;
}

    /**
     * Adds a custom action to the bulk actions dropdown in the admin orders page.
     */
    public function add_admin_download_csv_action($actions) {
        $actions['download_order_csv'] = __('Download Order CSV', 'wc-order-csv-export');
        return $actions;
    }

    /**
     * Handles the CSV download action for shop managers from the admin orders page.
     */
    public function handle_admin_csv_download() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to perform this action.', 'wc-order-csv-export'));
        }

        if (isset($_GET['post'])) {
            $order_id = intval($_GET['post']);
            $this->generate_csv($order_id);
        }

        // Redirect back to the orders page after download
        wp_redirect(admin_url('edit.php?post_type=shop_order'));
        exit;
    }

    /**
     * Adds a "Download CSV" button to the order actions section in the admin order details page.
     */
public function add_admin_order_page_download_button($order) {
    if ( current_user_can('manage_woocommerce') ) {
        $order_id = $order->get_id();
		$icon_url = plugin_dir_url( __FILE__ ) . 'assets/file-csv-solid.svg';
        $csv_url  = wp_nonce_url( admin_url('admin.php?action=download_order_csv&post=' . $order_id), 'download_order_csv_' . $order_id );
        $svg_icon = '<img style="width: 1.2em !important; height: 1.2em !important; margin: 0 auto; margin-top: 0.5em; fill: currentColor;" src="' . esc_url( $icon_url ) . '" class="csvicon" />';
        echo '<a href="' . esc_url( $csv_url ) . '" class="button">' . $svg_icon . ' ' . __( '', 'wc-order-csv-export' ) . '</a>';
    }
}

}

// Initialize the plugin
new WC_Order_CSV_Export();