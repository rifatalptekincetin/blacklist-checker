<?php
/*
Plugin Name: Blacklist Checker
Description: Check if an IP address or domain is blacklisted across various spam databases.
Version: 1.0
Author: ALPX
*/

// Enqueue necessary scripts and localize translations
function enqueue_blacklist_checker_scripts() {
    wp_enqueue_script('blacklist-checker-script', plugin_dir_url(__FILE__) . 'blacklist-checker.js', array('jquery'), '1.0', true);

    // Localization for JavaScript
    wp_localize_script('blacklist-checker-script', 'blacklist_vars', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('blacklist_nonce')
    ));

    // Localization for JavaScript translations
    wp_localize_script('blacklist-checker-script', 'blacklistTranslations', array(
        'checking'        => __('Checking...', 'blacklist-checker'),
        'ipCheckResult'   => __('IP Address Check Result for', 'blacklist-checker'),
        'domainCheckResult' => __('Domain Check Result for', 'blacklist-checker'),
        'error'           => __('Error', 'blacklist-checker'),
        'ajaxError'       => __('Ajax error', 'blacklist-checker')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_blacklist_checker_scripts');

// Ajax handler for Blacklist check
add_action('wp_ajax_blacklist_check', 'blacklist_check_callback');
add_action('wp_ajax_nopriv_blacklist_check', 'blacklist_check_callback');

function blacklist_check_callback() {
    check_ajax_referer('blacklist_nonce', 'security');

    $ip_or_domain = isset($_POST['ip_or_domain']) ? sanitize_text_field($_POST['ip_or_domain']) : '';

    if (!empty($ip_or_domain)) {
        $results = perform_blacklist_check($ip_or_domain);
        wp_send_json_success($results);
    } else {
        wp_send_json_error(__('Invalid input provided.', 'blacklist-checker'));
    }

    wp_die();
}

// Function to perform blacklist check
function perform_blacklist_check($ip_or_domain) {
    $results = array();

    $spamhaus_result = dns_get_record($ip_or_domain . '.zen.spamhaus.org', DNS_A);
    if (!empty($spamhaus_result)) {
        $results[] = array(
            'service' => 'Spamhaus',
            'result' => 'Listed'
        );
    } else {
        $results[] = array(
            'service' => 'Spamhaus',
            'result' => 'Not Listed'
        );
    }

    $barracuda_result = dns_get_record($ip_or_domain . '.b.barracudacentral.org', DNS_A);
    if (!empty($barracuda_result)) {
        $results[] = array(
            'service' => 'Barracuda',
            'result' => 'Listed'
        );
    } else {
        $results[] = array(
            'service' => 'Barracuda',
            'result' => 'Not Listed'
        );
    }

    $surbl_result = dns_get_record($ip_or_domain . '.multi.surbl.org', DNS_A);
    if (!empty($surbl_result)) {
        $results[] = array(
            'service' => 'SURBL',
            'result' => 'Listed'
        );
    } else {
        $results[] = array(
            'service' => 'SURBL',
            'result' => 'Not Listed'
        );
    }

    $sorbs_result = dns_get_record($ip_or_domain . '.dnsbl.sorbs.net', DNS_A);
    if (!empty($sorbs_result)) {
        $results[] = array(
            'service' => 'SORBS',
            'result' => 'Listed'
        );
    } else {
        $results[] = array(
            'service' => 'SORBS',
            'result' => 'Not Listed'
        );
    }

    return $results;
}

// Shortcode for Blacklist check form
function blacklist_check_shortcode() {
    ob_start();
    ?>
    <div id="blacklist-check-form">
        <input type="text" id="ip-or-domain" placeholder="<?php echo esc_attr(__('Enter IP Address or Domain', 'blacklist-checker')); ?>">
        <button id="blacklist-check-button"><?php echo esc_html(__('Check Blacklist', 'blacklist-checker')); ?></button>
        <div id="blacklist-check-results"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('blacklist_check_form', 'blacklist_check_shortcode');
