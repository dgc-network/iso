<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Display custom fields on the "Add New User" screen
function user_custom_fields_on_new_user_form() {
    ?>
    <h2>Custom Fields</h2>
    <table class="form-table">
        <tr>
            <th><label for="dgc_wallet_balance">Wallet Balance</label></th>
            <td>
                <input
                    type="text"
                    value=""
                    name="dgc_wallet_balance"
                    id="dgc_wallet_balance"
                    class="regular-text"
                >
            </td>
        </tr>
        <tr>
            <th><label for="dgc_wallet_address">Wallet Address</label></th>
            <td>
                <input
                    type="text"
                    value=""
                    name="dgc_wallet_address"
                    id="dgc_wallet_address"
                    class="regular-text"
                >
            </td>
        </tr>
    </table>
    <?php
}
add_action('user_new_form', 'user_custom_fields_on_new_user_form');

// Save custom fields when a new user is registered
function save_user_custom_fields_on_registration($user_id) {
    if (isset($_POST['dgc_wallet_balance'])) {
        update_post_meta($user_id, 'dgc_wallet_balance', sanitize_text_field($_POST['dgc_wallet_balance']));
    }

    if (isset($_POST['dgc_wallet_address'])) {
        update_post_meta($user_id, 'dgc_wallet_address', sanitize_text_field($_POST['dgc_wallet_address']));
    }
}
add_action('user_register', 'save_user_custom_fields_on_registration');

function user_custom_fields(WP_User $user) {
    ?>
    <h2>Custom Fields</h2>
        <table class="form-table">
            <tr>
                <th><label for="dgc_wallet_balance">Wallet Balance</label></th>
                <td>
                    <input
                        type="text"
                        value="<?php echo esc_attr(get_user_meta($user->ID, 'dgc_wallet_balance', true)); ?>"
                        name="dgc_wallet_balance"
                        id="dgc_wallet_balance"
                        class="regular-text"
                    >
                </td>
            </tr>
            <tr>
                <th><label for="dgc_wallet_address">Wallet Address</label></th>
                <td>
                    <input
                        type="text"
                        value="<?php echo esc_attr(get_user_meta($user->ID, 'dgc_wallet_address', true)); ?>"
                        name="dgc_wallet_address"
                        id="dgc_wallet_address"
                        class="regular-text"
                    >
                </td>
            </tr>
            <tr>
                <th><label for="site-id">Site</label></th>
                <td>
                    <select id="site-id" name="_site_id" class="regular-text" >
                        <option value="">Select Site</option>
                    <?php
                        $site_id = esc_html(get_post_meta($user->ID, 'site_id', true));
                        $site_args = array(
                            'post_type'      => 'site',
                            'posts_per_page' => -1,
                        );
                        $sites = get_posts($site_args);    
                        foreach ($sites as $site) {
                            $selected = ($site_id == $site->ID) ? 'selected' : '';
                            echo '<option value="' . esc_attr($site->ID) . '" ' . $selected . '>' . esc_html($site->post_title) . '</option>';
                        }
                    ?>
                    </select>
                </td>
            </tr>
        </table>
    <?php
}
add_action('show_user_profile', 'user_custom_fields'); // editing your own profile
add_action('edit_user_profile', 'user_custom_fields'); // editing another user

function save_user_metadata($userId) {
    if (current_user_can('edit_user', $userId)) {
        update_post_meta($userId, 'dgc_wallet_balance', $_REQUEST['dgc_wallet_balance']);
        update_post_meta($userId, 'dgc_wallet_address', $_REQUEST['dgc_wallet_address']);
        update_post_meta($userId, 'site_id', $_REQUEST['_site_id']);
    }    
}
add_action('personal_options_update', 'save_user_metadata');
add_action('edit_user_profile_update', 'save_user_metadata');

function get_user_id_by_wallet_address($wallet_address='') {
    $query = new WP_Query( [
        'meta_key'     => 'dgc_wallet_address',
        'meta_compare' => $wallet_address,
    ] );
      
    foreach( $query->posts as $post ) {
        $post_id  = $post->ID;
        return $post_id;
        // ...
    }
}

function get_balance_by_wallet_address($wallet_address='') {
    $query = new WP_Query( [
        'meta_key'     => 'dgc_wallet_address',
        'meta_compare' => $wallet_address,
    ] );
      
    foreach( $query->posts as $post ) {
        $post_id  = $post->ID;
        $balance = get_post_meta( $post_id, 'dgc_wallet_balance', true );
        return $balance;
        // ...
    }
}

