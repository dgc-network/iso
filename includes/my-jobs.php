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

// Register job post type
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
                        <th>My</th>
                        <th>Job</th>
                        <th>Description</th>
                    </thead>
                    <tbody>
                    <?php
                    if ($query->have_posts()) :
                        $x = 0;
                        while ($query->have_posts()) : $query->the_post();
                            $checked = (is_my_job(get_the_ID())) ? 'checked' : '';
                            ?>
                            <tr class="site-job-list-<?php echo $x;?>" id="edit-site-job-<?php the_ID();?>">
                                <?php if (is_my_job(get_the_ID())) {
                                    ?><td style="text-align:center;"><input type="checkbox" id="check-my-job-<?php the_ID();?>" checked disabled /></td><?php
                                } else {
                                    ?><td style="text-align:center;"><input type="checkbox" id="check-my-job-<?php the_ID();?>" disabled /></td><?php
                                }?>
                                <td style="text-align:center;"><?php the_title();?></td>
                                <td><?php the_content();?></td>
                            </tr>
                            <?php 
                            $x += 1;
                        endwhile;
                        wp_reset_postdata();
                        while ($x<50) {
                            echo '<tr class="site-job-list-'.$x.'" style="display:none;"></tr>';
                            $x += 1;
                        }
                    endif;
                    ?>
                    </tbody>
                </table>
                <div id="btn-new-site-job" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php display_job_dialog($site_id);?>
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

