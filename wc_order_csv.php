<?php
/**
 * Plugin Name: WooCommerce Order CSV Export
 * Plugin URI:  https://yourwebsite.com/
 * Description: Allows customers to download their WooCommerce orders as a CSV file.
 * Version:     1.0.0
 * Author:      Your Name
 * Author URI:  https://yourwebsite.com/
 * License:     GPL2
 * Text Domain: wc-order-csv-export
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WC_Order_CSV_Export {

    public function __construct() {
        // Hook to display the download button on order details page
        add_action('woocommerce_order_details_after_order_table', [$this, 'add_csv_download_link']);

        // Hook to process CSV download
        add_action('init', [$this, 'handle_csv_download']);
    }

    /**
     * Adds a "Download CSV" button on the order details page for completed orders.
     */
    public function add_csv_download_link($order) {
        if ($order->has_status('completed')) {
            $order_id = $order->get_id();
            $csv_url = wp_nonce_url(add_query_arg('order_csv_download', $order_id, home_url()), 'download_order_csv_' . $order_id);
            echo '<p class="download-csv"><a href="' . esc_url($csv_url) . '" class="button">Download CSV</a></p>';
        }
    }

    /**
     * Handles the CSV download request.
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
            $wholesale_price = get_post_meta($product->get_id(), 'wcwp_wholesale', true); // Assuming wholesale price is stored in meta
            $quantity = $item->get_quantity();
            $barcode = get_post_meta($product->get_id(), '_global_unique_id', true);

            // Write data to CSV
            fputcsv($output, [$sku, $product_name, $regular_price, $wholesale_price, $quantity, $barcode]);
        }

        fclose($output);

        // Get the output buffer contents
        $csv_data = ob_get_clean();

        // Send headers to force download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="order_' . $order_id . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $csv_data;
        exit;
    }
}

// Initialize the plugin
new WC_Order_CSV_Export();