function set_amount_transfer_to_wallet_address($wallet_address='', $amount=0) {
    $current_user = wp_get_current_user();
    $query = new WP_Query( [
        'meta_key'     => 'dgc_wallet_address',
        'meta_compare' => $wallet_address,
    ] );
    //$post_id = 0;
    foreach( $query->posts as $post ) {
        $post_id  = $post->ID;
        $my_balance = get_post_meta( $current_user->ID, 'dgc_wallet_balance', true );
        $your_balance = get_post_meta( $post_id, 'dgc_wallet_balance', true );
        if ($my_balance>=$amount) {
            update_post_meta( $current_user->ID, 'dgc_wallet_balance', $my_balance-$amount );
            update_post_meta( $post_id, 'dgc_wallet_balance', $your_balance+$amount );
            insert_dgc_transaction(
                array(
                    'send_id'   => $current_user->ID,
                    'receive_id'=> $post_id,
                    'tx_amount' => $amount,
                )
            );
        }
    }
}

function set_amount_transfer_to_user($_tx_id='', $_tx_amount=0) {
    if ($_tx_id!=0){
        $current_user = wp_get_current_user();
        $my_balance = get_post_meta( $current_user->ID, 'dgc_wallet_balance', true );
        $your_balance = get_post_meta( $_tx_id, 'dgc_wallet_balance', true );
        if ($my_balance>=$_tx_amount) {
            update_post_meta( $current_user->ID, 'dgc_wallet_balance', $my_balance-$_tx_amount );
            update_post_meta( $post_id, 'dgc_wallet_balance', $your_balance+$_tx_amount );
            insert_dgc_transaction(
                array(
                    'send_id'   => $current_user->ID,
                    'receive_id'=> $_tx_id,
                    'tx_amount' => $_tx_amount,
                )
            );
        }
    }
}

// Register post type
function register_job_post_type() {
    $args = array(
        'public'        => true,
        'rewrite'       => array('slug' => 'jobs'),
        'supports'      => array( 'title', 'editor', 'custom-fields' ),
        'has_archive'   => true,
        'show_in_menu'  => false, // Set this to false to hide from the admin menu
    );
    register_post_type( 'job', $args );
}
add_action('init', 'register_job_post_type');

// Shortcode to display my jobs on frontend
function my_jobs_shortcode() {
    //ob_start(); // Start output buffering

    // Check if the user is logged in
    if (is_user_logged_in()) {
        $current_user_id = get_current_user_id();
        $site_id = esc_attr(get_post_meta($current_user_id, 'site_id', true));
        $user_data = get_userdata( $current_user_id );
        ?>
        <h2><?php echo __( 'My jobs', 'your-text-domain' );?></h2>
        <div class="ui-widget">
            <form method="post">
            <fieldset>
                <label for="display-name">Name : </label>
                <input type="text" id="display-name" name="_display_name" value="<?php echo $user_data->display_name;?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="site-title"> Site: </label>
                <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" disabled />
                <?php
                // My job list in site
                $query = retrieve_site_job_list_data($site_id);
                ?>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th></th>
                        <th>Job</th>
                        <th>Description</th>
                        <th></th>
                        <th></th>
                    </thead>
                    <tbody>
                <?php
                if ($query->have_posts()) :
                    $x = 0;
                    while ($query->have_posts()) : $query->the_post();
                        ?>
                            <tr id="my-job-list-<?php echo $x;?>">
                                <td style="text-align:center;"><input type="checkbox" id="check-my-job-<?php echo $x;?>" /></td>
                                <td style="text-align:center;"><?php echo get_the_title(get_the_ID());?></td>
                                <td><?php echo get_the_content(get_the_ID());?></td>
                                <td style="text-align:center;"><span id="btn-edit-site-job-<?php the_ID();?>" class="dashicons dashicons-edit"></span></td>
                                <td style="text-align:center;"><span id="btn-del-site-job-<?php the_ID();?>" class="dashicons dashicons-trash"></span></td>
                            </tr>
                        <?php 
                        $x += 1;
                    endwhile;
                    while ($x<50) {
                        echo '<tr id="my-job-list-'.$x.'" style="display:none;"></tr>';
                        $x += 1;
                    }
                    wp_reset_postdata();
                endif;
                ?>
                    </tbody>
                    <tr><td colspan="5"><div id="btn-new-site-job" style="border:solid; margin:3px; text-align:center; border-radius:5px">+</div></td></tr>
                </table>

                <div id="job-dialog" title="Job dialog" style="display:none;">
                    <fieldset>
                        <input type="hidden" id="job-id" />
                        <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
                        <label for="job-title">Title:</label>
                        <input type="text" id="job-title" class="text ui-widget-content ui-corner-all" />
                        <label for="job-content">Content:</label>
                        <input type="text" id="job-content" class="text ui-widget-content ui-corner-all" />
                    </fieldset>
                </div>
            </fieldset>
            </form>
        </div><?php

    } else {
        user_did_not_login_yet();
    }
    //return ob_get_clean(); // Return the buffered content
}
add_shortcode('my-jobs', 'my_jobs_shortcode');