function get_site_job_list_data() {
    // Retrieve the value
    $query = retrieve_site_job_list_data($_POST['_site_id']);

    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $_list = array();
            if (is_my_job(get_the_ID())){
                $_list["is_my_job"] = 1;
            } else {
                $_list["is_my_job"] = 0;
            }
            $_list["job_id"] = get_the_ID();
            $_list["job_title"] = get_the_title();
            $_list["job_content"] = get_post_field('post_content', get_the_ID());
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata(); // Reset post data to the main loop
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_site_job_list_data', 'get_site_job_list_data' );
add_action( 'wp_ajax_nopriv_get_site_job_list_data', 'get_site_job_list_data' );

function is_my_job($job_id) {
    // Get the current user ID
    $current_user_id = get_current_user_id();    
    // Get the user's job IDs as an array
    $user_jobs = get_user_meta($current_user_id, 'my_job_ids', true);
    // If $user_jobs is not an array, convert it to an array
    if (!is_array($user_jobs)) {
        $user_jobs = array();
    }
    // Check if the current user has the specified job ID in their metadata
    return in_array($job_id, $user_jobs);
}

function display_job_dialog($site_id=0) {
    ?>
    <div id="job-dialog" title="Job dialog" style="display:none;">
        <fieldset>
            <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
            <input type="hidden" id="job-id" />
            <label for="job-title">Title:</label>
            <input type="text" id="job-title" class="text ui-widget-content ui-corner-all" />
            <label for="job-content">Content:</label>
            <input type="text" id="job-content" class="text ui-widget-content ui-corner-all" />
            <?php display_site_job_action_list();?>
            <label for="is-my-job">My job:</label>
            <input type="checkbox" id="is-my-job" />
            <input type="hidden" id="my-job-ids" />
        </fieldset>
    </div>
    <?php
}

function get_site_job_dialog_data() {
    $response = array();
    if( isset($_POST['_job_id']) ) {
        $job_id = (int)sanitize_text_field($_POST['_job_id']);
        $response["job_title"] = get_the_title($job_id);
        $response["job_content"] = get_post_field('post_content', $job_id);
        $response["is_my_job"] = is_my_job($job_id);
        $my_job_ids_array = get_user_meta($current_user_id, 'my_job_ids', true);
        $my_job_ids = implode(',', $my_job_ids_array);
        $response["my_job_ids"] = $my_job_ids;
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_get_site_job_dialog_data', 'get_site_job_dialog_data' );
add_action( 'wp_ajax_nopriv_get_site_job_dialog_data', 'get_site_job_dialog_data' );

function set_site_job_dialog_data() {
    $current_user_id = get_current_user_id();
    if( isset($_POST['_job_id']) ) {
        $data = array(
            'ID'           => $_POST['_job_id'],
            'post_title'   => $_POST['_job_title'],
            'post_content' => $_POST['_job_content'],
        );
        wp_update_post( $data );
/*
        $job_id = sanitize_text_field($_POST['_job_id']);
        $is_my_job = sanitize_text_field($_POST['_is_my_job']);
        $my_job_ids_array = get_user_meta($current_user_id, 'my_job_ids', true);
        if ($is_my_job==1){

        }
        $my_job_ids = sanitize_text_field($_POST['my_job_ids']);
        // Convert the comma-separated string to an array
        $my_job_ids_array = explode(',', $my_job_ids);
        // Update user meta with the new job IDs
        update_post_meta($current_user_id, 'my_job_ids', $my_job_ids_array);
*/
        $job_id = sanitize_text_field($_POST['_job_id']);
        $is_my_job = sanitize_text_field($_POST['_is_my_job']);
        $my_job_ids_array = get_user_meta($current_user_id, 'my_job_ids', true);
        
        // Convert the current 'my_job_ids' value to an array if not already an array
        if (!is_array($my_job_ids_array)) {
            $my_job_ids_array = array();
        }
        
        // Check if $job_id is in 'my_job_ids'
        $job_exists = in_array($job_id, $my_job_ids_array);
        
        // Check the condition and update 'my_job_ids' accordingly
        if ($is_my_job == 1 && !$job_exists) {
            // Add $job_id to 'my_job_ids'
            $my_job_ids_array[] = $job_id;
        } elseif ($is_my_job != 1 && $job_exists) {
            // Remove $job_id from 'my_job_ids'
            $my_job_ids_array = array_diff($my_job_ids_array, array($job_id));
        }
        
        // Update 'my_job_ids' meta value
        update_user_meta($current_user_id, 'my_job_ids', $my_job_ids_array);
        
    } else {
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

function display_site_job_action_list() {
?>
    <table style="width:100%;">
        <thead>
            <tr>
                <th><?php echo __( 'Action', 'your-text-domain' );?></th>
                <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                <th><?php echo __( 'Next job', 'your-text-domain' );?></th>
                <th><?php echo __( 'LeadTime', 'your-text-domain' );?></th>
            </tr>
        </thead>
        <tbody>
        <?php
        $x = 0;
        while ($x<50) {
            echo '<tr class="site-job-action-list-'.$x.'" style="display:none;"></tr>';
            $x += 1;
        }
        ?>
        </tbody>
    </table>
    <div id="btn-new-site-job-action" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
    <?php display_site_job_action_dialog();?>
<?php
}
    
function retrieve_action_list_data($job_id=0) {
    $args = array(
        'post_type'      => 'action',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'job_id',
                'value' => $job_id,
            ),
        ),
    );
    $query = new WP_Query($args);
    return $query;
}

function get_job_action_list_data() {
    // Retrieve the documents data
    $query = retrieve_action_list_data($_POST['_job_id']);

    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $next_job = esc_attr(get_post_meta(get_the_ID(), 'next_job', true));
            $_list = array();
            $_list["action_id"] = get_the_ID();
            $_list["action_title"] = get_the_title();
            $_list["action_content"] = get_post_field('post_content', get_the_ID());
            if ($next_job==-1){
                $_list["next_job"] = __( '發行', 'your-text-domain' );
            } else {
                $_list["next_job"] = get_the_title($next_job);
            }
            $_list["next_leadtime"] = esc_html(get_post_meta(get_the_ID(), 'next_leadtime', true));
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata(); // Reset post data to the main loop
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_job_action_list_data', 'get_job_action_list_data' );
add_action( 'wp_ajax_nopriv_get_job_action_list_data', 'get_job_action_list_data' );

function display_site_job_action_dialog(){
?>
    <div id="site-job-action-dialog" title="Action templates dialog" style="display:none;">
        <fieldset>
            <input type="hidden" id="job-id" />
            <input type="hidden" id="action-id" />
            <label for="action-title">Title:</label>
            <input type="text" id="action-title" class="text ui-widget-content ui-corner-all" />
            <label for="action-content">Content:</label>
            <input type="text" id="action-content" class="text ui-widget-content ui-corner-all" />
            <label for="next-job">Next job:</label>
            <select id="next-job" class="text ui-widget-content ui-corner-all" ></select>
            <label for="next-leadtime">Next leadtime:</label>
            <input type="text" id="next-leadtime" class="text ui-widget-content ui-corner-all" />
        </fieldset>
    </div>
<?php
}

function select_site_job_option_data($selected_job=0, $site_id=0) {
    $options = '<option value="">Select job</option>';
    $query = retrieve_site_job_list_data($site_id);
    while ($query->have_posts()) : $query->the_post();
        $selected = ($selected_job == get_the_ID()) ? 'selected' : '';
        $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
    endwhile;
    wp_reset_postdata(); // Reset post data to the main loop
    if ($selected_job==0){
        $options .= '<option value="-1" selected>'.__( '發行', 'your-text-domain' ).'</option>';
    } else {
        $options .= '<option value="-1">'.__( '發行', 'your-text-domain' ).'</option>';
    }
    return $options;
}

function get_job_action_dialog_data() {
    $response = array();
    if( isset($_POST['_action_id']) ) {
        $action_id = (int)sanitize_text_field($_POST['_action_id']);
        $response["action_title"] = get_the_title($action_id);
        $response["action_content"] = get_post_field('post_content', $action_id);
        $next_job = esc_attr(get_post_meta($action_id, 'next_job', true));
        $response["next_job"] = select_site_job_option_data($next_job, $_POST['_site_id']);
        $response["next_leadtime"] = esc_html(get_post_meta($action_id, 'next_leadtime', true));
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_get_job_action_dialog_data', 'get_job_action_dialog_data' );
add_action( 'wp_ajax_nopriv_get_job_action_dialog_data', 'get_job_action_dialog_data' );

function set_job_action_dialog_data() {
    if( isset($_POST['_action_id']) ) {
        $data = array(
            'ID'         => $_POST['_action_id'],
            'post_title' => $_POST['_action_title'],
            'post_content' => $_POST['_action_content'],
            'meta_input' => array(
                'next_job'   => $_POST['_next_job'],
                'next_leadtime' => $_POST['_next_leadtime'],
            )
        );
        wp_update_post( $data );
    } else {
        $current_user_id = get_current_user_id();
        // Set up the post data
        $new_post = array(
            'post_title'    => 'New action',
            'post_content'  => 'Your post content goes here.',
            'post_status'   => 'publish', // Publish the post immediately
            'post_author'   => $current_user_id, // Use the user ID of the author
            'post_type'     => 'action', // Change to your custom post type if needed
        );    
        // Insert the post into the database
        $post_id = wp_insert_post($new_post);
        update_post_meta( $post_id, 'job_id', sanitize_text_field($_POST['_job_id']));
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_job_action_dialog_data', 'set_job_action_dialog_data' );
add_action( 'wp_ajax_nopriv_set_job_action_dialog_data', 'set_job_action_dialog_data' );

function del_job_action_dialog_data() {
    // Delete the post
    $result = wp_delete_post($_POST['_action_id'], true); // Set the second parameter to true to force delete    
    wp_send_json($result);
}
add_action( 'wp_ajax_del_job_action_dialog_data', 'del_job_action_dialog_data' );
add_action( 'wp_ajax_nopriv_del_job_action_dialog_data', 'del_job_action_dialog_data' );

