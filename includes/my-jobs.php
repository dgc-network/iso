<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Register job post type
function register_job_post_type() {
    $labels = array(
        'menu_name'     => _x('Jobs', 'admin menu', 'textdomain'),
    );
    $args = array(
        'labels'        => $labels,
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
function profiles_shortcode() {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        if ($_GET['_select_profile']=='1') display_site_profile();
        if ($_GET['_select_profile']!='1') display_my_profile();
    } else {
        user_did_not_login_yet();
    }
}
add_shortcode('my-jobs', 'profiles_shortcode');

function display_my_profile() {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        $current_user_id = get_current_user_id();
        $site_id = esc_attr(get_post_meta($current_user_id, 'site_id', true));
        $user_data = get_userdata( $current_user_id );
        ?>
        <h2><?php echo __( 'My profile', 'your-text-domain' );?></h2>
        <div class="ui-widget">
        <fieldset>
            <div id="profile-setting-div" style="display:none">
            <fieldset>
                <label for="display-name">Name : </label>
                <input type="text" id="display-name" name="_display_name" value="<?php echo $user_data->display_name;?>" class="text ui-widget-content ui-corner-all" />
                <label for="site-title"> Site: </label>
                <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" />
                <div id="site-hint" style="display:none; color:#999;"></div>
                <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
                <hr>
                <button type="submit" id="btn-submit-profile">Submit</button>
            </fieldset>
            </div>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <select id="select-profile">
                        <option value="0" selected>My profile</option>
                        <option value="1">Site profile</option>
                        <option value="2">...</option>
                    </select>
                </div>
                <div style="text-align: right">
                    <input type="text" id="search-job" style="display:inline" placeholder="Search..." />
                    <span id="btn-job-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic"></span>
                </div>
            </div>

            <table class="ui-widget" style="width:100%;">
                <thead>
                    <th id="btn-profile-setting">My<span style="margin-left:5px;" class="dashicons dashicons-admin-generic"></span></th>
                    <th>Job</th>
                    <th>Description</th>
                    <th>Start</th>
                </thead>
                <tbody>
                <?php
                $query = retrieve_site_job_list_data($site_id);
                if ($query->have_posts()) :
                    $x = 0;
                    while ($query->have_posts()) : $query->the_post();
                        $job_id = get_the_ID();
                        $my_job_checked = is_my_job(get_the_ID()) ? 'checked' : '';
                        $is_start_job = esc_attr(get_post_meta(get_the_ID(), 'is_start_job', true));
                        $start_job_checked = ($is_start_job==1) ? 'checked' : '';
                        ?>
                        <tr class="site-job-list-<?php echo esc_attr($x);?>" id="edit-site-job-<?php the_ID();?>">
                            <td style="text-align:center;"><input type="checkbox" id="check-my-job-<?php the_ID();?>" <?php echo $my_job_checked;?>/></td>
                            <td style="text-align:center;"><?php the_title();?></td>
                            <td><?php the_content();?></td>
                            <td style="text-align:center;"><input type="checkbox" id="check-start-job-<?php the_ID();?>" <?php echo $start_job_checked;?>/></td>
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
            <input type ="button" id="new-site-job" value="+" style="width:100%; margin:3px; border-radius:5px; font-size:small;" />
        </fieldset>
        </div>
        <?php display_job_dialog();?>
        <?php
    }
}

function display_site_profile() {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        $current_user_id = get_current_user_id();
        $site_id = esc_attr(get_post_meta($current_user_id, 'site_id', true));
        $user_data = get_userdata( $current_user_id );
        ?>
        <h2><?php echo __( 'Site profile', 'your-text-domain' );?></h2>
        <div class="ui-widget">
        <fieldset>
            <div id="profile-setting-div" style="display:none">
            <fieldset>
                <label for="display-name">Name : </label>
                <input type="text" id="display-name" name="_display_name" value="<?php echo $user_data->display_name;?>" class="text ui-widget-content ui-corner-all" />
                <label for="site-title"> Site: </label>
                <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" />
                <div id="site-hint" style="display:none; color:#999;"></div>
                <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
                <hr>
                <button type="submit" id="btn-submit-profile">Submit</button>
            </fieldset>
            </div>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <select id="select-profile">
                        <option value="0">My profile</option>
                        <option value="1" selected>Site profile</option>
                        <option value="2">...</option>
                    </select>
                </div>
                <div style="text-align: right">
                    <input type="text" id="search-job" style="display:inline" placeholder="Search..." />
                    <span id="btn-job-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic"></span>
                </div>
            </div>

            <table class="ui-widget" style="width:100%;">
                <thead>
                    <th id="btn-profile-setting">My<span style="margin-left:5px;" class="dashicons dashicons-admin-generic"></span></th>
                    <th>Job</th>
                    <th>Description</th>
                    <th>Start</th>
                </thead>
                <tbody>
                <?php
                $query = retrieve_site_job_list_data($site_id);
                if ($query->have_posts()) :
                    $x = 0;
                    while ($query->have_posts()) : $query->the_post();
                        $job_id = get_the_ID();
                        $my_job_checked = is_my_job(get_the_ID()) ? 'checked' : '';
                        $is_start_job = esc_attr(get_post_meta(get_the_ID(), 'is_start_job', true));
                        $start_job_checked = ($is_start_job==1) ? 'checked' : '';
                        ?>
                        <tr class="site-job-list-<?php echo esc_attr($x);?>" id="edit-site-job-<?php the_ID();?>">
                            <td style="text-align:center;"><input type="checkbox" id="check-my-job-<?php the_ID();?>" <?php echo $my_job_checked;?>/></td>
                            <td style="text-align:center;"><?php the_title();?></td>
                            <td><?php the_content();?></td>
                            <td style="text-align:center;"><input type="checkbox" id="check-start-job-<?php the_ID();?>" <?php echo $start_job_checked;?>/></td>
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
            <input type ="button" id="new-site-job" value="+" style="width:100%; margin:3px; border-radius:5px; font-size:small;" />
        </fieldset>
        </div>
        <?php display_job_dialog();?>
        <?php
    }
}

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
            $_list["job_id"] = get_the_ID();
            $_list["job_title"] = get_the_title();
            $_list["job_content"] = get_post_field('post_content', get_the_ID());
            $_list["is_my_job"] = is_my_job(get_the_ID()) ? 1 : 0;
            $_list["is_start_job"] = esc_attr(get_post_meta(get_the_ID(), 'is_start_job', true));
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata();
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