function retrieve_site_job_list_data($site_id=0) {
    // Retrieve the value
    $args = array(
        'post_type'      => 'job',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'site_id',
                'value' => $site_id,
            ),
        ),
    );
    $query = new WP_Query($args);
    return $query;
}

function get_my_job_list_data() {
    // Retrieve the value
    $query = retrieve_site_job_list_data($_POST['_site_id']);

    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $_list = array();
            $_list["job_id"] = get_the_ID();
            $_list["job_title"] = get_the_title();
            $_list["job_content"] = get_the_content();
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata(); // Reset post data to the main loop
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_my_job_list_data', 'get_my_job_list_data' );
add_action( 'wp_ajax_nopriv_get_my_job_list_data', 'get_my_job_list_data' );

function new_site_job_data() {
    $current_user_id = get_current_user_id();
    // Set up the post data
    $new_post = array(
        'post_title'    => 'New job',
        'post_content'  => 'Your post content goes here.',
        'post_status'   => 'publish', // Publish the post immediately
        'post_author'   => $current_user_id, // Use the user ID of the author
        'post_type'     => 'job', // Change to your custom post type if needed
    );    
    // Insert the post into the database
    $post_id = wp_insert_post($new_post);
    
    // Check if the post was successfully inserted
    if ($post_id) {
        // Add metadata to the post
        update_post_meta($post_id, 'site_id', $_POST['_site_id']);
        //update_post_meta($post_id, 'next_action_leadtime', 86400); // Assume the default is 1 day
    }
    wp_send_json($post_id);
}
add_action( 'wp_ajax_new_site_job_data', 'new_site_job_data' );
add_action( 'wp_ajax_nopriv_new_site_job_data', 'new_site_job_data' );

function get_site_job_dialog_data() {
    $response = array();
    if( isset($_POST['_job_id']) ) {
        $job_id = (int)sanitize_text_field($_POST['_job_id']);
        $response["job_title"] = get_the_title($job_id);
        $response["job_content"] = get_the_content($job_id);
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_get_site_job_dialog_data', 'get_site_job_dialog_data' );
add_action( 'wp_ajax_nopriv_get_site_job_dialog_data', 'get_site_job_dialog_data' );

function set_site_job_dialog_data() {
    if( isset($_POST['_job_id']) ) {
        $data = array(
            'ID'           => $_POST['_job_id'],
            'post_title'   => $_POST['_job_title'],
            'post_content' => $_POST['_job_content'],
        );
        wp_update_post( $data );
    } else {
        $current_user_id = get_current_user_id();
        // Set up the post data
        $new_post = array(
            'post_title'    => 'New job',
            'post_content'  => 'Your post content goes here.',
            'post_status'   => 'publish', // Publish the post immediately
            'post_author'   => $current_user_id, // Use the user ID of the author
            'post_type'     => 'job', // Change to your custom post type if needed
        );    
        // Insert the post into the database
        $post_id = wp_insert_post($new_post);
        update_post_meta( $post_id, 'site_id', sanitize_text_field($_POST['_site_id']));
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_site_job_dialog_data', 'set_site_job_dialog_data' );
add_action( 'wp_ajax_nopriv_set_site_job_dialog_data', 'set_site_job_dialog_data' );

function del_site_job_dialog_data() {
    // Delete the post
    $result = wp_delete_post($_POST['_job_id'], true); // Set the second parameter to true to force delete
    wp_send_json($result);
}
add_action( 'wp_ajax_del_site_job_dialog_data', 'del_site_job_dialog_data' );
add_action( 'wp_ajax_nopriv_del_site_job_dialog_data', 'del_site_job_dialog_data' );


