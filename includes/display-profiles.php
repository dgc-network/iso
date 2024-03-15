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
        echo '<div class="ui-widget" id="result-container">';
        if ($_GET['_select_profile']=='1') echo display_site_profile();
        if ($_GET['_select_profile']!='1') echo display_my_profile();
        echo '</div>';
    } else {
        user_did_not_login_yet();
    }
}
add_shortcode('display-profiles', 'profiles_shortcode');

function display_my_profile() {
    ob_start();
    if (is_user_logged_in()) {
        // Check if the user is logged in
        $current_user_id = get_current_user_id();
        $site_id = get_user_meta( $current_user_id, 'site_id', true);
        $image_url = get_post_meta( $site_id, 'image_url', true);
        $user_data = get_userdata( $current_user_id );
        $is_site_admin = get_user_meta($current_user_id, 'is_site_admin', true);
        $site_admin_checked = ($is_site_admin==1) ? 'checked' : '';
        ?>
        <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
        <h2 style="display:inline;"><?php echo __( '我的帳號設定', 'your-text-domain' );?></h2>
        <fieldset>
            <label for="display-name">Name : </label>
            <input type="text" id="display-name" value="<?php echo $user_data->display_name;?>" class="text ui-widget-content ui-corner-all" />
            <label for="user-email">Email : </label>
            <input type="text" id="user-email" value="<?php echo $user_data->user_email;?>" class="text ui-widget-content ui-corner-all" />
            <fieldset style="margin-top:5px;">
            <table class="ui-widget" style="width:100%;">
                <thead>
                    <th>My</th>
                    <th>Job</th>
                    <th>Description</th>
                </thead>
                <tbody>
                <?php
                $query = retrieve_site_job_list_data($site_id);
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        $job_id = get_the_ID();
                        $my_job_checked = is_user_job(get_the_ID()) ? 'checked' : '';
                        ?>
                        <tr id="my-job-list" data-job-id="<?php the_ID();?>">
                            <td style="text-align:center;"><input type="checkbox" id="check-my-job-<?php the_ID();?>" <?php echo $my_job_checked;?> /></td>
                            <td style="text-align:center;"><?php the_title();?></td>
                            <td><?php the_content();?></td>
                        </tr>
                        <?php 
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
                </tbody>
            </table>
            </fieldset>
            <label for="site-title"> Site: </label>
            <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
            <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" disabled />
            <hr>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <select id="select-profile">
                        <option value="0" selected><?php echo __( '我的帳號設定', 'your-text-domain' );?></option>
                        <option value="1"><?php echo __( '單位組織設定', 'your-text-domain' );?></option>
                        <option value="2">...</option>
                    </select>
                </div>
                <div style="text-align: right">
                    <button type="submit" id="my-profile-submit">Submit</button>
                </div>
            </div>

        </fieldset>
        <?php
    }
    $html = ob_get_clean();
    return $html;
}

