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
        update_user_meta($user_id, 'dgc_wallet_balance', sanitize_text_field($_POST['dgc_wallet_balance']));
    }

    if (isset($_POST['dgc_wallet_address'])) {
        update_user_meta($user_id, 'dgc_wallet_address', sanitize_text_field($_POST['dgc_wallet_address']));
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
        </table>
    <?php
}
add_action('show_user_profile', 'user_custom_fields'); // editing your own profile
add_action('edit_user_profile', 'user_custom_fields'); // editing another user

function save_user_metadata($userId) {
    if (current_user_can('edit_user', $userId)) {
        update_user_meta($userId, 'dgc_wallet_balance', $_REQUEST['dgc_wallet_balance']);
        update_user_meta($userId, 'dgc_wallet_address', $_REQUEST['dgc_wallet_address']);
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

// Shortcode to display User profile on frontend
function user_profile_shortcode() {
    ob_start(); // Start output buffering

    // Check if the user is logged in
    if (is_user_logged_in()) {
        $current_user_id = get_current_user_id();
        $site_id = esc_html(get_post_meta($current_user_id, 'site_id', true));

        if( isset($_POST['_user_submit']) ) {
            $user_data = wp_update_user( array( 
                'ID' => $current_user_id, 
                'display_name' => $_POST['_display_name'], 
                //'user_email' => $_POST['_user_email'], 
            ) );
            update_post_meta( $current_user_id, 'site_id', $_POST['_site_id'] );

            if ( is_wp_error( $user_data ) ) {
                // There was an error; possibly this user doesn't exist.
                echo 'Error.';
            } else {
                // Success!
                echo 'User profile updated.';
            }
        }

        $user_data = get_userdata( $current_user_id );

        ?>
        <div class="ui-widget">
            <h2>My profile</h2>
            <form method="post">
            <fieldset>
                <label for="display-name">Name : </label>
                <input type="text" id="display-name" name="_display_name" value="<?php echo $user_data->display_name;?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="site-id"> Site: </label>
                <select id="site-id" name="_site_id" class="text ui-widget-content ui-corner-all" disabled>
                    <option value="">Select Site</option>
                <?php
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
                <?php
                // Site action list by site_id
                user_site_action_list($site_id);
                //echo '<input type="submit" name="_user_submit" style="margin:3px;" value="Submit" />';
                ?>
            </fieldset>
            </form>
        </div><?php

    } else {
        user_did_not_login_yet();
    }
    return ob_get_clean(); // Return the buffered content
}
add_shortcode('user-profile', 'user_profile_shortcode');

function user_site_action_list($site_id=0) {
    // Site action list by site_id                
    $args = array(
        'post_type'      => 'action',
        'posts_per_page' => -1,
/*        
        'meta_query'     => array(
            array(
                'key'   => 'site_id',
                'value' => $site_id,
            ),
        ),
*/        
    );    
    $query = new WP_Query($args);
    ?>
    <table class="ui-widget" style="width:100%;">
        <thead>
            <th></th>
            <th>Action</th>
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
                <tr id="user-action-list-<?php echo $x;?>">
                    <td style="text-align:center;"><input type="checkbox" id="user-action-<?php echo $x;?>" /></td>
                    <td style="text-align:center;"><?php echo esc_html(get_the_title(get_the_ID()));?></td>
                    <td><?php echo esc_html(get_the_content(get_the_ID()));?></td>
                    <td style="text-align:center;"><span id="btn-edit-user-action-<?php the_ID();?>" class="dashicons dashicons-edit"></span></td>
                    <td style="text-align:center;"><span id="btn-del-user-action-<?php the_ID();?>" class="dashicons dashicons-trash"></span></td>
                </tr>
            <?php 
            $x += 1;
        endwhile;
        while ($x<50) {
            echo '<tr id="user-action-list-'.$x.'" style="display:none;"></tr>';
            $x += 1;
        }
    wp_reset_postdata();
    endif;
    ?>
        </tbody>
        <tr><td colspan="5"><div id="btn-new-user-site-action" style="border:solid; margin:3px; text-align:center; border-radius:5px">+</div></td></tr>
    </table>
    <?php
}

function new_user_site_action_data() {
    $current_user = wp_get_current_user();
    // Set up the post data
    $new_post = array(
        'post_title'    => 'New action',
        'post_content'  => 'Your post content goes here.',
        'post_status'   => 'publish', // Publish the post immediately
        'post_author'   => $current_user->ID, // Use the user ID of the author
        'post_type'     => 'action', // Change to your custom post type if needed
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
add_action( 'wp_ajax_new_user_site_action_data', 'new_user_site_action_data' );
add_action( 'wp_ajax_nopriv_new_user_site_action_data', 'new_user_site_action_data' );

function get_user_site_action_list() {
    // Retrieve the value
    $query = retrieve_user_site_action_list_data($_POST['_site_id']);

    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $_list = array();
            $_list["action_id"] = get_the_ID();
            $_list["action_title"] = '<a href="'.get_permalink(get_the_ID()).'">'.get_the_title().'</a>';
            $_list["action_description"] = get_the_content();
            $next_action_id = esc_html(get_post_meta(get_the_ID(), 'next_action', true));
            $_list["next_action_title"] = get_the_title($next_action_id);
            $_list["next_action_leadtime"] = esc_html(get_post_meta(get_the_ID(), 'next_action_leadtime', true));
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata(); // Reset post data to the main loop
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_user_site_action_list', 'get_user_site_action_list' );
add_action( 'wp_ajax_nopriv_get_user_site_action_list', 'get_user_site_action_list' );

function retrieve_user_site_action_list_data($_id=0) {
    // Retrieve the value
    $args = array(
        'post_type'      => 'action',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'site_id',
                'value' => $_id,
            ),
        ),
    );
    $query = new WP_Query($args);
    return $query;
}


