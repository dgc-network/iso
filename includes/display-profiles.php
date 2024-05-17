<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('display_profiles')) {
    class display_profiles {
        // Class constructor
        public function __construct() {
            add_shortcode( 'display-profiles', array( $this, 'display_shortcode' ) );
            add_action( 'init', array( $this, 'register_job_post_type' ) );

            add_action( 'wp_ajax_set_my_profile_data', array( $this, 'set_my_profile_data' ) );
            add_action( 'wp_ajax_nopriv_set_my_profile_data', array( $this, 'set_my_profile_data' ) );
            add_action( 'wp_ajax_get_site_profile_data', array( $this, 'get_site_profile_data' ) );
            add_action( 'wp_ajax_nopriv_get_site_profile_data', array( $this, 'get_site_profile_data' ) );
            add_action( 'wp_ajax_get_site_user_dialog_data', array( $this, 'get_site_user_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_site_user_dialog_data', array( $this, 'get_site_user_dialog_data' ) );
            add_action( 'wp_ajax_set_site_user_dialog_data', array( $this, 'set_site_user_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_site_user_dialog_data', array( $this, 'set_site_user_dialog_data' ) );
            add_action( 'wp_ajax_del_site_user_dialog_data', array( $this, 'del_site_user_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_site_user_dialog_data', array( $this, 'del_site_user_dialog_data' ) );
            add_action( 'wp_ajax_get_site_job_list_data', array( $this, 'get_site_job_list_data' ) );
            add_action( 'wp_ajax_nopriv_get_site_job_list_data', array( $this, 'get_site_job_list_data' ) );
            add_action( 'wp_ajax_get_site_job_dialog_data', array( $this, 'get_site_job_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_site_job_dialog_data', array( $this, 'get_site_job_dialog_data' ) );
            add_action( 'wp_ajax_set_site_job_dialog_data', array( $this, 'set_site_job_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_site_job_dialog_data', array( $this, 'set_site_job_dialog_data' ) );
            add_action( 'wp_ajax_del_site_job_dialog_data', array( $this, 'del_site_job_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_site_job_dialog_data', array( $this, 'del_site_job_dialog_data' ) );
            add_action( 'wp_ajax_get_doc_action_list_data', array( $this, 'get_doc_action_list_data' ) );
            add_action( 'wp_ajax_nopriv_get_doc_action_list_data', array( $this, 'get_doc_action_list_data' ) );                                                                    
            add_action( 'wp_ajax_get_doc_action_dialog_data', array( $this, 'get_doc_action_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_doc_action_dialog_data', array( $this, 'get_doc_action_dialog_data' ) );                                                                    
            add_action( 'wp_ajax_set_doc_action_dialog_data', array( $this, 'set_doc_action_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_doc_action_dialog_data', array( $this, 'set_doc_action_dialog_data' ) );                                                                    
            add_action( 'wp_ajax_del_doc_action_dialog_data', array( $this, 'del_doc_action_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_doc_action_dialog_data', array( $this, 'del_doc_action_dialog_data' ) );                                                                    
            add_action( 'wp_ajax_get_doc_category_list_data', array( $this, 'get_doc_category_list_data' ) );
            add_action( 'wp_ajax_nopriv_get_doc_category_list_data', array( $this, 'get_doc_category_list_data' ) );
            add_action( 'wp_ajax_get_doc_category_dialog_data', array( $this, 'get_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_doc_category_dialog_data', array( $this, 'get_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_set_doc_category_dialog_data', array( $this, 'set_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_doc_category_dialog_data', array( $this, 'set_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_del_doc_category_dialog_data', array( $this, 'del_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_doc_category_dialog_data', array( $this, 'del_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_set_user_doc_data', array( $this, 'set_user_doc_data' ) );
            add_action( 'wp_ajax_nopriv_set_user_doc_data', array( $this, 'set_user_doc_data' ) );

        }

        // Shortcode to display
        function display_shortcode() {
            // Check if the user is logged in
            if (is_user_logged_in()) {
                echo '<div class="ui-widget" id="result-container">';
                if ($_GET['_retrieve_chart_of_account']=='true') retrieve_chart_of_account();
                if ($_GET['_initial']=='true') echo $this->display_site_profile(true);
                if ($_GET['_select_profile']=='1') echo $this->display_site_profile();
                if ($_GET['_select_profile']=='2') echo $this->display_site_job_list();
                if ($_GET['_select_profile']=='3') echo $this->display_doc_category_list();
                if ($_GET['_select_profile']!='1'&&$_GET['_select_profile']!='2'&&$_GET['_select_profile']!='3'&&!isset($_GET['_initial'])) echo $this->display_my_profile();
                echo '</div>';
            } else {
                user_did_not_login_yet();
            }
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
                'show_in_menu'  => false,
            );
            register_post_type( 'job', $args );
        }

        function display_my_profile() {
            $current_user_id = get_current_user_id();
            $current_user = get_userdata( $current_user_id );
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
            ob_start();
            ?>
            <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
            <h2 style="display:inline;"><?php echo __( '我的帳號', 'your-text-domain' );?></h2>
            <fieldset>
                <label for="display-name"><?php echo __( 'Name: ', 'your-text-domain' );?></label>
                <input type="text" id="display-name" value="<?php echo $current_user->display_name;?>" class="text ui-widget-content ui-corner-all" />
                <label for="user-email"><?php echo __( 'Email: ', 'your-text-domain' );?></label>
                <input type="text" id="user-email" value="<?php echo $current_user->user_email;?>" class="text ui-widget-content ui-corner-all" />
                <fieldset style="margin-top:5px;">
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th>#</th>
                        <th><?php echo __( 'Job', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                    </thead>
                    <tbody>
                    <?php    
                    // Accessing elements of the array
                    if (is_array($user_doc_ids)) {
                        foreach ($user_doc_ids as $doc_id) {
                            $job_number = get_post_meta($doc_id, 'job_number', true);
                            $job_title = get_the_title($doc_id);
                            $job_content = get_post_field('post_content', $doc_id);
                            $doc_site = get_post_meta($doc_id, 'site_id', true);
                            if ($doc_site==$site_id) {
                            ?>
                            <tr>
                                <td style="text-align:center;"><?php echo esc_html($job_number);?></td>
                                <td style="text-align:center;"><?php echo esc_html($job_title);?></td>
                                <td width="70%"><?php echo wp_kses_post($job_content);?></td>
                            </tr>
                            <?php
                            }
                        }
                    }
                    ?>
                    </tbody>
                </table>
                </fieldset>
                <label for="site-title"><?php echo __( 'Site: ', 'your-text-domain' );?></label>
                <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
                <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" disabled />
                <hr>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <select id="select-profile">
                            <option value="0" selected><?php echo __( '我的帳號', 'your-text-domain' );?></option>
                            <option value="1"><?php echo __( '組織設定', 'your-text-domain' );?></option>
                            <option value="2"><?php echo __( '工作職掌', 'your-text-domain' );?></option>
                            <option value="3"><?php echo __( '文件類別', 'your-text-domain' );?></option>
                        </select>
                    </div>
                    <div style="text-align: right">
                        <button type="submit" id="my-profile-submit">Submit</button>
                    </div>
                </div>    
            </fieldset>
            <?php
            $html = ob_get_clean();
            return $html;
        }

        function set_my_profile_data() {
            $response = array();
            if (isset($_POST['_display_name'])) {
                $current_user_id = get_current_user_id();
                wp_update_user(array('ID' => $current_user_id, 'display_name' => sanitize_text_field($_POST['_display_name'])));
                wp_update_user(array('ID' => $current_user_id, 'user_email' => sanitize_text_field($_POST['_user_email'])));
                $response = array('success' => true);
            }
            wp_send_json($response);
        }

        function display_site_profile($initial=false) {
            ob_start();
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            $is_site_admin = get_user_meta($current_user_id, 'is_site_admin', true);
            $current_user = get_userdata($current_user_id);        
            // Check if the user is administrator or initial...
            if ($is_site_admin==1 || current_user_can('administrator') || $initial) {
                ?>
                <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
                <h2 style="display:inline;"><?php echo __( '組織設定', 'your-text-domain' );?></h2>
                <fieldset>
                    <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
                    <label for="site-title"><?php echo __( '單位組織名稱：', 'your-text-domain' );?></label>
                    <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" />
                    <div id="site-hint" style="display:none; color:#999;"></div>

                    <div id="site-image-container">
                        <?php echo (isURL($image_url)) ? '<img src="' . esc_attr($image_url) . '" style="object-fit:cover; width:250px; height:250px;" class="button">' : '<a href="#" id="custom-image-href">Set image URL</a>'; ?>
                    </div>
                    <div id="site-image-url" style="display:none;">
                    <fieldset>
                        <label for="image-url"><?php echo __( 'Image URL:', 'your-text-domain' );?></label>
                        <textarea id="image-url" rows="3" style="width:99%;"><?php echo $image_url;?></textarea>
                        <button id="set-image-url" class="button">Set</button>
                    </fieldset>
                    </div>

                    <label for="site-members"><?php echo __( '單位組織成員：', 'your-text-domain' );?></label>
                    <fieldset style="margin-top:5px;">
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'Name', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Email', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Admin', 'your-text-domain' );?></th>
                        </thead>
                        <tbody>
                        <?php        
                        $users = get_users(); // Initialize with all users
                        // If the current user is not an administrator, filter by site_id
                        if (!current_user_can('administrator')) {
                            $meta_query_args = array(
                                array(
                                    'key'     => 'site_id',
                                    'value'   => $site_id,
                                    'compare' => '=',
                                ),
                            );
                            $users = get_users(array('meta_query' => $meta_query_args));
                        }
                        // Loop through the users
                        foreach ($users as $user) {
                            $is_site_admin = get_user_meta($user->ID, 'is_site_admin', true);
                            $user_site = get_user_meta($user->ID, 'site_id', true);
                            $is_other_site = ($user_site == $site_id) ? '' : '*';
                            $is_admin_checked = ($is_site_admin == 1) ? 'checked' : '';
                            ?>
                            <tr id="edit-site-user-<?php echo $user->ID; ?>">
                                <td style="text-align:center;"><?php echo $is_other_site.$user->display_name; ?></td>
                                <td style="text-align:center;"><?php echo $user->user_email; ?></td>
                                <td style="text-align:center;"><input type="checkbox" <?php echo $is_admin_checked; ?>/></td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                    <div id="new-site-user" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                    </fieldset>
                    <?php $this->display_new_user_dialog();?>
                    <?php echo $this->display_site_user_dialog();?>

                    <div style="display:flex; justify-content:space-between; margin:5px;">
                        <div>
                            <select id="select-profile">
                                <option value="0"><?php echo __( '我的帳號', 'your-text-domain' );?></option>
                                <option value="1" selected><?php echo __( '組織設定', 'your-text-domain' );?></option>
                                <option value="2"><?php echo __( '工作職掌', 'your-text-domain' );?></option>
                                <option value="3"><?php echo __( '文件類別', 'your-text-domain' );?></option>
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
                <p><?php echo __( 'You do not have permission to access this page.', 'your-text-domain' );?></p>
                <?php
            }
            $html = ob_get_clean();
            return $html;
        }

        function get_site_profile_data() {
            $response = array('html_contain' => $this->display_site_profile());
            wp_send_json($response);
        }

        function display_site_user_dialog($user_id=false) {
            $current_user = get_userdata($user_id);
            $is_site_admin = get_user_meta($user_id, 'is_site_admin', true);
            $is_admin_checked = ($is_site_admin == 1) ? 'checked' : '';
            ob_start();
            ?>
            <div id="site-user-dialog" title="User dialog">
            <fieldset>
                <input type="hidden" id="user-id" value="<?php echo $user_id;?>" />
                <label for="display-name"><?php echo __( 'Name:', 'your-text-domain' );?></label>
                <input type="text" id="display-name" value="<?php echo $current_user->display_name;?>" class="text ui-widget-content ui-corner-all" />
                <label for="user-email"><?php echo __( 'Email:', 'your-text-domain' );?></label>
                <input type="text" id="user-email" value="<?php echo $current_user->user_email;?>" class="text ui-widget-content ui-corner-all" />
                <input type="checkbox" id="is-site-admin" <?php echo $is_admin_checked;?> />
                <label for="is-site-admin"><?php echo __( 'Is site admin', 'your-text-domain' );?></label><br>
                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th></th>
                            <th><?php echo __( 'Job', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Title', 'your-text-domain' );?></th>
                        </thead>
                        <tbody>
                            <?php
                            $query = $this->retrieve_site_job_list_data(0);
                            if ($query->have_posts()) {
                                while ($query->have_posts()) : $query->the_post();
                                    $user_job_checked = $this->is_user_doc(get_the_ID(), $user_id) ? 'checked' : '';
                                    $job_number = get_post_meta(get_the_ID(), 'job_number', true);
                                    echo '<tr id="check-user-job-' . get_the_ID() . '">';
                                    echo '<td style="text-align:center;"><input type="checkbox" id="myCheckbox-'.get_the_ID().'" ' . $user_job_checked . ' /></td>';
                                    echo '<td style="text-align:center;">' . esc_html($job_number) . '</td>';
                                    echo '<td style="text-align:center;">' . get_the_title() . '</td>';
                                    echo '</tr>';
                                endwhile;
                                wp_reset_postdata();
                            }        
                            ?>
                        </tbody>
                    </table>
                </fieldset>
                <?php
                if (current_user_can('administrator')) {
                    $current_user_id = get_current_user_id();
                    $site_id = get_user_meta($current_user_id, 'site_id', true);
                    ?>
                    <label for="select-site"><?php echo __( 'Site:', 'your-text-domain' );?></label>
                    <select id="select-site" class="text ui-widget-content ui-corner-all" >
                        <option value=""><?php echo __( 'Select Site', 'your-text-domain' );?></option>
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
                    echo '</select>';
                }
                ?>
            </fieldset>
            </div>
            <?php
            $html = ob_get_clean();
            return $html;            
        }

        function get_site_user_dialog_data() {
            $response = array();
            if (isset($_POST['_user_id'])) {
                $user_id = (int)$_POST['_user_id'];
                $response = array('html_contain' => $this->display_site_user_dialog($user_id));
            }
            wp_send_json($response);
        }

        function set_site_user_dialog_data() {
            $response = array();            
            if (isset($_POST['_user_id'])) {
                $user_id = absint($_POST['_user_id']);
                $current_user = array(
                    'ID'           => $user_id,
                    'display_name' => sanitize_text_field($_POST['_display_name']),
                    'user_email'   => sanitize_email($_POST['_user_email']),
                );        
                // Update user data
                $result = wp_update_user($current_user);

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

        function del_site_user_dialog_data() {
            $response = array();
            if (isset($_POST['_user_id'])) {
                $user_id = absint($_POST['_user_id']);
                // Check if the user ID is valid
                if ($user_id > 0) {
                    // Attempt to delete the user
                    $result = wp_delete_user($user_id, true);

                    if (is_wp_error($result)) {
                        // If an error occurs while deleting the user, set the error message in the response
                        $response['error'] = $result->get_error_message();
                    } else {
                        // If the user is successfully deleted, set success to true in the response
                        $response = array('success' => true);
                    }
                } else {
                    // If the provided user ID is invalid, set an error message in the response
                    $response['error'] = 'Invalid user ID provided.';
                }
            } else {
                // If user_id is not provided in the POST request, set an error message in the response
                $response['error'] = 'User ID is missing in the request.';
            }
            wp_send_json($response);
        }

        function display_new_user_dialog($site_id=false) {
            ?>
            <div id="new-user-dialog" title="New user dialog" style="display:none;">
            <fieldset>
                <img src="<?php echo get_option('line_official_qr_code');?>">
            </fieldset>
            </div>
            <?php
        }

        function is_user_doc($doc_id=false, $user_id=false) {
            // Get the current user ID
            if (!$user_id) $user_id = get_current_user_id();    
            // Get the user's doc IDs as an array
            $user_docs = get_user_meta($user_id, 'user_doc_ids', true);
            // If $user_docs is not an array, convert it to an array
            if (!is_array($user_docs)) $user_docs = array();
            // Check if the current user has the specified doc ID in their metadata
            return in_array($doc_id, $user_docs);
        }

        function set_user_doc_data() {
            $response = array('success' => false, 'error' => 'Invalid data format');
            if (isset($_POST['_doc_id'])) {
                $doc_id = sanitize_text_field($_POST['_doc_id']);
                $user_id = sanitize_text_field($_POST['_user_id']);
                $is_user_doc = sanitize_text_field($_POST['_is_user_doc']);

                if (!isset($user_id)) $user_id = get_current_user_id();
                $user_doc_ids = get_user_meta($user_id, 'user_doc_ids', true);
                if (!is_array($user_doc_ids)) $user_doc_ids = array();
                $doc_exists = in_array($doc_id, $user_doc_ids);

                // Check the condition and update 'user_doc_ids' accordingly
                if ($is_user_doc == 1 && !$doc_exists) {
                    // Add $doc_id to 'user_doc_ids'
                    $user_doc_ids[] = $doc_id;
                } elseif ($is_user_doc != 1 && $doc_exists) {
                    // Remove $doc_id from 'user_doc_ids'
                    $user_doc_ids = array_diff($user_doc_ids, array($doc_id));
                }        
                // Update 'user_doc_ids' meta value
                update_user_meta( $user_id, 'user_doc_ids', $user_doc_ids);
                $response = array('success' => true);
            }
            wp_send_json($response);
        }

        // Site job
        function display_site_job_list($initial=false) {
            ob_start();
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            $is_site_admin = get_user_meta($current_user_id, 'is_site_admin', true);
            $current_user = get_userdata($current_user_id);

            // Check if the user is administrator
            if ($is_site_admin==1 || current_user_can('administrator') || $initial) {
                ?>
                <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
                <h2 style="display:inline;"><?php echo __( '工作職掌', 'your-text-domain' );?></h2>
                <fieldset>
                    <div style="display:flex; justify-content:space-between; margin:5px;">
                        <div>
                            <select id="select-profile">
                                <option value="0"><?php echo __( '我的帳號', 'your-text-domain' );?></option>
                                <option value="1"><?php echo __( '組織設定', 'your-text-domain' );?></option>
                                <option value="2" selected><?php echo __( '工作職掌', 'your-text-domain' );?></option>
                                <option value="3"><?php echo __( '文件類別', 'your-text-domain' );?></option>
                            </select>
                        </div>
                        <div style="text-align: right">
                            <input type="text" id="search-site-job" style="display:inline" placeholder="Search..." />
                        </div>
                    </div>
        
                    <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th>#</th>
                            <th><?php echo __( 'Job', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Department', 'your-text-domain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        // Define the custom pagination parameters
                        $posts_per_page = get_option('operation_row_counts');
                        $current_page = max(1, get_query_var('paged')); // Get the current page number
                        $query = $this->retrieve_site_job_list_data($current_page);
                        $total_posts = $query->found_posts;
                        $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages

                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                $job_number = get_post_meta(get_the_ID(), 'job_number', true);
                                $department = get_post_meta(get_the_ID(), 'department', true);
                                $doc_number = get_post_meta(get_the_ID(), 'doc_number', true);
                                $content = get_the_content().'('.$doc_number.')';
                                ?>
                                <tr id="edit-site-job-<?php the_ID();?>">
                                    <td style="text-align:center;"><?php echo esc_html($job_number);?></td>
                                    <td style="text-align:center;"><?php the_title();?></td>
                                    <td width="70%"><?php echo esc_html($content);?></td>
                                    <td style="text-align:center;"><?php echo esc_html($department);?></td>
                                </tr>
                                <?php 
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                        </tbody>
                    </table>
                    <div id="new-site-job" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                    <div class="pagination">
                        <?php
                        // Display pagination links
                        if ($current_page > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($current_page - 1)) . '"> < </a></span>';
                        echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $current_page, $total_pages) . '</span>';
                        if ($current_page < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($current_page + 1)) . '"> > </a></span>';
                        ?>
                    </div>
                    </fieldset>        
                </fieldset>
                <?php echo $this->display_site_job_dialog();?>                
                <?php
            } else {
                ?>
                <p><?php echo __( 'You do not have permission to access this page.', 'your-text-domain' );?></p>
                <?php
            }
            $html = ob_get_clean();
            return $html;
        }

        function retrieve_site_job_list_data($current_page = 1) {
            // Define the custom pagination parameters
            $posts_per_page = get_option('operation_row_counts');

            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $is_site_admin = get_user_meta($current_user_id, 'is_site_admin', true);
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
            if (empty($user_doc_ids)) $user_doc_ids=array();

            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => $posts_per_page,
                'paged'          => $current_page,
                'meta_query'     => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
                'meta_key'       => 'job_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );

            if (!current_user_can('administrator') && !$is_site_admin) {
                $args['post__in'] = $user_doc_ids; // Value is the array of job post IDs
            }

            if ($current_page==0) $args['posts_per_page'] = -1;

            $search_query = sanitize_text_field($_GET['_search']);
            if ($search_query) $args['paged'] = 1;

            if ($search_query) {
                $args['meta_query'][] = array(
                    'key'     => 'job_number',
                    'value'   => $search_query,
                    'compare' => '=',
                );
            }

            $query = new WP_Query($args);

            // Check if $query is empty and search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Loop through meta query array to find and remove 'job_number'
                foreach ($args['meta_query'] as $key => $meta_query) {
                    if (isset($meta_query['key']) && $meta_query['key'] === 'job_number') {
                        unset($args['meta_query'][$key]);
                        break; // Stop looping once 'job_number' is found and removed
                    }
                }            
                // Set the search query parameter
                $args['s'] = $search_query;            
                // Reset pagination to page 1
                $args['paged'] = 1;
                $query = new WP_Query($args);
            }

            return $query;
        }

        function get_site_job_list_data() {
            $response = array('html_contain' => $this->display_site_job_list());
            wp_send_json($response);
        }

        function display_site_job_dialog($doc_id=false) {
            $documents_class = new display_documents();
            $job_number = get_post_meta($doc_id, 'job_number', true);
            $job_title = get_the_title($doc_id);
            $job_content = get_post_field('post_content', $doc_id);
            $department = get_post_meta($doc_id, 'department', true);
            ob_start();
            ?>
            <div id="site-job-dialog" title="Job dialog">
            <fieldset>
                <input type="hidden" id="doc-id" value="<?php echo esc_attr($doc_id);?>" />
                <label for="job-number">Number:</label>
                <input type="text" id="job-number" value="<?php echo esc_attr($job_number);?>" class="text ui-widget-content ui-corner-all" />
                <label for="job-title">Title:</label>
                <input type="text" id="job-title" value="<?php echo esc_attr($job_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="job-content">Content:</label>
                <textarea id="job-content" rows="3" style="width:100%;"><?php echo esc_attr($job_content);?></textarea>
                <div class="separator"></div>
                <?php echo $this->display_doc_action_list($doc_id);?>
                <label for="department">Department:</label>
                <input type="text" id="department" value="<?php echo esc_attr($department);?>" class="text ui-widget-content ui-corner-all" />
            </fieldset>
            </div>
            <?php
            $html = ob_get_clean();
            return $html;            
        }

        function get_site_job_dialog_data() {
            $response = array();
            if( isset($_POST['_doc_id']) ) {
                $doc_id = sanitize_text_field($_POST['_doc_id']);
                $response = array('html_contain' => $this->display_site_job_dialog($doc_id));
            }
            wp_send_json($response);
        }

        function set_site_job_dialog_data() {
            $response = array();
            if( isset($_POST['_doc_id']) ) {
                $doc_id = sanitize_text_field($_POST['_doc_id']);
                $data = array(
                    'ID'           => $doc_id,
                    'post_title'   => sanitize_text_field($_POST['_job_title']),
                    'post_content' => sanitize_text_field($_POST['_job_content']),
                );
                wp_update_post( $data );
                update_post_meta($doc_id, 'job_number', sanitize_text_field($_POST['_job_number']));
                update_post_meta($doc_id, 'department', sanitize_text_field($_POST['_department']));
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                // new job
                $new_post = array(
                    'post_title'    => 'New job',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'document',
                );    
                $new_doc_id = wp_insert_post($new_post);
                update_post_meta($new_doc_id, 'site_id', $site_id);
                update_post_meta($new_doc_id, 'job_number', '-');
                // new action
                $new_post = array(
                    'post_title'    => 'OK',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'action',
                );    
                $new_action_id = wp_insert_post($new_post);
                update_post_meta($new_action_id, 'doc_id', $new_doc_id);
                update_post_meta($new_action_id, 'next_job', -1);
                update_post_meta($new_action_id, 'next_leadtime', 86400);
            }
            wp_send_json($response);
        }

        function del_site_job_dialog_data() {
            $response = array();
            $doc_id = sanitize_text_field($_POST['_doc_id']);
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            if ($doc_title) echo 'You cannot delete this document';
            else wp_delete_post($doc_id, true);
            wp_send_json($response);
        }

        // doc-action
        function display_doc_action_list($doc_id=false) {
            ob_start();
            ?>
            <div id="doc-action-list">
            <fieldset>
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th><?php echo __( 'Action', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Next', 'your-text-domain' );?></th>
                        <th><?php echo __( 'LeadTime', 'your-text-domain' );?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $query = $this->retrieve_doc_action_list_data($doc_id);
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        $action_title = get_the_title();
                        $action_content = get_post_field('post_content', get_the_ID());
                        $next_job = get_post_meta(get_the_ID(), 'next_job', true);
                        $next_job_title = get_the_title($next_job);
                        if ($next_job==-1) $next_job_title = __( '發行', 'your-text-domain' );
                        if ($next_job==-2) $next_job_title = __( '廢止', 'your-text-domain' );
                        $next_leadtime = get_post_meta(get_the_ID(), 'next_leadtime', true);
                        ?>
                        <tr id="edit-doc-action-<?php the_ID();?>">
                            <td style="text-align:center;"><?php echo esc_html($action_title);?></td>
                            <td><?php echo esc_html($action_content);?></td>
                            <td style="text-align:center;"><?php echo esc_html($next_job_title);?></td>
                            <td style="text-align:center;"><?php echo esc_html($next_leadtime);?></td>
                        </tr>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
                </tbody>
            </table>
            <div id="new-doc-action" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
            </fieldset>
            </div>
            <?php echo $this->display_doc_action_dialog();?>
            <?php
            $html = ob_get_clean();
            return $html;            
        }

        function find_next_job_query($query=false) {
            // Retrieve the IDs of the posts from the initial query
            $initial_ids = wp_list_pluck($query->posts, 'ID');            
            // Retrieve the meta values of "next_job" for the posts from the initial query
            $next_jobs = array();
            foreach ($initial_ids as $post_id) {
                $next_job = get_post_meta($post_id, 'next_job', true);
                if (!empty($next_job)) {
                    $next_jobs[] = $next_job;
                }
            }            
            // Additional query arguments to find posts with doc_id equal to next_job of initial results
            $additional_args = array(
                'post_type'      => 'action',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'     => 'doc_id',
                        'value'   => $next_jobs,
                        'compare' => 'IN',
                    ),
                ),
            );            
            // Perform the additional query
            $additional_query = new WP_Query($additional_args);            
            // Combine the results
            $combined_posts = array_merge($query->posts, $additional_query->posts);            
            // Create a new WP_Query object with the combined results
            $query = new WP_Query(array('post__in' => wp_list_pluck($combined_posts, 'ID'), 'post_type' => 'action', 'posts_per_page' => -1));
            return $query;
        }

        function retrieve_doc_action_list_data($doc_id = false, $nest = false) {
            // Initial query arguments
            $args = array(
                'post_type'      => 'action',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'   => 'doc_id',
                        'value' => $doc_id,
                    ),
                ),
            );        
            // Perform the initial query
            $query = new WP_Query($args);

            if ($nest) {
                $this->find_next_job_query($query);
/*                
                // Retrieve the IDs of the posts from the initial query
                $initial_ids = wp_list_pluck($query->posts, 'ID');            
                // Retrieve the meta values of "next_job" for the posts from the initial query
                $next_jobs = array();
                foreach ($initial_ids as $post_id) {
                    $next_job = get_post_meta($post_id, 'next_job', true);
                    if (!empty($next_job)) {
                        $next_jobs[] = $next_job;
                    }
                }            
                // Additional query arguments to find posts with doc_id equal to next_job of initial results
                $additional_args = array(
                    'post_type'      => 'action',
                    'posts_per_page' => -1,
                    'meta_query'     => array(
                        array(
                            'key'     => 'doc_id',
                            'value'   => $next_jobs,
                            'compare' => 'IN',
                        ),
                    ),
                );            
                // Perform the additional query
                $additional_query = new WP_Query($additional_args);            
                // Combine the results
                $combined_posts = array_merge($query->posts, $additional_query->posts);            
                // Create a new WP_Query object with the combined results
                $query = new WP_Query(array('post__in' => wp_list_pluck($combined_posts, 'ID'), 'post_type' => 'action', 'posts_per_page' => -1));
*/                
            }

            return $query;
        }

        function get_doc_action_list_data() {
            $response = array();
            if (isset($_POST['_doc_id'])) {
                $doc_id = sanitize_text_field($_POST['_doc_id']);
                $response['html_contain'] = $this->display_doc_action_list($doc_id);
            }
            wp_send_json($response);
        }

        function display_doc_action_dialog($action_id=false){
            $action_title = get_the_title($action_id);
            $action_content = get_post_field('post_content', $action_id);
            $next_job = get_post_meta($action_id, 'next_job', true);
            $next_leadtime = get_post_meta($action_id, 'next_leadtime', true);
            ob_start();
            ?>
            <div id="doc-action-dialog" title="Action dialog">
            <fieldset>
                <input type="hidden" id="action-id" value="<?php echo esc_attr($action_id);?>" />
                <label for="action-title">Title:</label>
                <input type="text" id="action-title" value="<?php echo esc_attr($action_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="action-content">Content:</label>
                <input type="text" id="action-content" value="<?php echo esc_attr($action_content);?>" class="text ui-widget-content ui-corner-all" />
                <label for="next-job">Next job:</label>
                <select id="next-job" class="text ui-widget-content ui-corner-all" ><?php echo $this->select_site_job_option_data($next_job);?></select>
                <label for="next-leadtime">Next leadtime:</label>
                <input type="text" id="next-leadtime" value="<?php echo esc_attr($next_leadtime);?>" class="text ui-widget-content ui-corner-all" />
            </fieldset>
            </div>
            <?php
            $html = ob_get_clean();
            return $html;            
        }

        function get_doc_action_dialog_data() {
            $response = array();
            if (isset($_POST['_action_id'])) {
                $action_id = sanitize_text_field($_POST['_action_id']);
                $response['html_contain'] = $this->display_doc_action_dialog($action_id);
            }
            wp_send_json($response);
        }

        function set_doc_action_dialog_data() {
            $response = array();
            if( isset($_POST['_action_id']) ) {
                $data = array(
                    'ID'         => sanitize_text_field($_POST['_action_id']),
                    'post_title' => sanitize_text_field($_POST['_action_title']),
                    'post_content' => sanitize_text_field($_POST['_action_content']),
                    'meta_input' => array(
                        'next_job'   => sanitize_text_field($_POST['_next_job']),
                        'next_leadtime' => sanitize_text_field($_POST['_next_leadtime']),
                    )
                );
                wp_update_post( $data );
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_title'    => 'New action',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'action',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta( $post_id, 'doc_id', sanitize_text_field($_POST['_doc_id']) );
                update_post_meta( $post_id, 'next_job', -1);
                update_post_meta( $post_id, 'next_leadtime', 86400);
            }
            wp_send_json($response);
        }

        function del_doc_action_dialog_data() {
            $response = array();
            wp_delete_post($_POST['_action_id'], true);
            wp_send_json($response);
        }

        function select_site_job_option_data($selected_option=0) {
            $options = '<option value="">Select job</option>';
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    'key'   => 'site_id',
                    'value' => $site_id,
                ),
                'orderby'        => 'meta_value',
                'meta_key'       => 'job_number',
                'order'          => 'ASC',
            );

            $query = new WP_Query($args);

            while ($query->have_posts()) : $query->the_post();
                $job_number = get_post_meta(get_the_ID(), 'job_number', true);
                $job_title = get_the_title().'('.$job_number.')';
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html($job_title) . '</option>';
            endwhile;
            wp_reset_postdata();
            if ($selected_option==-1){
                $options .= '<option value="-1" selected>'.__( '發行', 'your-text-domain' ).'</option>';
            } else {
                $options .= '<option value="-1">'.__( '發行', 'your-text-domain' ).'</option>';
            }
            if ($selected_option==-2){
                $options .= '<option value="-2" selected>'.__( '廢止', 'your-text-domain' ).'</option>';
            } else {
                $options .= '<option value="-2">'.__( '廢止', 'your-text-domain' ).'</option>';
            }
            return $options;
        }

        // doc-category
        function display_doc_category_list() {
            ob_start();
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            $is_site_admin = get_user_meta($current_user_id, 'is_site_admin', true);
            $current_user = get_userdata($current_user_id);

            if ($is_site_admin==1 || current_user_can('administrator')) {
                // Check if the user is administrator
                ?>
                <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
                <h2 style="display:inline;"><?php echo __( '文件類別', 'your-text-domain' );?></h2>
                <fieldset>
                    <div style="display:flex; justify-content:space-between; margin:5px;">
                        <div>
                            <select id="select-profile">
                                <option value="0"><?php echo __( '我的帳號', 'your-text-domain' );?></option>
                                <option value="1"><?php echo __( '組織設定', 'your-text-domain' );?></option>
                                <option value="2"><?php echo __( '工作職掌', 'your-text-domain' );?></option>
                                <option value="3" selected><?php echo __( '文件類別', 'your-text-domain' );?></option>
                            </select>
                        </div>
                        <div style="text-align: right">
                        </div>
                    </div>

                    <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'Category', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        $query = $this->retrieve_doc_category_data();
                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                ?>
                                <tr id="edit-doc-category-<?php the_ID();?>">
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
                    <div id="new-doc-category" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                    </fieldset>        
                </fieldset>
                <?php $this->display_doc_category_dialog();?>
                <?php
            } else {
                ?>
                <p><?php echo __( 'You do not have permission to access this page.', 'your-text-domain' );?></p>
                <?php
            }
            $html = ob_get_clean();
            return $html;
        }

        function retrieve_doc_category_data() {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $args = array(
                'post_type'      => 'doc-category',
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

        function get_doc_category_list_data() {
            $response = array('html_contain' => $this->display_doc_category_list());
            wp_send_json($response);
        }

        function display_doc_category_dialog() {
            ?>
            <div id="doc-category-dialog" title="Category dialog" style="display:none;">
            <fieldset>
                <input type="hidden" id="category-id" />
                <label for="category-title"><?php echo __( 'Category: ', 'your-text-domain' );?></label>
                <input type="text" id="category-title" class="text ui-widget-content ui-corner-all" />
                <label for="category-content"><?php echo __( 'Description: ', 'your-text-domain' );?></label>
                <textarea id="category-content" rows="3" style="width:100%;"></textarea>
            </fieldset>
            </div>
            <?php
        }

        function get_doc_category_dialog_data() {
            $response = array();
            if( isset($_POST['_category_id']) ) {
                $category_id = sanitize_text_field($_POST['_category_id']);
                $response["category_title"] = get_the_title($category_id);
                $response["category_content"] = get_post_field('post_content', $category_id);
            }
            wp_send_json($response);
        }

        function set_doc_category_dialog_data() {
            $response = array();
            if( isset($_POST['_category_id']) ) {
                $category_id = sanitize_text_field($_POST['_category_id']);
                $data = array(
                    'ID'           => $category_id,
                    'post_title'   => sanitize_text_field($_POST['_category_title']),
                    'post_content' => sanitize_text_field($_POST['_category_content']),
                );
                wp_update_post( $data );
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => 'New category',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'doc-category',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
            }
            wp_send_json($response);
        }

        function del_doc_category_dialog_data() {
            $response = array();
            wp_delete_post($_POST['_category_id'], true);
            wp_send_json($response);
        }

        function select_doc_category_option_data($selected_option=0) {
            $query = $this->retrieve_doc_category_data();
            $options = '<option value="">Select category</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }
    }
    $profiles_class = new display_profiles();
}