function set_my_profile_data() {
    $response = array('success' => false, 'error' => 'Invalid data format');
    if (isset($_POST['_display_name'])) {
        $current_user_id = get_current_user_id();
        wp_update_user(array('ID' => $current_user_id, 'display_name' => sanitize_text_field($_POST['_display_name'])));
        wp_update_user(array('ID' => $current_user_id, 'user_email' => sanitize_text_field($_POST['_user_email'])));
        $response = array('success' => true);
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_my_profile_data', 'set_my_profile_data' );
add_action( 'wp_ajax_nopriv_set_my_profile_data', 'set_my_profile_data' );

function display_site_profile() {
    ob_start();
    $current_user_id = get_current_user_id();
    $site_id = get_user_meta($current_user_id, 'site_id', true);
    $image_url = get_post_meta( $site_id, 'image_url', true);
    $is_site_admin = get_user_meta($current_user_id, 'is_site_admin', true);
    $user_data = get_userdata($current_user_id);

    if ($is_site_admin==1 || current_user_can('administrator')) {
        // Check if the user is administrator
        ?>
        <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
        <h2 style="display:inline;"><?php echo __( '單位組織設定', 'your-text-domain' );?></h2>
        <fieldset>
            <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
            <label for="site-title"><?php echo __( '單位組織名稱：', 'your-text-domain' );?></label>
            <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" />
            <div id="site-hint" style="display:none; color:#999;"></div>
            <?php echo (isURL($image_url)) ? '<img src="' . esc_attr($image_url) . '" style="object-fit:cover; width:250px; height:250px;">' : '<a href="#" id="custom-image-href">Set image URL</a>'; ?>

            <label for="site-members"><?php echo __( '單位組織成員：', 'your-text-domain' );?></label>
            <fieldset style="margin-top:5px;">
            <table class="ui-widget" style="width:100%;">
                <thead>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Admin</th>
                </thead>
                <tbody>
                <?php

                // Define the meta query parameters
                $meta_query_args = array(
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                        'compare' => '=',
                    ),
                );
                $users = get_users(array('meta_query' => $meta_query_args));
                if (current_user_can('administrator')) $users = get_users();
                
                // Loop through the users
                foreach ($users as $user) {
                    $is_site_admin = get_user_meta($user->ID, 'is_site_admin', true);
                    $is_admin_checked = ($is_site_admin==1) ? 'checked' : '';
                    ?>
                    <tr id="edit-site-user-<?php echo $user->ID;?>">
                        <td style="text-align:center;"><?php echo $user->display_name;?></td>
                        <td style="text-align:center;"><?php echo $user->user_email;?></td>
                        <td style="text-align:center;"><input type="checkbox" <?php echo $is_admin_checked;?>/></td>
                    </tr>
                    <?php 
                }
                ?>
                </tbody>
            </table>
            </fieldset>
            <?php display_user_dialog($site_id);?>

            <label for="site-title"><?php echo __( '單位組織職務：', 'your-text-domain' );?></label>
            <fieldset style="margin-top:5px;">
            <table class="ui-widget" style="width:100%;">
                <thead>
                    <th>Job</th>
                    <th>Description</th>
                    <th>Start</th>
                </thead>
                <tbody>
                <?php
                $query = retrieve_site_job_list_data($site_id);
                if ($query->have_posts()) :
                    //$x = 0;
                    while ($query->have_posts()) : $query->the_post();
                        $is_start_job = get_post_meta(get_the_ID(), 'is_start_job', true);
                        $start_job_checked = ($is_start_job==1) ? 'checked' : '';
                        ?>
                        <tr class="site-job-list-<?php echo esc_attr($x);?>" id="edit-site-job-<?php the_ID();?>">
                            <td style="text-align:center;"><?php the_title();?></td>
                            <td><?php the_content();?></td>
                            <td style="text-align:center;"><input type="checkbox" id="check-start-job-<?php the_ID();?>" <?php echo $start_job_checked;?> /></td>
                        </tr>
                        <?php 
                        //$x += 1;
                    endwhile;
                    wp_reset_postdata();
                    //while ($x<50) {
                    //    echo '<tr class="site-job-list-'.$x.'" style="display:none;"></tr>';
                    //    $x += 1;
                    //}
                endif;
                ?>
                </tbody>
            </table>
            <input type ="button" id="new-site-job" value="+" style="width:100%; margin:3px; border-radius:5px; font-size:small;" />
            </fieldset>
            <?php display_job_dialog();?>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <select id="select-profile">
                        <option value="0"><?php echo __( '我的帳號設定', 'your-text-domain' );?></option>
                        <option value="1" selected><?php echo __( '單位組織設定', 'your-text-domain' );?></option>
                        <option value="2">...</option>
                    </select>
                </div>
                <div style="text-align: right">
                    <button type="submit" id="site-profile-submit">Submit</button>
                </div>
            </div>

        </fieldset>
        <?php
    } else {
        ?>
        <p>You do not have permission to access this page.</p>
        <?php
    }
    $html = ob_get_clean();
    return $html;
}

function retrieve_site_job_list_data($site_id=0) {
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

function get_site_profile_data() {
    $response = array('html_contain' => display_site_profile());
    wp_send_json($response);
}
add_action( 'wp_ajax_get_site_profile_data', 'get_site_profile_data' );
add_action( 'wp_ajax_nopriv_get_site_profile_data', 'get_site_profile_data' );

function display_user_dialog($site_id) {
    ?>
    <div id="user-dialog" title="User dialog" style="display:none;">
    <fieldset>
        <input type="hidden" id="user-id" />
        <label for="display-name">Name:</label>
        <input type="text" id="display-name" class="text ui-widget-content ui-corner-all" />
        <label for="user-email">Email:</label>
        <input type="text" id="user-email" class="text ui-widget-content ui-corner-all" />
        <input type="checkbox" id="is-site-admin" />
        <label for="is-site-admin">Is site admin</label><br>
        <?php //display_site_user_job_list($site_id);?>
        <fieldset>
            <table class="ui-widget" style="width:100%;">
                <thead>
                    <th></th>
                    <th>Job</th>
                    <th>Description</th>
                </thead>
                <tbody id="user-job-list">
                </tbody>
            </table>
        </fieldset>
        <?php
        if (current_user_can('administrator')) {
            ?>
            <label for="select-site">Site:</label>
            <select id="select-site" class="text ui-widget-content ui-corner-all" >
                <option value="">Select Site</option>
            <?php
            //$site_id = get_user_meta( $user->ID, 'site_id', true);
            $site_args = array(
                'post_type'      => 'site',
                'posts_per_page' => -1,
            );
            $sites = get_posts($site_args);    
            foreach ($sites as $site) {
                $selected = ($site_id == $site->ID) ? 'selected' : '';
                echo '<option value="' . esc_attr($site->ID) . '" ' . $selected . '>' . esc_html($site->post_title) . '</option>';
            }
            echo '</select>';
        }
        ?>
    </fieldset>
    </div>
    <?php
}

function get_site_user_dialog_data() {
    $response = array();
    if (isset($_POST['_user_id'])) {
        $user_id = (int)$_POST['_user_id'];
        // Get user data
        $user_data = get_userdata($user_id);
        if ($user_data) {
            $response["display_name"] = $user_data->display_name;
            $response["user_email"] = $user_data->user_email;
            $response["is_site_admin"] = get_user_meta($user_id, 'is_site_admin', true);
            $response["site_id"] = get_user_meta($user_id, 'site_id', true);
            // Get site job list data
            $site_id = get_user_meta($user_id, 'site_id', true);
            $query = retrieve_site_job_list_data($site_id);
            if ($query->have_posts()) {
                $user_job_list = '';
                while ($query->have_posts()) : $query->the_post();
                    $user_job_checked = is_user_job(get_the_ID(), $user_id) ? 'checked' : '';
                    $user_job_list .= '<tr id="check-user-job-' . get_the_ID() . '">';
                    $user_job_list .= '<td style="text-align:center;"><input type="checkbox" id="myCheckbox-'.get_the_ID().'" ' . $user_job_checked . ' /></td>';
                    $user_job_list .= '<td style="text-align:center;">' . get_the_title() . '</td>';
                    $user_job_list .= '<td>' . get_the_content() . '</td>';
                    $user_job_list .= '</tr>';
                endwhile;
                wp_reset_postdata();
                $response["user_job_list"] = $user_job_list;
            } else {
                $response["error"] = 'Error retrieving site job list data.';
            }
        } else {
            $response["error"] = 'Error retrieving user data.';
        }
    } else {
        $response["error"] = 'User ID not provided in the request.';
    }
    wp_send_json($response);
}
add_action('wp_ajax_get_site_user_dialog_data', 'get_site_user_dialog_data');
add_action('wp_ajax_nopriv_get_site_user_dialog_data', 'get_site_user_dialog_data');

function set_site_user_dialog_data() {
    $response = array('success' => false, 'error' => 'Invalid data format');
    if (isset($_POST['_user_id'])) {
        $user_id = absint($_POST['_user_id']);
        // Prepare user data
        $user_data = array(
            'ID'           => $user_id,
            'display_name' => sanitize_text_field($_POST['_display_name']),
            'user_email'   => sanitize_text_field($_POST['_user_email']),
        );        
        // Update the user
        $result = wp_update_user($user_data);

        if (is_wp_error($result)) {
            $response['error'] = $result->get_error_message();
        } else {
            // Update user meta
            update_user_meta($user_id, 'is_site_admin', sanitize_text_field($_POST['_is_site_admin']));
            update_user_meta($user_id, 'site_id', sanitize_text_field($_POST['_select_site']));
            $response = array('success' => true);
        }
    }
    wp_send_json($response);
}
add_action('wp_ajax_set_site_user_dialog_data', 'set_site_user_dialog_data');
add_action('wp_ajax_nopriv_set_site_user_dialog_data', 'set_site_user_dialog_data');

function del_site_user_dialog_data() {
    // Delete the post
    //$result = wp_delete_post($_POST['_job_id'], true);
    wp_send_json($result);
}
add_action( 'wp_ajax_del_site_user_dialog_data', 'del_site_user_dialog_data' );
add_action( 'wp_ajax_nopriv_del_site_user_dialog_data', 'del_site_user_dialog_data' );

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
        <input type="checkbox" id="is-start-job" />
        <label for="is-start-job">Start job</label>
    </fieldset>
    </div>
    <?php
}

function get_site_job_dialog_data() {
    $response = array();
    if( isset($_POST['_job_id']) ) {
        $job_id = sanitize_text_field($_POST['_job_id']);
        $response["job_title"] = get_the_title($job_id);
        $response["job_content"] = get_post_field('post_content', $job_id);
        $response["is_start_job"] = esc_attr(get_post_meta( $job_id, 'is_start_job', true));
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
    } else {
        // Set up the post data
        $new_post = array(
            'post_title'    => 'New job',
            'post_content'  => 'Your post content goes here.',
            'post_status'   => 'publish',
            'post_author'   => $current_user_id,
            'post_type'     => 'job',
        );    
        $post_id = wp_insert_post($new_post);
        update_post_meta( $post_id, 'site_id', sanitize_text_field($_POST['_site_id']));
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_site_job_dialog_data', 'set_site_job_dialog_data' );
add_action( 'wp_ajax_nopriv_set_site_job_dialog_data', 'set_site_job_dialog_data' );

function del_site_job_dialog_data() {
    // Delete the post
    $result = wp_delete_post($_POST['_job_id'], true);
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
    
function retrieve_job_action_list_data($job_id=0) {
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
        $next_job = esc_attr(get_post_meta( $action_id, 'next_job', true));
        $response["next_job"] = select_site_job_option_data($next_job, $_POST['_site_id']);
        $response["next_leadtime"] = esc_html(get_post_meta( $action_id, 'next_leadtime', true));
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

function is_user_job($job_id, $user_id=0) {    
    // Get the current user ID
    if ($user_id==0) $user_id = get_current_user_id();    
    // Get the user's job IDs as an array
    $user_jobs = get_user_meta($user_id, 'user_job_ids', true);
    // If $user_jobs is not an array, convert it to an array
    if (!is_array($user_jobs)) {
        $user_jobs = array();
    }
    // Check if the current user has the specified job ID in their metadata
    return in_array($job_id, $user_jobs);
}

function set_user_job_data() {
    $response = array('success' => false, 'error' => 'Invalid data format');
    if (isset($_POST['_job_id'])) {
        $job_id = (int)sanitize_text_field($_POST['_job_id']);
        $user_id = (int)sanitize_text_field($_POST['_user_id']);
        $is_user_job = sanitize_text_field($_POST['_is_user_job']);
        
        if (!isset($user_id)) $user_id = get_current_user_id();
        $user_job_ids_array = get_user_meta($user_id, 'user_job_ids', true);        
        if (!is_array($user_job_ids_array)) $user_job_ids_array = array();
        $job_exists = in_array($job_id, $user_job_ids_array);
    
        // Check the condition and update 'user_job_ids' accordingly
        if ($is_user_job == 1 && !$job_exists) {
            // Add $job_id to 'user_job_ids'
            $user_job_ids_array[] = $job_id;
        } elseif ($is_user_job != 1 && $job_exists) {
            // Remove $job_id from 'user_job_ids'
            $user_job_ids_array = array_diff($user_job_ids_array, array($job_id));
        }

        // Update 'user_job_ids' meta value
        update_user_meta( $user_id, 'user_job_ids', $user_job_ids_array);
        $response = array('success' => true);
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_user_job_data', 'set_user_job_data' );
add_action( 'wp_ajax_nopriv_set_user_job_data', 'set_user_job_data' );


function get_site_list_data() {
    $search_query = sanitize_text_field($_POST['_site_title']);
    $args = array(
        'post_type'      => 'site',
        'posts_per_page' => -1,
        's'              => $search_query,
    );
    $query = new WP_Query($args);

    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $_list = array();
            $_list["site_id"] = get_the_ID();
            $_list["site_title"] = get_the_title();
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata();
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_site_list_data', 'get_site_list_data' );
add_action( 'wp_ajax_nopriv_get_site_list_data', 'get_site_list_data' );

function get_site_dialog_data() {
    $response = array();
    if( isset($_POST['_site_id']) ) {
        $site_id = (int)sanitize_text_field($_POST['_site_id']);
        //$response["site_id"] = $site_id;
        $response["site_title"] = get_the_title($site_id);
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_get_site_dialog_data', 'get_site_dialog_data' );
add_action( 'wp_ajax_nopriv_get_site_dialog_data', 'get_site_dialog_data' );

function set_site_dialog_data() {
    $response = array('success' => false, 'error' => 'Invalid data format');
    $site_title = sanitize_text_field($_POST['_site_title']);
    if( isset($_POST['_site_id']) ) {
        $site_id = (int) sanitize_text_field($_POST['_site_id']);
        $site_title = sanitize_text_field($_POST['_site_title']);
        // Prepare post data
        $post_data = array(
            'ID'         => $site_id,
            'post_title' => $site_title,
        );        
        // Update the post
        wp_update_post($post_data);
        $response = array('success' => true);
    } else {
        // Set up the new post data
        $current_user_id = get_current_user_id();
        $new_post = array(
            'post_title'    => $site_title,
            'post_content'  => 'Your post content goes here.',
            'post_status'   => 'publish',
            'post_author'   => $current_user_id,
            'post_type'     => 'site',
        );    
        $post_id = wp_insert_post($new_post);
        update_user_meta( $current_user_id, 'site_id', $post_id );
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_site_dialog_data', 'set_site_dialog_data' );
add_action( 'wp_ajax_nopriv_set_site_dialog_data', 'set_site_dialog_data' );
