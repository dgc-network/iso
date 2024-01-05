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
        $user = get_userdata( $current_user_id );
        $site_id = esc_html(get_post_meta($current_user_id, '_site_id', true));


        if( isset($_POST['_user_submit']) ) {
            $user_data = wp_update_user( array( 
                'ID' => $current_user_id, 
                'display_name' => $_POST['_display_name'], 
                'user_email' => $_POST['_user_email'], 
            ) );

            //update_post_meta( $post_id, 'dgc_wallet_balance', $your_balance+$amount );

            if ( is_wp_error( $user_data ) ) {
                // There was an error; possibly this user doesn't exist.
                echo 'Error.';
            } else {
                // Success!
                echo 'User profile updated.';
            }
        }

        echo '<div style="text-align:center;">';
        echo '<h3>User profile</h3>';
        echo '<form method="post" style="display:inline-block; text-align:-webkit-center;">';
        echo '<fieldset>';
        echo '<label style="text-align:left;" for="_display_name">Name:</label>';
        echo '<input type="text" name="_display_name" value="'.$user->display_name.'" class="text ui-widget-content ui-corner-all" />';
        echo '<label style="text-align:left;" for="_user_email">Email:</label>';
        echo '<input type="text" name="_user_email" value="'.$user->user_email.'" class="text ui-widget-content ui-corner-all" />';
        //echo '<input type="hidden" name="_line_user_id" value="'.$_GET['_id'].'" />';

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
    
        if ($query->have_posts()) :?>
            <table class="user-action-list" style="width:100%;">
                <thead>
                    <th></th>
                    <th>Action</th>
                    <th>Description</th>
                </thead>
                <tbody>
            <?php
            $x = 0;
            while ($query->have_posts()) : $query->the_post();
                ?>
                    <tr class="user-action-item">
                        <td style="text-align:center;"><input type="checkbox" id="user-action-<?php echo $x;?>" /></td>
                        <td style="text-align:center;"><?php echo esc_html(get_the_title(get_the_ID()));?></td>
                        <td><?php echo esc_html(get_the_content(get_the_ID()));?></td>
                    </tr>
                <?php 
                $x += 1;
            endwhile;
            ?>
                </tbody>
            </table>
            <?php
            wp_reset_postdata();
        endif;

        echo '<input type="submit" name="_user_submit" style="margin:3px;" value="Submit" />';
        echo '</fieldset>';
        echo '</form>';
        echo '</div>';


    } else {
        did_not_login();
    }
    return ob_get_clean(); // Return the buffered content
}
add_shortcode('user-profile', 'user_profile_shortcode');