function display_job_dialog() {
    ?>
    <div id="job-dialog" title="Job dialog" style="display:none;">
    <fieldset>
        <input type="hidden" id="job-id" />
        <label for="job-title">Title:</label>
        <input type="text" id="job-title" class="text ui-widget-content ui-corner-all" />
        <label for="job-content">Content:</label>
        <input type="text" id="job-content" class="text ui-widget-content ui-corner-all" />
        <?php display_site_job_action_list();?>
        <div>
            <div style="display:inline-block; width:50%;">
                <label for="is-my-job">My job:</label>
                <input type="checkbox" id="is-my-job" />
            </div>
            <div style="display:inline-block;">
                <label for="is-start-job">Start job:</label>
                <input type="checkbox" id="is-start-job" />
            </div>
        </div>
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
        $response["is_my_job"] = is_my_job($job_id) ? 1 : 0;
        $response["is_start_job"] = esc_attr(get_post_meta($job_id, 'is_start_job', true));
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_get_site_job_dialog_data', 'get_site_job_dialog_data' );
add_action( 'wp_ajax_nopriv_get_site_job_dialog_data', 'get_site_job_dialog_data' );

function set_site_job_dialog_data() {
    $current_user_id = get_current_user_id();
    if( isset($_POST['_job_id']) ) {
        $job_id = sanitize_text_field($_POST['_job_id']);
        $data = array(
            'ID'           => $job_id,
            'post_title'   => sanitize_text_field($_POST['_job_title']),
            'post_content' => sanitize_text_field($_POST['_job_content']),
        );
        wp_update_post( $data );
        update_post_meta( $job_id, 'is_start_job', sanitize_text_field($_POST['_is_start_job']));

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
    
function retrieve_job_action_list_data($_id=0) {
    $args = array(
        'post_type'      => 'action',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'job_id',
                'value' => $_id,
            ),
        ),
    );
    $query = new WP_Query($args);
    return $query;
}

function get_job_action_list_data() {
    $query = retrieve_job_action_list_data($_POST['_job_id']);
    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $next_job = esc_attr(get_post_meta(get_the_ID(), 'next_job', true));
            $_list = array();
            $_list["action_id"] = get_the_ID();
            $_list["action_title"] = get_the_title();
            $_list["action_content"] = get_post_field('post_content', get_the_ID());
            $_list["next_job"] = get_the_title($next_job);
            if ($next_job==-1) $_list["next_job"] = __( '發行', 'your-text-domain' );
            if ($next_job==-2) $_list["next_job"] = __( '廢止', 'your-text-domain' );
            $_list["next_leadtime"] = esc_html(get_post_meta(get_the_ID(), 'next_leadtime', true));
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata();
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
    wp_reset_postdata();
    if ($selected_job==-1){
        $options .= '<option value="-1" selected>'.__( '發行', 'your-text-domain' ).'</option>';
    } else {
        $options .= '<option value="-1">'.__( '發行', 'your-text-domain' ).'</option>';
    }
    if ($selected_job==-2){
        $options .= '<option value="-2" selected>'.__( '廢止', 'your-text-domain' ).'</option>';
    } else {
        $options .= '<option value="-2">'.__( '廢止', 'your-text-domain' ).'</option>';
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
        // Insert the post into the database
        $new_post = array(
            'post_title'    => 'New action',
            'post_content'  => 'Your post content goes here.',
            'post_status'   => 'publish', // Publish the post immediately
            'post_author'   => $current_user_id, // Use the user ID of the author
            'post_type'     => 'action', // Change to your custom post type if needed
        );    
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

