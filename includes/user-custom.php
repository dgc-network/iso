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
                <th><label for="line_user_id">Line User ID</label></th>
                <td>
                    <input
                        type="text"
                        value="<?php echo esc_attr(get_user_meta($user->ID, 'line_user_id', true)); ?>"
                        name="line_user_id"
                        id="line_user_id"
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

// Populate the custom column with display_name data
function custom_users_custom_column($value, $column_name, $user_id) {
    if ($column_name === 'display_name') {
        $user = get_userdata($user_id);
        return $user->display_name;
    }
    return $value;
}
add_filter('manage_users_custom_column', 'custom_users_custom_column', 10, 3);

// Modify the user table columns order
function custom_users_column_order($columns) {
    //unset($columns['username']);
    unset($columns['name']);
    // Define the desired order of columns
    $new_order = array(
        'cb' => $columns['cb'],
        'display_name' => $columns['display_name'],
        'username' => $columns['username'],
        //'name' => $columns['name'],
        'email' => $columns['email'],
        'role' => $columns['role'],
        'posts' => $columns['posts'],
    );
    $new_order['display_name'] = __('Diplay name', 'your-text-domain');

    return $new_order;
}
add_filter('manage_users_columns', 'custom_users_column_order');

// Make 'display_name' field sortable
function custom_users_sortable_columns($columns) {
    $columns['display_name'] = 'display_name';
    return $columns;
}
add_filter('manage_users_sortable_columns', 'custom_users_sortable_columns');

// Handle sorting for 'display_name' field
function custom_users_orderby($query) {
    if (isset($query->query['orderby']) && $query->query['orderby'] === 'display_name') {
        $query->set('orderby', 'display_name');
    }
}
add_action('pre_get_users', 'custom_users_orderby');

