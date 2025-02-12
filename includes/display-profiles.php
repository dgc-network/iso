<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('display_profiles')) {
    class display_profiles {
        // Class constructor
        public function __construct() {
            add_shortcode( 'display-profiles', array( $this, 'display_profiles' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_display_profile_scripts' ) );
            //add_action( 'init', array( $this, 'register_site_profile_post_type' ) );
            //add_action( 'init', array( $this, 'register_exception_notification_setting_post_type' ) );

            add_action( 'wp_ajax_set_my_profile_data', array( $this, 'set_my_profile_data' ) );
            add_action( 'wp_ajax_nopriv_set_my_profile_data', array( $this, 'set_my_profile_data' ) );

            add_action( 'wp_ajax_get_my_job_action_list_data', array( $this, 'get_my_job_action_list_data' ) );
            add_action( 'wp_ajax_nopriv_get_my_job_action_list_data', array( $this, 'get_my_job_action_list_data' ) );
            add_action( 'wp_ajax_get_my_job_action_dialog_data', array( $this, 'get_my_job_action_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_my_job_action_dialog_data', array( $this, 'get_my_job_action_dialog_data' ) );
            add_action( 'wp_ajax_set_my_job_action_dialog_data', array( $this, 'set_my_job_action_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_my_job_action_dialog_data', array( $this, 'set_my_job_action_dialog_data' ) );

            add_action( 'wp_ajax_get_site_profile_data', array( $this, 'get_site_profile_data' ) );
            add_action( 'wp_ajax_nopriv_get_site_profile_data', array( $this, 'get_site_profile_data' ) );
            add_action( 'wp_ajax_set_site_profile_data', array( $this, 'set_site_profile_data' ) );
            add_action( 'wp_ajax_nopriv_set_site_profile_data', array( $this, 'set_site_profile_data' ) );
            add_action( 'wp_ajax_set_site_user_doc_data', array( $this, 'set_site_user_doc_data' ) );
            add_action( 'wp_ajax_nopriv_set_site_user_doc_data', array( $this, 'set_site_user_doc_data' ) );

            add_action( 'wp_ajax_get_site_profile_content', array( $this, 'get_site_profile_content' ) );
            add_action( 'wp_ajax_nopriv_get_site_profile_content', array( $this, 'get_site_profile_content' ) );
            add_action( 'wp_ajax_set_NDA_assignment', array( $this, 'set_NDA_assignment' ) );
            add_action( 'wp_ajax_nopriv_set_NDA_assignment', array( $this, 'set_NDA_assignment' ) );

            add_action( 'wp_ajax_get_site_user_dialog_data', array( $this, 'get_site_user_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_site_user_dialog_data', array( $this, 'get_site_user_dialog_data' ) );
            add_action( 'wp_ajax_set_site_user_dialog_data', array( $this, 'set_site_user_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_site_user_dialog_data', array( $this, 'set_site_user_dialog_data' ) );
            add_action( 'wp_ajax_del_site_user_dialog_data', array( $this, 'del_site_user_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_site_user_dialog_data', array( $this, 'del_site_user_dialog_data' ) );

            add_action( 'wp_ajax_get_site_action_dialog_data', array( $this, 'get_site_action_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_site_action_dialog_data', array( $this, 'get_site_action_dialog_data' ) );
            add_action( 'wp_ajax_set_site_action_dialog_data', array( $this, 'set_site_action_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_site_action_dialog_data', array( $this, 'set_site_action_dialog_data' ) );
            add_action( 'wp_ajax_del_site_action_dialog_data', array( $this, 'del_site_action_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_site_action_dialog_data', array( $this, 'del_site_action_dialog_data' ) );

            add_action( 'wp_ajax_get_new_action_user', array( $this, 'get_new_action_user' ) );
            add_action( 'wp_ajax_nopriv_get_new_action_user', array( $this, 'get_new_action_user' ) );                                                                    
            add_action( 'wp_ajax_set_action_user_data', array( $this, 'set_action_user_data' ) );
            add_action( 'wp_ajax_nopriv_set_action_user_data', array( $this, 'set_action_user_data' ) );                                                                    
            add_action( 'wp_ajax_del_action_user_data', array( $this, 'del_action_user_data' ) );
            add_action( 'wp_ajax_nopriv_del_action_user_data', array( $this, 'del_action_user_data' ) );                                                                    



            add_action( 'wp_ajax_get_site_job_dialog_data', array( $this, 'get_site_job_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_site_job_dialog_data', array( $this, 'get_site_job_dialog_data' ) );
            add_action( 'wp_ajax_set_site_job_dialog_data', array( $this, 'set_site_job_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_site_job_dialog_data', array( $this, 'set_site_job_dialog_data' ) );
            add_action( 'wp_ajax_del_site_job_dialog_data', array( $this, 'del_site_job_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_site_job_dialog_data', array( $this, 'del_site_job_dialog_data' ) );

            add_action( 'wp_ajax_get_doc_action_dialog_data', array( $this, 'get_doc_action_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_doc_action_dialog_data', array( $this, 'get_doc_action_dialog_data' ) );                                                                    
            add_action( 'wp_ajax_set_doc_action_dialog_data', array( $this, 'set_doc_action_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_doc_action_dialog_data', array( $this, 'set_doc_action_dialog_data' ) );                                                                    
            add_action( 'wp_ajax_del_doc_action_dialog_data', array( $this, 'del_doc_action_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_doc_action_dialog_data', array( $this, 'del_doc_action_dialog_data' ) );                                                                    

            add_action( 'wp_ajax_get_new_user_list', array( $this, 'get_new_user_list' ) );
            add_action( 'wp_ajax_nopriv_get_new_user_list', array( $this, 'get_new_user_list' ) );                                                                    
            add_action( 'wp_ajax_add_doc_user_data', array( $this, 'add_doc_user_data' ) );
            add_action( 'wp_ajax_nopriv_add_doc_user_data', array( $this, 'add_doc_user_data' ) );                                                                    
            add_action( 'wp_ajax_del_doc_user_data', array( $this, 'del_doc_user_data' ) );
            add_action( 'wp_ajax_nopriv_del_doc_user_data', array( $this, 'del_doc_user_data' ) );                                                                    

            add_action( 'wp_ajax_get_site_list_data', array( $this, 'get_site_list_data' ) );
            add_action( 'wp_ajax_nopriv_get_site_list_data', array( $this, 'get_site_list_data' ) );
            add_action( 'wp_ajax_get_site_dialog_data', array( $this, 'get_site_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_site_dialog_data', array( $this, 'get_site_dialog_data' ) );
        }

        function enqueue_display_profile_scripts() {
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);        

            wp_enqueue_script('display-profiles', plugins_url('js/display-profiles.js', __FILE__), array('jquery'), time());
            wp_localize_script('display-profiles', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('display-profiles-nonce'), // Generate nonce
            ));                
        }        

        // Select profile
        function display_select_profile($select_option=false) {
            ?>
            <select id="select-profile">
                <option value="my-profile" <?php echo ($select_option=="my-profile") ? 'selected' : ''?>><?php echo __( 'My Account', 'textdomain' );?></option>
                <option value="site-profile" <?php echo ($select_option=="site-profile") ? 'selected' : ''?>><?php echo __( 'Site Configuration', 'textdomain' );?></option>
                <option value="department-card" <?php echo ($select_option=="department-card") ? 'selected' : ''?>><?php echo __( 'Departments', 'textdomain' );?></option>
                <option value="doc-category" <?php echo ($select_option=="doc-category") ? 'selected' : ''?>><?php echo __( 'Categories', 'textdomain' );?></option>
            </select>
            <?php
        }

        // Shortcode to display
        function display_profiles() {
            // Check if the user is logged in
            if (!is_user_logged_in()) user_is_not_logged_in();                
            elseif (is_site_not_configured()) display_NDA_assignment();
            elseif (isset($_GET['_nda_user_id'])) echo $this->approve_NDA_assignment($_GET['_nda_user_id']);
            else {

                echo '<div class="ui-widget" id="result-container">';

                if (!isset($_GET['_select_profile'])) $_GET['_select_profile'] = 'my-profile';
                if ($_GET['_select_profile']=='my-profile') echo $this->display_my_profile();
                if ($_GET['_select_profile']=='site-profile') echo $this->display_site_profile();
                if ($_GET['_select_profile']=='site-job') echo $this->display_site_job_list();
                if ($_GET['_select_profile']=='user-list') echo $this->display_site_user_list(-1);
                $items_class = new embedded_items();
                if ($_GET['_select_profile']=='doc-category') echo $items_class->display_doc_category_list();
                if ($_GET['_select_profile']=='iso-category') echo $items_class->display_iso_category_list();
                if ($_GET['_select_profile']=='department-card') echo $items_class->display_department_card_list();

                echo '</div>';

                if ($_GET['_select_profile']=='update_action_site_id_by_document') echo $this->update_action_site_id_by_document();
            }
        }

        function update_action_site_id_by_document() {
            $action_query = new WP_Query([
                'post_type'  => 'action',
                'posts_per_page' => -1
            ]);

            if ($action_query->have_posts()) {
                foreach ($action_query->posts as $action_post) {
                    $doc_id = get_post_meta($action_post->ID, 'doc_id', true);
                    $site_id = get_post_meta($doc_id, 'site_id', true);
                    update_post_meta($action_post->ID, 'site_id', $site_id);
                }
            }
            wp_reset_postdata();
        }
        
        function update_post_type_and_meta_for_embedded_items() {
            // Step 1: Query posts of post type 'doc-field' with meta key 'default_value'
            $args = array(
                'post_type'      => 'doc-field',
                'meta_key'       => 'default_value',
                'posts_per_page' => -1,
                'post_status'    => 'any',
            );
            $query = new WP_Query($args);
        
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    // Retrieve the embedded number from 'default_value'
                    $embedded_number = get_post_meta(get_the_ID(), 'default_value', true);
                    $doc_id = get_post_meta(get_the_ID(), 'doc_id', true);
        
                    if (!empty($embedded_number)) {
                        // Step 2: Query the post with post type 'embedded' and meta key 'embedded_number'
                        $args_embedded = array(
                            'post_type'      => 'embedded',
                            'meta_key'       => 'embedded_number',
                            'meta_value'     => $embedded_number,
                            'posts_per_page' => 1,
                            'post_status'    => 'any',
                        );
        
                        $embedded_query = new WP_Query($args_embedded);
        
                        if ($embedded_query->have_posts()) {
                            $embedded_query->the_post();
                            $matched_post_id = get_the_ID(); // Matched 'embedded' post ID
                            wp_reset_postdata();
        
                            // Step 3: Query posts of post type 'embedded-item' with meta key 'embedded_id'
                            $args_update = array(
                                'post_type'      => 'embedded-item',
                                'meta_key'       => 'embedded_id',
                                'meta_value'     => $matched_post_id,
                                'posts_per_page' => -1,
                                'post_status'    => 'any',
                            );
        
                            $query_update = new WP_Query($args_update);
        
                            if ($query_update->have_posts()) {
                                while ($query_update->have_posts()) {
                                    $query_update->the_post();
        
                                    // Step 4: Replace meta keys and update the post type
                                    $post_id = get_the_ID();

                                    if ($doc_id) {
                                        update_post_meta($post_id, 'doc_id', $doc_id);
                                        delete_post_meta($post_id, 'embedded_id');
                                    }
        
                                    // Fetch the current title to ensure it is preserved
                                    $current_title = get_the_title($post_id);
        
                                    // Update post type to 'doc-field'
                                    $post_args = array(
                                        'ID'          => $post_id,
                                        'post_type'   => 'doc-field',
                                        'post_title'  => $current_title, // Preserve the title
                                    );
        
                                    wp_update_post($post_args);
        
                                    // Optional: Log the updated post ID for debugging
                                    error_log('Post ID updated to doc-field with meta changes: ' . $post_id);
                                }
                                wp_reset_postdata();
                            }

                            // Step 5: Delete the matched 'embedded' post
                            wp_delete_post($matched_post_id, true); // true to force deletion
                            error_log('Deleted embedded post ID: ' . $matched_post_id);

                        }
                    }
                }
                wp_reset_postdata();
            } else {
                // No posts found for the given parameters
                error_log('No doc-field posts found with meta key "default_value".');
            }
        }

        // my-profile
        function display_my_profile() {
            ob_start();
            $current_user_id = get_current_user_id();
            $current_user = get_userdata( $current_user_id );
            $phone_number = get_user_meta($current_user_id, 'phone_number', true);
            $gemini_api_key = get_user_meta($current_user_id, 'gemini_api_key', true);
            ?>
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( 'My Account', 'textdomain' );?></h2>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $this->display_select_profile('my-profile');?></div>
                <div style="text-align: right">
                </div>
            </div>    
            <fieldset>
                <label for="display-name"><?php echo __( 'Name', 'textdomain' );?></label>
                <input type="text" id="display-name" value="<?php echo $current_user->display_name;?>" class="text ui-widget-content ui-corner-all" />
                <label for="user-email"><?php echo __( 'Email', 'textdomain' );?></label>
                <input type="text" id="user-email" value="<?php echo $current_user->user_email;?>" class="text ui-widget-content ui-corner-all" />
                <label for="my-job-list"><?php echo __( 'Jobs & Authorizations', 'textdomain' );?></label>
                <div id="my-job-list"><?php echo $this->display_my_job_list();?></div>
                <label for="phone-number"><?php echo __( 'Phone', 'textdomain' );?></label>
                <input type="text" id="phone-number" value="<?php echo $phone_number;?>" class="text ui-widget-content ui-corner-all" />
                <label for="gemini-api-key"><?php echo __( 'Gemini API key', 'textdomain' );?></label>
                <input type="password" id="gemini-api-key" value="<?php echo $gemini_api_key;?>" class="text ui-widget-content ui-corner-all" />
                <?php
                // transaction data vs card key/value
                $key_value_pair = array(
                    '_employee' => get_current_user_id(),
                );
                $documents_class = new display_documents();
                $documents_class->get_transactions_by_key_value_pair($key_value_pair);
                // exception notification setting
                $iot_messages = new iot_messages();
                $is_display = ($iot_messages->is_site_with_iot_device()) ? '' : 'display:none;';
                ?>
                <div style=<?php echo $is_display;?>>
                    <label id="my-exception-notification-setting-label" class="button"><?php echo __( 'Exception Notification Settings', 'textdomain' );?></label>
                    <div id="my-exception-notification-setting"><?php echo $iot_messages->display_exception_notification_setting_list();?></div>
                </div>
            </fieldset>
            <button type="submit" id="my-profile-submit" style="margin:3px;"><?php echo __( 'Submit', 'textdomain' );?></button>
            <?php
            return ob_get_clean();
        }

        function set_my_profile_data() {
            $response = array();
            $current_user_id = get_current_user_id();
            wp_update_user(array('ID' => $current_user_id, 'display_name' => $_POST['_display_name']));
            wp_update_user(array('ID' => $current_user_id, 'user_email' => $_POST['_user_email']));
            update_user_meta( $current_user_id, 'phone_number', $_POST['_phone_number']);
            update_user_meta( $current_user_id, 'gemini_api_key', $_POST['_gemini_api_key']);
            $response = array('success' => true);
            wp_send_json($response);
        }

        // my-job
        function display_my_job_list() {
            ob_start();
            $current_user_id = get_current_user_id();
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            ?>
            <fieldset style="margin-top:5px;">
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'No.', 'textdomain' );?></th>
                        <th><?php echo __( 'Job', 'textdomain' );?></th>
                        <th><?php echo __( 'Description', 'textdomain' );?></th>
                        <th><?php echo __( 'Authorized', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php    
                    // Accessing elements of the array
                    if (is_array($user_doc_ids)) {
                        $documents = array();
                        foreach ($user_doc_ids as $doc_id) {
                            $doc_site = get_post_meta($doc_id, 'site_id', true);
                            if ($doc_site == $site_id) {
                                $job_number = get_post_meta($doc_id, 'job_number', true);
                                $job_title = get_the_title($doc_id);
                                $doc_content = get_post_field('post_content', $doc_id);
                                $doc_number = get_post_meta($doc_id, 'doc_number', true);
                                $doc_title = get_post_meta($doc_id, 'doc_title', true);
                                if ($doc_number) $doc_title.='('.$doc_number.')';
                                else $doc_title=$doc_content;
                                $is_checked = $this->is_doc_authorized($doc_id) ? 'checked' : '';
                                // Add to documents array
                                $documents[] = array(
                                    'doc_id' => $doc_id,
                                    'job_number' => $job_number,
                                    'job_title' => $job_title,
                                    'doc_title' => $doc_title,
                                    'is_checked' => $is_checked
                                );
                            }
                        }

                        // Sort documents by job_number
                        usort($documents, function($a, $b) {
                            return strcmp($a['job_number'], $b['job_number']);
                        });

                        // Display sorted documents
                        foreach ($documents as $doc) {
                            ?>
                            <tr id="edit-my-job-<?php echo $doc['doc_id']; ?>">
                                <td style="text-align:center;"><?php echo esc_html($doc['job_number']); ?></td>
                                <td style="text-align:center;"><?php echo esc_html($doc['job_title']); ?></td>
                                <td><?php echo esc_html($doc['doc_title']); ?></td>
                                <td style="text-align:center;"><input type="radio" <?php echo $doc['is_checked']; ?> /></td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>
                <div id="my-job-action-list" title="Action authorization"></div>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function display_my_job_action_list($doc_id=false) {
            ob_start();
            $job_content = get_post_field('post_content', $doc_id);
            echo $job_content
            ?>
            <fieldset>
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th></th>
                        <th><?php echo __( 'Action', 'textdomain' );?></th>
                        <th><?php echo __( 'Description', 'textdomain' );?></th>
                        <th><?php echo __( 'Next', 'textdomain' );?></th>
                        <th><?php echo __( 'LeadTime', 'textdomain' );?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $query = $this->retrieve_doc_action_data($doc_id);
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        $action_id = get_the_ID();
                        $action_title = get_the_title();
                        $action_content = get_post_field('post_content', $action_id);
                        $next_job = get_post_meta($action_id, 'next_job', true);
                        $next_job_title = get_the_title($next_job);
                        $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
                        if ($next_job==-1) {
                            $next_job_title = __( 'Released', 'textdomain' );
                        }
                        if ($next_job==-2) {
                            $next_job_title = __( 'Removed', 'textdomain' );
                        }
                        $next_leadtime = get_post_meta($action_id, 'next_leadtime', true);
                        $is_checked = $this->is_action_authorized($action_id) ? 'checked' : '';
                        ?>
                        <tr id="edit-my-job-action-<?php echo $action_id;?>">
                            <td style="text-align:center;"><input type="radio" name="is_action_authorized" id="is-action-authorized-<?php echo $action_id;?>" <?php echo $is_checked;?> /></td>
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
            <div id="my-job-action-dialog" title="Action authorization"></div>
            </fieldset>
            </div>
            <?php
            return ob_get_clean();
        }

        function get_my_job_action_list_data() {
            if (isset($_POST['_doc_id'])) {
                $doc_id = sanitize_text_field($_POST['_doc_id']);
                $response = array('html_contain' => $this->display_my_job_action_list($doc_id));
            }
            wp_send_json($response);
        }

        function display_my_job_action_dialog($action_id=false) {
            ob_start();
            $todo_class = new to_do_list();
            $doc_id = get_post_meta($action_id, 'doc_id', true);
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $is_action_authorized = $this->is_action_authorized($action_id);
            $is_authorized = $this->is_action_authorized($action_id) ? __( 'Cancel Authorization', 'textdomain' ) : __( 'Prepare for Authorization', 'textdomain' );
            $frequence_report_setting = get_post_meta($action_id, 'frequence_report_setting', true);
            $frequence_report_start_time = get_post_meta($action_id, 'frequence_report_start_time', true);
            ?>
            <div>
                <h4>
                    <?php 
                    printf(
                        __( 'Set the action %s of the job %s', 'textdomain' ),
                        get_the_title($action_id),
                        get_the_title($doc_id),
                    );
                    ?>
                    → <span class="authorized-status"><?php echo esc_html($is_authorized); ?></span>
                </h4>                
                <input type="hidden" id="action-id" value="<?php echo $action_id;?>" />
                <input type="hidden" id="is-action-authorized" value="<?php echo $is_action_authorized;?>" />
                <label for="frequence-report-setting"><?php echo __( 'Cycle Form Start Settings', 'textdomain' );?></label>
                <select id="frequence-report-setting" class="text ui-widget-content ui-corner-all"><?php echo select_cron_schedules_option($frequence_report_setting);?></select>
                <div id="frquence-report-start-time-div">
                    <label for="frequence-report-start-time"><?php echo __( 'Cycle Form Start Time', 'textdomain' );?></label><br>
                    <input type="date" id="frequence-report-start-date" value="<?php echo wp_date('Y-m-d', $frequence_report_start_time);?>" />
                    <input type="time" id="frequence-report-start-time" value="<?php echo wp_date('H:i', $frequence_report_start_time);?>" />
                    <input type="hidden" id="prev-start-time" value="<?php echo $frequence_report_start_time;?>" />
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        function get_my_job_action_dialog_data() {
            if (isset($_POST['_action_id'])) {
                $action_id = sanitize_text_field($_POST['_action_id']);
                $response = array('html_contain' => $this->display_my_job_action_dialog($action_id));    
            }
            wp_send_json($response);
        }

        function set_my_job_action_dialog_data() {
            $response = array('success' => false, 'error' => 'Invalid data format');

            if (isset($_POST['_action_id']) && isset($_POST['_is_action_authorized'])) {
                $user_id = get_current_user_id();
                $action_id = sanitize_text_field($_POST['_action_id']);
                $is_action_authorized = sanitize_text_field($_POST['_is_action_authorized']);
                $action_authorized_ids = get_post_meta($action_id, 'action_authorized_ids', true);
                if (!is_array($action_authorized_ids)) $action_authorized_ids = array();
                $authorize_exists = in_array($user_id, $action_authorized_ids);
        
                // Check the condition and update 'action_authorized_ids' accordingly
                if (!$is_action_authorized && !$authorize_exists) {
                    // Add $user_id to 'action_authorized_ids'
                    $action_authorized_ids[] = $user_id;
                } elseif ($is_action_authorized && $authorize_exists) {
                    // Remove $user_id from 'action_authorized_ids'
                    $action_authorized_ids = array_diff($action_authorized_ids, array($user_id));
                }

                // Update 'action_authorized_ids' meta value
                update_post_meta($action_id, 'action_authorized_ids', $action_authorized_ids);

                // update the other actions
                $doc_id = get_post_meta($action_id, 'doc_id', true);
                $query = $this->retrieve_doc_action_data($doc_id);
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        $inner_action_id = get_the_ID();
                        if ($inner_action_id!=$action_id){
                            $action_authorized_ids = get_post_meta($inner_action_id, 'action_authorized_ids', true);
                            if (!is_array($action_authorized_ids)) $action_authorized_ids = array();
                            $authorize_exists = in_array($user_id, $action_authorized_ids);

                            if ($authorize_exists) {
                                // Remove $user_id from 'action_authorized_ids'
                                $action_authorized_ids = array_diff($action_authorized_ids, array($user_id));
                            }

                            // Update 'action_authorized_ids' meta value
                            update_post_meta($inner_action_id, 'action_authorized_ids', $action_authorized_ids);            
                        }
                    endwhile;
                    wp_reset_postdata();
                endif;

                // Get the timezone offset from WordPress settings
                $timezone_offset = get_option('gmt_offset');
                $offset_seconds = $timezone_offset * 3600; // Convert hours to seconds

                // Calculate and save start time
                $frequence_report_start_date = sanitize_text_field($_POST['_frequence_report_start_date']);
                $frequence_report_start_time = sanitize_text_field($_POST['_frequence_report_start_time']);
                $start_time = strtotime($frequence_report_start_date . ' ' . $frequence_report_start_time) - $offset_seconds;
                update_post_meta($action_id, 'frequence_report_start_time', $start_time);

                $hook_name = 'iso_helper_post_event';
                $interval = sanitize_text_field($_POST['_frequence_report_setting']);
                $args = array(
                    'action_id' => $action_id,
                    'user_id' => $user_id,
                );

                if (!$is_action_authorized && !$authorize_exists) {
                    // Frequency Report Setting
                    update_post_meta($action_id, 'frequence_report_setting', $interval);

                    // Check if an event with the same hook and args is already scheduled
                    if (!wp_next_scheduled($hook_name, array($args))) {
                        switch ($interval) {
                            case 'hourly':
                                wp_schedule_event($start_time, 'hourly', $hook_name, array($args));
                                break;
                            case 'twicedaily':
                                wp_schedule_event($start_time, 'twicedaily', $hook_name, array($args));
                                break;
                            case 'weekday_daily':
                                $hook_name = 'weekday_daily_post_event';
                                wp_schedule_event($start_time, 'weekday_daily', $hook_name, array($args));
                                break;
                            case 'daily':
                                wp_schedule_event($start_time, 'daily', $hook_name, array($args));
                                break;
                            case 'weekly':
                                wp_schedule_event($start_time, 'weekly', $hook_name, array($args));
                                break;
                            case 'biweekly':
                                wp_schedule_event($start_time, 'biweekly', $hook_name, array($args));
                                break;
                            case 'monthly':
                                wp_schedule_event($start_time, 'monthly', $hook_name, array($args));
                                break;
                            case 'bimonthly':
                                wp_schedule_event($start_time, 'bimonthly', $hook_name, array($args));
                                break;
                            case 'half_yearly':
                                wp_schedule_event($start_time, 'half_yearly', $hook_name, array($args));
                                break;
                            case 'yearly':
                                wp_schedule_event($start_time, 'yearly', $hook_name, array($args));
                                break;
                            default:
                                return new WP_Error('invalid_interval', 'The specified interval is invalid.');
                        }
                    }
                    // Store the hook name in options for later use
                    update_option('schedule_event_hook_name', $hook_name);

                } else {
                    delete_post_meta($action_id, 'frequence_report_setting');
                    delete_post_meta($action_id, 'frequence_report_start_time');
                    if ($interval=='weekday_daily') {
                        $hook_name = 'weekday_daily_post_event';
                    }
                    $cron_jobs = _get_cron_array(); // Fetch all cron jobs
                    if ($cron_jobs) {
                        foreach ($cron_jobs as $timestamp => $scheduled_hooks) {
                            if (isset($scheduled_hooks[$hook_name])) {
                                foreach ($scheduled_hooks[$hook_name] as $event) {
                                    // Check if event args match the specified args
                                    if (isset($event['args'][0]) && $event['args'][0] == $args) {
                                        wp_unschedule_event($timestamp, $hook_name, $event['args']);
                                    }
                                }
                            }
                        }
                    }
                }

                $response = array(
                    'success' => true, 
                    'action_id' => $action_id,
                    'is_action_authorized' => $is_action_authorized,
                    'authoriz_exists' => $authorize_exists,
                    'action_authorized_ids' => $action_authorized_ids,
                );
            }
            wp_send_json($response);
        }

        function is_doc_authorized($doc_id=false) {
            $query = $this->retrieve_doc_action_data($doc_id);
            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    $action_id = get_the_ID();
                    if ($this->is_action_authorized($action_id)) return true;
                endwhile;
                wp_reset_postdata();
            endif;
            return false;
        }

        function is_action_authorized($action_id=false, $user_id=false) {
            if (!$action_id) return false;
            if (!$user_id) $user_id = get_current_user_id();
            $action_authorized_ids = get_post_meta($action_id, 'action_authorized_ids', true);
            if (!is_array($action_authorized_ids)) $action_authorized_ids = array();

            if ($user_id) {
                return in_array($user_id, $action_authorized_ids) ? $action_authorized_ids : false;    
            } else {
                return $action_authorized_ids;
            }
        }

        // site-profile
        function register_site_profile_post_type() {
            $labels = array(
                'menu_name'     => _x('Site', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'site-profile', $args );
        }

        //function display_site_profile($_user_id=false) {
        function display_site_profile() {
            ob_start();
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            $site_content = get_post_field('post_content', $site_id);
            $unified_number = get_post_meta($site_id, 'unified_number', true);
            $company_phone = get_post_meta($site_id, 'company_phone', true);
            $company_address = get_post_meta($site_id, 'company_address', true);
            ?>
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( 'Site Configuration', 'textdomain' );?></h2>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $this->display_select_profile('site-profile');?></div>
                <div style="text-align: right">
                </div>
            </div>        

            <fieldset>
                <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
                <label for="site-title"><?php echo __( 'Site Name', 'textdomain' );?></label>
                <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" />
                <div id="site-hint" style="display:none; color:#999;"></div>

                <label for="site-logo"><?php echo __( 'LOGO', 'textdomain' );?></label>
                <div id="site-image-container">
                    <?php echo (isURL($image_url)) ? '<img src="' . esc_attr($image_url) . '" style="object-fit:cover; width:250px; height:250px;" class="button">' : '<a href="#" id="custom-image-href">Set image URL</a>'; ?>
                </div>
                <div id="site-image-url" style="display:none;">
                <fieldset>
                    <label for="image-url"><?php echo __( 'Image URL', 'textdomain' );?></label>
                    <textarea id="image-url" rows="3" style="width:99%;"><?php echo $image_url;?></textarea>
                    <button id="set-image-url" class="button"><?php echo __( 'Set', 'textdomain' );?></button>
                </fieldset>
                </div>

                <?php if (is_site_admin()) {?>
                    <label for="site-members"><?php echo __( 'Site Members', 'textdomain' );?></label>
                    <?php echo $this->display_site_user_list();?>
                <?php }?>

                <label for="site-content"><?php echo __( 'Non-Disclosure Agreement', 'textdomain' );?></label>
                <textarea id="site-content" rows="5" style="width:100%;"><?php echo esc_html($site_content);?></textarea>
                <label for="company-phone"><?php echo __( 'Phon', 'textdomain' );?></label>
                <input type="text" id="company-phone" value="<?php echo $company_phone;?>" class="text ui-widget-content ui-corner-all" />
                <label for="company-address"><?php echo __( 'Company Address', 'textdomain' );?></label>
                <textarea id="company-address" rows="2" style="width:100%;"><?php echo esc_html($company_address);?></textarea>
                <label for="unified-number"><?php echo __( 'Unified Number', 'textdomain' );?></label>
                <input type="text" id="unified-number" value="<?php echo $unified_number;?>" class="text ui-widget-content ui-corner-all" />

                <?php if (is_site_admin()) {?>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><label for="site-jobs"><?php echo __( 'Job List', 'textdomain' );?></label></div>
                    <div style="text-align: right">
                        <input type="text" id="search-site-job" style="display:inline" placeholder="<?php echo __( 'Search...', 'textdomain' );?>" />
                    </div>
                </div>
                <div id="site-job-list">
                    <?php //echo $this->display_site_job_list();?>
                </div>
                <div id="site-action-list">
                    <?php echo $this->display_site_action_list();?>
                </div>
                <?php }?>

            </fieldset>
            <?php if (is_site_admin()) {?>
                <button type="submit" id="site-profile-submit" style="margin:3px;"><?php echo __( 'Submit', 'textdomain' );?></button>
            <?php }?>
            <?php
            return ob_get_clean();
        }

        function get_site_profile_data() {
            $response = array('html_contain' => $this->display_site_profile());
            wp_send_json($response);
        }

        function set_site_profile_data() {
            $response = array('success' => false, 'error' => 'Invalid data format');
            if( isset($_POST['_site_id']) ) {
                $site_id = isset($_POST['_site_id']) ? sanitize_text_field($_POST['_site_id']) : 0;
                $site_title = isset($_POST['_site_title']) ? sanitize_text_field($_POST['_site_title']) : '';
                $company_phone = isset($_POST['_company_phone']) ? sanitize_text_field($_POST['_company_phone']) : '';
                $company_address = isset($_POST['_company_address']) ? sanitize_text_field($_POST['_company_address']) : '';
                $unified_number = isset($_POST['_unified_number']) ? sanitize_text_field($_POST['_unified_number']) : '';
                // Update the post
                $post_data = array(
                    'ID'           => $site_id,
                    'post_title'   => $site_title,
                    'post_content' => $_POST['_site_content'],
                );        
                wp_update_post($post_data);
                update_post_meta($site_id, 'image_url', $_POST['_image_url'] );
                update_post_meta($site_id, 'company_phone', $company_phone);
                update_post_meta($site_id, 'company_address', $company_address);
                update_post_meta($site_id, 'unified_number', $unified_number);
                $response = array('success' => true);

                if (isset($_POST['_keyValuePairs']) && is_array($_POST['_keyValuePairs'])) {
                    $keyValuePairs = $_POST['_keyValuePairs'];
                    $processedKeyValuePairs = [];
                    foreach ($keyValuePairs as $pair) {
                        foreach ($pair as $field_key => $field_value) {
                            // Sanitize the key and value
                            $field_key = sanitize_text_field($field_key);
                            $field_value = sanitize_text_field($field_value);
                            // Update post meta
                            update_post_meta($site_id, $field_key, $field_value);
                            // Add the sanitized pair to the processed array
                            $processedKeyValuePairs[$field_key] = $field_value;
                        }
                    }
                    // Prepare the response
                    $response = array('success' => true, 'data' => $processedKeyValuePairs);
                } else {
                    // Handle the error case
                    $response = array('success' => false, 'message' => 'No key-value pairs found or invalid format');
                }
            }
            wp_send_json($response);
        }

        // site-users
        function display_site_user_list($paged=1) {
            ob_start();
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            ?>
            <fieldset style="margin-top:5px;">
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Name', 'textdomain' );?></th>
                        <th><?php echo __( 'Email', 'textdomain' );?></th>
                        <th><?php echo __( 'Admin', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php        
                    $users = get_users(); // Initialize with all users

                    if ($paged==1) {
                        $meta_query_args = array(
                            array(
                                'key'     => 'site_id',
                                'value'   => $site_id,
                                //'compare' => '=',
                            ),
                        );
                        $users = get_users(array('meta_query' => $meta_query_args));
                    }
                    // Loop through the users
                    foreach ($users as $user) {
                        $user_site = get_user_meta($user->ID, 'site_id', true);
                        if ($user_site) $display_name = ($user_site == $site_id) ? $user->display_name : '*'.$user->display_name.'('.get_the_title($user_site).')';
                        else $display_name = ($user_site == $site_id) ? $user->display_name : $user->display_name.'<span style="color:red;">***</span>';
                        $is_admin_checked = (is_site_admin($user->ID, $site_id)) ? 'checked' : '';
                        ?>
                        <tr id="edit-site-user-<?php echo $user->ID; ?>">
                            <td style="text-align:center;"><?php echo $display_name; ?></td>
                            <td style="text-align:center;"><?php echo $user->user_email; ?></td>
                            <td style="text-align:center;"><input type="checkbox" <?php echo $is_admin_checked; ?>/></td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            </fieldset>
            <div id="site-user-dialog" title="User dialog"></div>
            <?php
            return ob_get_clean();
        }

        function display_site_user_dialog($user_id=false) {
            ob_start();
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $user_data = get_userdata($user_id);
            $user_site = get_user_meta($user_id, 'site_id', true);
            $is_admin_checked = (is_site_admin($user_id)) ? 'checked' : '';
            ?>
            <fieldset>
                <input type="hidden" id="user-id" value="<?php echo $user_id;?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="display-name"><?php echo __( 'Name', 'textdomain' );?></label>
                <input type="text" id="display-name" value="<?php echo $user_data->display_name;?>" class="text ui-widget-content ui-corner-all" />
                <label for="user-email"><?php echo __( 'Email', 'textdomain' );?></label>
                <input type="text" id="user-email" value="<?php echo $user_data->user_email;?>" class="text ui-widget-content ui-corner-all" />
                <label for="job-list"><?php echo __( 'Job List', 'textdomain' );?></label>
                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th></th>
                            <th><?php echo __( 'Job', 'textdomain' );?></th>
                            <th><?php echo __( 'Title', 'textdomain' );?></th>
                        </thead>
                        <tbody>
                            <?php
                            $query = $this->retrieve_site_job_list_data(0);
                            if ($query->have_posts()) {
                                while ($query->have_posts()) : $query->the_post();
                                    $job_id = get_the_ID();
                                    $job_title = get_the_title();
                                    $job_number = get_post_meta($job_id, 'job_number', true);
                                    $user_job_checked = $this->is_user_doc($job_id, $user_id) ? 'checked' : '';
                                    echo '<tr id="check-user-job-' . $job_id . '">';
                                    echo '<td style="text-align:center;"><input type="checkbox" id="is-user-doc-'.$job_id.'" ' . $user_job_checked . ' /></td>';
                                    echo '<td style="text-align:center;">' . esc_html($job_number) . '</td>';
                                    echo '<td style="text-align:center;">' . $job_title . '</td>';
                                    echo '</tr>';
                                endwhile;
                                wp_reset_postdata();                                    
                            }
                            ?>
                        </tbody>
                    </table>
                </fieldset>
                <?php
                $current_user_id = get_current_user_id();
                $current_site_id = get_user_meta($current_user_id, 'site_id', true);
                if (current_user_can('administrator')) {
                    ?>
                    <label for="select-site"><?php echo __( 'Site', 'textdomain' );?></label>
                    <select id="select-site" class="text ui-widget-content ui-corner-all" ><?php echo $this->select_site_profile_options($current_site_id);?></select>
                    <div>
                    <input type="checkbox" id="is-site-admin-setting" <?php echo $is_admin_checked;?> />
                    <label for="is-site-admin-setting"><?php echo __( 'Is site admin?', 'textdomain' );?></label>
                    </div>
                    <?php
                } else {
                    $site_ids = get_user_meta($user_id, 'site_admin_ids', true);
                    if (!empty($site_ids)) {
                        echo '<select id="select-site" class="text ui-widget-content ui-corner-all" >';
                        foreach ($site_ids as $site_id) {
                            $selected = ($site_id == $current_site_id) ? 'selected' : '';
                            echo '<option value="' . esc_attr($site_id) . '" ' . $selected . '>' . esc_html(get_the_title($site_id)) . '</option>';
                        }
                        echo '</select>';
                    }
                    ?>
                    <div>
                    <input type="checkbox" id="is-site-admin-setting" <?php echo $is_admin_checked;?> disabled />
                    <label for="is-site-admin-setting"><?php echo __( 'Is site admin?', 'textdomain' );?></label>
                    </div>
                    <?php
                }
                ?>
            </fieldset>
            <?php
            return ob_get_clean();
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
                    $is_site_admin = sanitize_text_field($_POST['_is_site_admin']);
                    update_user_meta($user_id, 'site_id', sanitize_text_field($_POST['_select_site']));
                    $this->set_site_admin_data($user_id, $is_site_admin);
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

        function set_site_admin_data($user_id=false, $is_site_admin=false) {
            if (!$user_id) $user_id = get_current_user_id();
            $site_id = get_user_meta($user_id, 'site_id', true);
            $site_admin_ids = get_user_meta($user_id, 'site_admin_ids', true);
            if (!is_array($site_admin_ids)) $site_admin_ids = array();
            $site_exists = in_array($site_id, $site_admin_ids);

            // Check the condition and update 'site_admin_ids' accordingly
            if ($is_site_admin && !$site_exists) {
                // Add $site_id to 'site_admin_ids'
                $site_admin_ids[] = $site_id;
            } elseif (!$is_site_admin && $site_exists) {
                // Remove $site_id from 'site_admin_ids'
                $site_admin_ids = array_diff($site_admin_ids, array($site_id));
            }        
            // Update 'site_admin_ids' meta value
            update_user_meta( $user_id, 'site_admin_ids', $site_admin_ids);
        }

        function set_site_user_doc_data() {
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

        function is_user_doc($doc_id=false, $user_id=false) {
            // Get the current user ID
            if (!$user_id) $user_id = get_current_user_id();    
            if (is_site_admin($user_id)) return true;
            // Get the user's doc IDs as an array
            $user_doc_ids = get_user_meta($user_id, 'user_doc_ids', true);
            // If $user_doc_ids is not an array, convert it to an array
            if (!is_array($user_doc_ids)) $user_doc_ids = array();
            // Check if the current user has the specified doc ID in their metadata
            return in_array($doc_id, $user_doc_ids);
        }

        function select_site_profile_options($selected_option=0) {
            $args = array(
                'post_type'      => 'site-profile',
                'posts_per_page' => -1,
            );
            $query = new WP_Query($args);
            $options = '<option value="">'.__( 'Select Option', 'textdomain' ).'</option>';
            while ($query->have_posts()) : $query->the_post();
                $site_id = get_the_ID();
                $site_title = get_the_title();
                $selected = ($selected_option == $site_id) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($site_id) . '" '.$selected.' />' . esc_html($site_title) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        // Site actions
        function display_site_action_list() {
            ob_start();
            ?>
            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Trigger', 'textdomain' );?></th>
                        <th><?php echo __( 'Connector', 'textdomain' );?></th>
                        <th><?php echo __( 'Action', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_site_action_list_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages

                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $action_id = get_the_ID();
                            $action_title = get_the_title();
                            $action_content = get_the_content();
                            $action_number = get_post_meta($action_id, 'action_number', true);
                            $action_connector = get_post_meta($action_id, 'action_connector', true);
                            $next_job = get_post_meta($action_id, 'next_job', true);
                            $doc_id = get_post_meta($action_id, 'doc_id', true);
                            $doc_number = get_post_meta($doc_id, 'doc_number', true);
                            $doc_title = get_post_meta($doc_id, 'doc_title', true);
                            if ($doc_number) $doc_title .= '('.$doc_number.')';
                            $action_title .= ': '.$doc_title;
                            // display the warning if the job without assigned users
                            $users_query = $this->retrieve_users_by_doc_id($doc_id);
                            $action_title = (!empty($users_query)) ? $action_title : '<span style="color:red;">'.$action_title.'</span>';
                            ?>
                            <tr id="edit-site-action-<?php echo $action_id;?>">
<?php /*?>                                
                                <td style="text-align:center;"><?php echo esc_html($action_number);?></td>
*/?>
                                <td><?php echo $action_title;?></td>
                                <td><?php echo get_the_title($action_connector);?></td>
                                <td><?php echo get_post_meta($next_job, 'doc_title', true);?></td>
                            </tr>
                            <?php 
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <?php if (is_site_admin()) {?>
                    <div id="new-site-action" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php }?>
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>
            </fieldset>
            <div id="site-action-dialog" title="Action dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_site_action_list_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
            if (empty($user_doc_ids)) $user_doc_ids=array();

            $args = array(
                'post_type'      => 'action',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
                //'meta_key'       => 'job_number', // Meta key for sorting
                //'orderby'        => 'meta_value', // Sort by meta value
                //'order'          => 'ASC', // Sorting order (ascending)
            );

            if (!is_site_admin()) {
            //    $args['post__in'] = $user_doc_ids; // Value is the array of job post IDs
            }

            if ($paged==0) $args['posts_per_page'] = -1;
/*
            if (isset($_GET['_search'])) {
                $search_query = sanitize_text_field($_GET['_search']);
                $args['meta_query'][] = array(
                    'key'     => 'job_number',
                    'value'   => $search_query,
                    'compare' => 'LIKE',
                );
            }
*/
            $query = new WP_Query($args);

            // Check if $query is empty and search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
/*                
                // Loop through meta query array to find and remove 'job_number'
                foreach ($args['meta_query'] as $key => $meta_query) {
                    if (isset($meta_query['key']) && $meta_query['key'] === 'job_number') {
                        unset($args['meta_query'][$key]);
                        break; // Stop looping once 'job_number' is found and removed
                    }
                }
*/
                // Set the search query parameter
                $args['s'] = $search_query;            
                // Reset pagination to page 1
                $args['paged'] = 1;
                $query = new WP_Query($args);
            }

            return $query;
        }

        function display_site_action_dialog($action_id=false) {
            ob_start();
            $items_class = new embedded_items();
            $action_number = get_post_meta($action_id, 'action_number', true);
            $action_title = get_the_title($action_id);
            $action_content = get_post_field('post_content', $action_id);
            $action_connector = get_post_meta($action_id, 'action_connector', true);
            $next_job = get_post_meta($action_id, 'next_job', true);
            $doc_id = get_post_meta($action_id, 'doc_id', true);
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            ?>
            <fieldset>
                <input type="hidden" id="action-id" value="<?php echo esc_attr($action_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="action-title"><?php echo __( 'Title', 'textdomain' );?></label><br>
                <input type="text" id="action-title" value="<?php echo esc_attr($action_title);?>" />
                <?php echo $doc_title;?><br>
                <label for="action-content"><?php echo __( 'Content', 'textdomain' );?></label>
                <input type="text" id="action-content" value="<?php echo esc_attr($action_content);?>" class="text ui-widget-content ui-corner-all" />
                <label for="action-connector"><?php echo __( 'Connector', 'textdomain' );?></label>
                <select id="action-connector" class="text ui-widget-content ui-corner-all" ><?php echo $items_class->select_doc_category_options($action_connector, true);?></select>
                <label for="next-job"><?php echo __( 'Action', 'textdomain' );?></label>
                <select id="next-job" class="text ui-widget-content ui-corner-all" ><?php echo $this->select_site_job_options($next_job, $action_connector);?></select>
                <label for="user-list"><?php echo __( 'User List', 'textdomain' );?></label>
                <?php echo $this->display_action_user_list($action_id);?>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_site_action_dialog_data() {
            $response = array();
            if( isset($_POST['_action_id']) ) {
                $action_id = sanitize_text_field($_POST['_action_id']);
                if( isset($_POST['_action_connector']) ) {
                    $action_connector = sanitize_text_field($_POST['_action_connector']);
                    update_post_meta($action_id, 'action_connector', $action_connector);
                }
                $response = array('html_contain' => $this->display_site_action_dialog($action_id));
            }
            wp_send_json($response);
        }

        function set_site_action_dialog_data() {
            $response = array();
            if( isset($_POST['_action_id']) ) {
                $action_id = isset($_POST['_action_id']) ? sanitize_text_field($_POST['_action_id']) : 0;
                $action_title = isset($_POST['_action_title']) ? sanitize_text_field($_POST['_action_title']) : '';
                $action_number = isset($_POST['_action_number']) ? sanitize_text_field($_POST['_action_number']) : '';
                $action_connector = isset($_POST['_action_connector']) ? sanitize_text_field($_POST['_action_connector']) : 0;
                $next_job = isset($_POST['_next_job']) ? sanitize_text_field($_POST['_next_job']) : 0;
                $data = array(
                    'ID'           => $action_id,
                    'post_title'   => $action_title,
                    'post_content' => $_POST['_action_content'],
                );
                wp_update_post( $data );
                update_post_meta($action_id, 'action_number', $action_number);
                update_post_meta($action_id, 'action_connector', $action_connector);
                update_post_meta($action_id, 'next_job', $next_job);

                // Check if action_number is null
                if ($action_number == null || $action_number === '') {
                    // If null or empty, delete the meta key
                    delete_post_meta($action_id, 'action_number');
                }

            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                // new action
                $new_post = array(
                    'post_type'     => 'action',
                    'post_title'    => __( 'New action', 'textdomain' ),
                    'post_content'  => __( 'Your post content goes here.', 'textdomain' ),
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $new_action_id = wp_insert_post($new_post);
                update_post_meta($new_action_id, 'site_id', $site_id);
                update_post_meta($new_action_id, 'action_number', '-');
                //update_post_meta($new_action_id, 'site_id', $site_id);
                //update_post_meta($new_action_id, 'next_job', -1);
                //update_post_meta($new_action_id, 'next_leadtime', 86400);
            }
            $response['html_contain'] = $this->display_site_action_list();
            wp_send_json($response);
        }

        function del_site_action_dialog_data() {
            $response = array();
            $action_id = isset($_POST['_action_id']) ? sanitize_text_field($_POST['_action_id']) : 0;
            wp_delete_post($action_id, true);
            $response['html_contain'] = $this->display_site_action_list();
            wp_send_json($response);
        }
        
        // action-user
        function display_action_user_list($action_id=false) {
            ob_start();
            ?>
            <div id="action-user-list">
            <fieldset>
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th><?php echo __( 'Name', 'textdomain' );?></th>
                        <th><?php echo __( 'Email', 'textdomain' );?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $users = $this->retrieve_users_by_action_id($action_id);
                foreach ($users as $user) {
                    ?>
                    <tr id="del-doc-user-<?php echo $user->ID;?>">
                        <td style="text-align:center;"><?php echo esc_html($user->display_name);?></td>
                        <td style="text-align:center;"><?php echo esc_html($user->user_email);?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <?php if (is_site_admin()) {?>
                <div id="new-action-user" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
            <?php }?>
            </fieldset>
            </div>
            <div id="new-action-users-dialog" title="<?php echo __( 'Add User', 'textdomain' );?>"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_users_by_action_id($action_id) {
            $args = array(
                'meta_query' => array(
                    array(
                        'key'     => 'user_action_ids',
                        'value'   => $action_id,
                        'compare' => 'LIKE'
                    )
                )
            );
            $user_query = new WP_User_Query($args);
            // Get the results
            $users = $user_query->get_results();
            return $users;
        }

        function display_new_action_user_list() {
            ob_start();
            ?>
            <fieldset>
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th><?php echo __( 'Name', 'textdomain' );?></th>
                        <th><?php echo __( 'Email', 'textdomain' );?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $users = $this->retrieve_users_by_site_id();
                foreach ($users as $user) {
                    ?>
                    <tr id="add-action-user-<?php echo $user->ID;?>">
                        <td style="text-align:center;"><?php echo esc_html($user->display_name);?></td>
                        <td style="text-align:center;"><?php echo esc_html($user->user_email);?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_new_action_user() {
            $response = array();
            $response['html_contain'] = $this->display_new_action_user_list();
            wp_send_json($response);
        }

        function set_action_user_data() {
            $response = array();
            $action_id = sanitize_text_field($_POST['_action_id']);
            $user_id = sanitize_text_field($_POST['_user_id']);

            // Check if user exists
            if (get_userdata($user_id) === false) {
                $response['status'] = 'error';
                $response['message'] = 'Invalid user ID.';
                wp_send_json($response);
            }
        
            // Retrieve current user_action_ids
            $user_action_ids = get_user_meta($user_id, 'user_action_ids', true);
        
            if (empty($user_action_ids)) {
                $user_action_ids = array();
            } elseif (is_string($user_action_ids)) {
                // Handle if user_action_ids is a serialized array or a comma-separated list
                $user_action_ids_array = maybe_unserialize($user_action_ids);
                if (is_array($user_action_ids_array)) {
                    $user_action_ids = $user_action_ids_array;
                } else {
                    $user_action_ids = explode(',', $user_action_ids);
                }
            }
        
            // Add the new action_id if it doesn't already exist
            if (!in_array($action_id, $user_action_ids)) {
                $user_action_ids[] = $action_id;
                update_user_meta($user_id, 'user_action_ids', $user_action_ids);
        
                $response['status'] = 'success';
                $response['message'] = 'ID added successfully.';
            } else {
                $response['status'] = 'info';
                $response['message'] = 'ID already exists for this user.';
            }

            $action_id = sanitize_text_field($_POST['_action_id']);
            $response['html_contain'] = $this->display_action_user_list($action_id);
            wp_send_json($response);
        }

        function del_action_user_data() {
            $response = array();
            $action_id = sanitize_text_field($_POST['_action_id']);
            $user_id = sanitize_text_field($_POST['_user_id']);

            // Check if user exists
            if (get_userdata($user_id) === false) {
                $response['status'] = 'error';
                $response['message'] = 'Invalid user ID.';
                wp_send_json($response);
            }
        
            // Retrieve current user_action_ids
            $user_action_ids = get_user_meta($user_id, 'user_action_ids', true);
        
            if (empty($user_action_ids)) {
                $user_action_ids = array();
            } elseif (is_string($user_action_ids)) {
                // Handle if user_action_ids is a serialized array or a comma-separated list
                $user_action_ids_array = maybe_unserialize($user_action_ids);
                if (is_array($user_action_ids_array)) {
                    $user_action_ids = $user_action_ids_array;
                } else {
                    $user_action_ids = explode(',', $user_action_ids);
                }
            }
        
            // Remove the action_id if it exists
            if (in_array($action_id, $user_action_ids)) {
                $user_action_ids = array_diff($user_action_ids, array($action_id));
                update_user_meta($user_id, 'user_action_ids', $user_action_ids);
        
                $response['status'] = 'success';
                $response['message'] = 'ID deleted successfully.';
            } else {
                $response['status'] = 'info';
                $response['message'] = 'ID does not exist for this user.';
            }

            $action_id = sanitize_text_field($_POST['_action_id']);
            $response['html_contain'] = $this->display_action_user_list($action_id);
            wp_send_json($response);
        }

/*
        function retrieve_users_by_site_id() {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $args = array(
                'meta_query' => array(
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                        //'compare' => '='
                    )
                )
            );
            $user_query = new WP_User_Query($args);
            // Get the results
            $users = $user_query->get_results();
            return $users;
        }
*/



        // Site job
        function display_site_job_list() {
            ob_start();
            ?>
            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'No.', 'textdomain' );?></th>
                        <th><?php echo __( 'Title', 'textdomain' );?></th>
                        <th><?php echo __( 'Description', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_site_job_list_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages

                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $job_id = get_the_ID();
                            $job_title = get_the_title();
                            $job_number = get_post_meta($job_id, 'job_number', true);
                            $department_id = get_post_meta($job_id, 'department_id', true);
                            $doc_number = get_post_meta($job_id, 'doc_number', true);
                            $doc_title = get_post_meta($job_id, 'doc_title', true);
                            if ($doc_number) $doc_title .= '('.$doc_number.')';
                            else $doc_title = get_the_content();
                            // display the warning if the job without assigned actions
                            $action_query = $this->retrieve_doc_action_data($job_id);
                            $job_title = ($action_query->have_posts()) ? $job_title : '<span style="color:red;">'.$job_title.'</span>';
                            // display the warning if the job without assigned users
                            $users_query = $this->retrieve_users_by_doc_id($job_id);
                            $doc_title = (!empty($users_query)) ? $doc_title : '<span style="color:red;">'.$doc_title.'</span>';
                            ?>
                            <tr id="edit-site-job-<?php echo $job_id;?>">
                                <td style="text-align:center;"><?php echo esc_html($job_number);?></td>
                                <td style="text-align:center;"><?php echo $job_title;?></td>
                                <td><?php echo $doc_title;?></td>
                            </tr>
                            <?php 
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <?php if (is_site_admin()) {?>
                    <div id="new-site-job" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php }?>
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>
            </fieldset>
            <div id="site-job-dialog" title="Job dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_site_job_list_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $user_doc_ids = get_user_meta($current_user_id, 'user_doc_ids', true);
            if (empty($user_doc_ids)) $user_doc_ids=array();

            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                    array(
                        'relation' => 'OR',
                        array(
                            'key'   => 'is_doc_report',
                            'value' => 1,
                            //'compare' => '=',
                            //'type'    => 'NUMERIC'
                        ),
                        array(
                            'key'   => 'is_doc_report',
                            'compare' => 'NOT EXISTS',
                        ),    
                    ),
                ),
                'meta_key'       => 'job_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );

            if (!is_site_admin()) {
                $args['post__in'] = $user_doc_ids; // Value is the array of job post IDs
            }

            if ($paged==0) $args['posts_per_page'] = -1;

            if (isset($_GET['_search'])) {
                $search_query = sanitize_text_field($_GET['_search']);
                $args['meta_query'][] = array(
                    'key'     => 'job_number',
                    'value'   => $search_query,
                    'compare' => 'LIKE',
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

        function display_site_job_dialog($doc_id=false) {
            ob_start();
            $items_class = new embedded_items();
            $job_number = get_post_meta($doc_id, 'job_number', true);
            $job_title = get_the_title($doc_id);
            $job_content = get_post_field('post_content', $doc_id);
            //$department = get_post_meta($doc_id, 'department', true);
            $department_id = get_post_meta($doc_id, 'department_id', true);
            $is_summary_job = get_post_meta($doc_id, 'is_summary_job', true);
            $is_checked = ($is_summary_job==1) ? 'checked' : '';
            ?>
                <input type="hidden" id="doc-id" value="<?php echo esc_attr($doc_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="job-number"><?php echo __( 'No.', 'textdomain' );?></label>
                <input type="text" id="job-number" value="<?php echo esc_attr($job_number);?>" class="text ui-widget-content ui-corner-all" />
                <label for="job-title"><?php echo __( 'Title', 'textdomain' );?></label>
                <input type="text" id="job-title" value="<?php echo esc_attr($job_title);?>" class="text ui-widget-content ui-corner-all" />
                <label style="display:none;" for="job-content"><?php echo __( 'Content', 'textdomain' );?></label>
                <textarea style="display:none;" id="job-content" class="visual-editor"><?php echo $job_content;?></textarea>
                <label for="action-list"><?php echo __( 'Action List', 'textdomain' );?></label>
                <?php echo $this->display_doc_action_list($doc_id);?>
                <label for="department"><?php echo __( 'Department', 'textdomain' );?></label>
                <select id="department-id" class="text ui-widget-content ui-corner-all"><?php echo $items_class->select_department_card_options($department_id);?></select>
                <label for="user-list"><?php echo __( 'User List', 'textdomain' );?></label>
                <?php echo $this->display_doc_user_list($doc_id);?>
                <input type="checkbox" id="is-summary-job" <?php echo $is_checked?> />
                <label for="is-summary-job"><?php echo __( 'Is Summary Job', 'textdomain' );?></label>
            <?php
            return ob_get_clean();
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
                $job_id = isset($_POST['_doc_id']) ? sanitize_text_field($_POST['_doc_id']) : 0;
                $job_title = isset($_POST['_job_title']) ? sanitize_text_field($_POST['_job_title']) : '';
                $job_number = isset($_POST['_job_number']) ? sanitize_text_field($_POST['_job_number']) : '';
                $department_id = isset($_POST['_department_id']) ? sanitize_text_field($_POST['_department_id']) : 0;
                $is_summary_job = isset($_POST['_is_summary_job']) ? sanitize_text_field($_POST['_is_summary_job']) : 0;
                $data = array(
                    'ID'           => $job_id,
                    'post_title'   => $job_title,
                    'post_content' => $_POST['_job_content'],
                );
                wp_update_post( $data );
                update_post_meta($job_id, 'job_number', $job_number);
                update_post_meta($job_id, 'department_id', $department_id);
                update_post_meta($job_id, 'is_summary_job', $is_summary_job);

                // Check if job_number is null
                if ($job_number == null || $job_number === '') {
                    // If null or empty, delete the meta key
                    delete_post_meta($job_id, 'job_number');
                }

            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                // new job
                $new_post = array(
                    'post_type'     => 'document',
                    'post_title'    => __( 'New Job', 'textdomain' ),
                    'post_content'  => __( 'Your post content goes here.', 'textdomain' ),
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $new_doc_id = wp_insert_post($new_post);
                update_post_meta($new_doc_id, 'site_id', $site_id);
                update_post_meta($new_doc_id, 'job_number', '-');

                // new action
                $new_post = array(
                    'post_type'     => 'action',
                    'post_title'    => __( 'OK', 'textdomain' ),
                    'post_content'  => __( 'Your post content goes here.', 'textdomain' ),
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $new_action_id = wp_insert_post($new_post);
                update_post_meta($new_action_id, 'doc_id', $new_doc_id);
                update_post_meta($new_action_id, 'next_job', -1);
                update_post_meta($new_action_id, 'next_leadtime', 86400);
            }
            $response['html_contain'] = $this->display_site_job_list();
            wp_send_json($response);
        }

        function del_site_job_dialog_data() {
            $response = array();
            $doc_id = isset($_POST['_doc_id']) ? sanitize_text_field($_POST['_doc_id']) : 0;
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            if ($doc_title) echo 'You cannot delete this job';
            else wp_delete_post($doc_id, true);
            $response['html_contain'] = $this->display_site_job_list();
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
                        <th><?php echo __( 'Action', 'textdomain' );?></th>
                        <th><?php echo __( 'Description', 'textdomain' );?></th>
                        <th><?php echo __( 'Next', 'textdomain' );?></th>
                        <th><?php echo __( 'LeadTime', 'textdomain' );?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $query = $this->retrieve_doc_action_data($doc_id);
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        $action_id = get_the_ID();
                        $action_title = get_the_title();
                        $action_content = get_post_field('post_content', $action_id);
                        $next_job = get_post_meta($action_id, 'next_job', true);
                        $next_job_title = get_the_title($next_job);
                        $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
                        if ($next_job==-1) {
                            $next_job_title = __( 'Released', 'textdomain' );
                        }
                        if ($next_job==-2) {
                            $next_job_title = __( 'Removed', 'textdomain' );
                        }
                        $next_leadtime = get_post_meta($action_id, 'next_leadtime', true);
                        ?>
                        <tr id="edit-doc-action-<?php echo $action_id;?>">
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
            <?php if (is_site_admin()) {?>
                <div id="new-doc-action" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
            <?php }?>
            </fieldset>
            </div>
            <div id="doc-action-dialog" title="Action dialog"></div>
            <?php
            return ob_get_clean();
        }

        function find_more_query_posts($query=false) {
            if (!$query) return false;
        
            // Retrieve the current total posts count
            $current_total_posts = $query->found_posts;
        
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
        
            // If there are no next jobs, return the original query
            if (empty($next_jobs)) {
                return $query;
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
            $query = new WP_Query(array(
                'post__in' => wp_list_pluck($combined_posts, 'ID'),
                'post_type' => 'action',
                'posts_per_page' => -1
            ));
        
            // Retrieve the next total posts count
            $next_total_posts = $query->found_posts;
        
            // If new posts are found, perform the recursive call
            if ($next_total_posts > $current_total_posts) {
                return $this->find_more_query_posts($query);
            }
        
            // Return the final query
            return $query;
        }

        function retrieve_doc_action_data($doc_id = false, $is_nest = false) {
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

            if ($is_nest) $query = $this->find_more_query_posts($query);

            return $query;
        }

        function display_doc_action_dialog($action_id=false){
            ob_start();
            $action_title = get_the_title($action_id);
            $action_content = get_post_field('post_content', $action_id);
            $next_job = get_post_meta($action_id, 'next_job', true);
            $next_leadtime = get_post_meta($action_id, 'next_leadtime', true);
            ?>
            <fieldset>
                <input type="hidden" id="action-id" value="<?php echo esc_attr($action_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="action-title"><?php echo __( 'Title', 'textdomain' );?></label>
                <input type="text" id="action-title" value="<?php echo esc_attr($action_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="action-content"><?php echo __( 'Content', 'textdomain' );?></label>
                <input type="text" id="action-content" value="<?php echo esc_attr($action_content);?>" class="text ui-widget-content ui-corner-all" />
                <label for="next-job"><?php echo __( 'Next Job', 'textdomain' );?></label>
                <select id="next-job" class="text ui-widget-content ui-corner-all" ><?php echo $this->select_site_job_options($next_job);?></select>
                <label for="next-leadtime"><?php echo __( 'Leadtime', 'textdomain' );?></label>
                <input type="text" id="next-leadtime" value="<?php echo esc_attr($next_leadtime);?>" class="text ui-widget-content ui-corner-all" />
            </fieldset>
            <?php
            return ob_get_clean();
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
                $action_id = isset($_POST['_action_id']) ? sanitize_text_field($_POST['_action_id']) : 0;
                $action_title = isset($_POST['_action_title']) ? sanitize_text_field($_POST['_action_title']) : '';
                $next_job = isset($_POST['_next_job']) ? sanitize_text_field($_POST['_next_job']) : 0;
                $next_leadtime = isset($_POST['_next_leadtime']) ? sanitize_text_field($_POST['_next_leadtime']) : 86400;
                $data = array(
                    'ID'          => $action_id,
                    'post_title'  => $action_title,
                    'post_content' => $_POST['_action_content'],
                    'meta_input'  => array(
                        'next_job'      => $next_job,
                        'next_leadtime' => $next_leadtime,
                    )
                );
                wp_update_post( $data );
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_type'     => 'action',
                    'post_title'    => __( 'New Action', 'textdomain' ),
                    'post_content'  => __( 'Your post content goes here.', 'textdomain' ),
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'doc_id', sanitize_text_field($_POST['_doc_id']) );
                update_post_meta($post_id, 'next_job', -1);
                update_post_meta($post_id, 'next_leadtime', 86400);
            }
            $doc_id = sanitize_text_field($_POST['_doc_id']);
            $response['html_contain'] = $this->display_doc_action_list($doc_id);
            wp_send_json($response);
        }

        function del_doc_action_dialog_data() {
            $response = array();
            wp_delete_post($_POST['_action_id'], true);
            $doc_id = sanitize_text_field($_POST['_doc_id']);
            $response['html_contain'] = $this->display_doc_action_list($doc_id);
            wp_send_json($response);
        }

        function select_site_job_options($selected_option=false, $action_connector=false) {
            $options = '<option value="">'.__( 'Select Option', 'textdomain' ).'</option>';
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => -1,
                'meta_query'     => array(),
                'orderby'        => 'meta_value',
                'meta_key'       => 'job_number',
                'order'          => 'ASC',
            );

            if ($action_connector) {
                $args['meta_query'][] = array(
                    'key'   => 'doc_category',
                    'value' => $action_connector,
                );
            } else {
                $args['meta_query'][] = array(
                    'relation' => 'OR',
                    array(
                        'key'   => 'is_doc_report',
                        'value' => 1,
                    ),
                    array(
                        'key'   => 'is_doc_report',
                        'compare' => 'NOT EXISTS',
                    ),    
                );
            }
            $query = new WP_Query($args);

            while ($query->have_posts()) : $query->the_post();
                $job_id = get_the_ID();
                $job_title = get_the_title();
                $job_number = get_post_meta($job_id, 'job_number', true);
                $doc_number = get_post_meta($job_id, 'doc_number', true);
                $doc_title = get_post_meta($job_id, 'doc_title', true);
                $job_title = $job_title.'('.$job_number.')';
                if ($action_connector) $job_title = $doc_title.'('.$doc_number.')';

                $selected = ($selected_option == $job_id) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($job_id) . '" '.$selected.' />' . esc_html($job_title) . '</option>';
            endwhile;
            wp_reset_postdata();

            if (!$action_connector) {
                if ($selected_option==-1){
                    $options .= '<option value="-1" selected>'.__( 'Released', 'textdomain' ).'</option>';
                } else {
                    $options .= '<option value="-1">'.__( 'Released', 'textdomain' ).'</option>';
                }
                if ($selected_option==-2){
                    $options .= '<option value="-2" selected>'.__( 'Removed', 'textdomain' ).'</option>';
                } else {
                    $options .= '<option value="-2">'.__( 'Removed', 'textdomain' ).'</option>';
                }
            }

            return $options;
        }

        // doc-user
        function display_doc_user_list($doc_id=false) {
            ob_start();
            ?>
            <div id="doc-user-list">
            <fieldset>
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th><?php echo __( 'Name', 'textdomain' );?></th>
                        <th><?php echo __( 'Email', 'textdomain' );?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $users = $this->retrieve_users_by_doc_id($doc_id);
                foreach ($users as $user) {
                    ?>
                    <tr id="del-doc-user-<?php echo $user->ID;?>">
                        <td style="text-align:center;"><?php echo esc_html($user->display_name);?></td>
                        <td style="text-align:center;"><?php echo esc_html($user->user_email);?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <?php if (is_site_admin()) {?>
                <div id="new-doc-user" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
            <?php }?>
            </fieldset>
            </div>
            <div id="new-user-list-dialog" title="<?php echo __( 'Add User', 'textdomain' );?>"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_users_by_doc_id($doc_id) {
            $args = array(
                'meta_query' => array(
                    array(
                        'key'     => 'user_doc_ids',
                        'value'   => $doc_id,
                        'compare' => 'LIKE'
                    )
                )
            );
            $user_query = new WP_User_Query($args);
            // Get the results
            $users = $user_query->get_results();
            return $users;
        }

        function display_new_doc_user_list() {
            ob_start();
            ?>
            <fieldset>
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th><?php echo __( 'Name', 'textdomain' );?></th>
                        <th><?php echo __( 'Email', 'textdomain' );?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $users = $this->retrieve_users_by_site_id();
                foreach ($users as $user) {
                    ?>
                    <tr id="add-doc-user-<?php echo $user->ID;?>">
                        <td style="text-align:center;"><?php echo esc_html($user->display_name);?></td>
                        <td style="text-align:center;"><?php echo esc_html($user->user_email);?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function retrieve_users_by_site_id() {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $args = array(
                'meta_query' => array(
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                        //'compare' => '='
                    )
                )
            );
            $user_query = new WP_User_Query($args);
            // Get the results
            $users = $user_query->get_results();
            return $users;
        }

        function get_new_user_list() {
            $response = array();
            $response['html_contain'] = $this->display_new_doc_user_list();
            wp_send_json($response);
        }

        function add_doc_user_data() {
            $response = array();
            $doc_id = sanitize_text_field($_POST['_doc_id']);
            $user_id = sanitize_text_field($_POST['_user_id']);

            // Check if user exists
            if (get_userdata($user_id) === false) {
                $response['status'] = 'error';
                $response['message'] = 'Invalid user ID.';
                wp_send_json($response);
            }
        
            // Retrieve current user_doc_ids
            $user_doc_ids = get_user_meta($user_id, 'user_doc_ids', true);
        
            if (empty($user_doc_ids)) {
                $user_doc_ids = array();
            } elseif (is_string($user_doc_ids)) {
                // Handle if user_doc_ids is a serialized array or a comma-separated list
                $user_doc_ids_array = maybe_unserialize($user_doc_ids);
                if (is_array($user_doc_ids_array)) {
                    $user_doc_ids = $user_doc_ids_array;
                } else {
                    $user_doc_ids = explode(',', $user_doc_ids);
                }
            }
        
            // Add the new doc_id if it doesn't already exist
            if (!in_array($doc_id, $user_doc_ids)) {
                $user_doc_ids[] = $doc_id;
                update_user_meta($user_id, 'user_doc_ids', $user_doc_ids);
        
                $response['status'] = 'success';
                $response['message'] = 'Document ID added successfully.';
            } else {
                $response['status'] = 'info';
                $response['message'] = 'Document ID already exists for this user.';
            }

            $doc_id = sanitize_text_field($_POST['_doc_id']);
            $response['html_contain'] = $this->display_doc_user_list($doc_id);
            wp_send_json($response);
        }

        function del_doc_user_data() {
            $response = array();
            $doc_id = sanitize_text_field($_POST['_doc_id']);
            $user_id = sanitize_text_field($_POST['_user_id']);

            // Check if user exists
            if (get_userdata($user_id) === false) {
                $response['status'] = 'error';
                $response['message'] = 'Invalid user ID.';
                wp_send_json($response);
            }
        
            // Retrieve current user_doc_ids
            $user_doc_ids = get_user_meta($user_id, 'user_doc_ids', true);
        
            if (empty($user_doc_ids)) {
                $user_doc_ids = array();
            } elseif (is_string($user_doc_ids)) {
                // Handle if user_doc_ids is a serialized array or a comma-separated list
                $user_doc_ids_array = maybe_unserialize($user_doc_ids);
                if (is_array($user_doc_ids_array)) {
                    $user_doc_ids = $user_doc_ids_array;
                } else {
                    $user_doc_ids = explode(',', $user_doc_ids);
                }
            }
        
            // Remove the doc_id if it exists
            if (in_array($doc_id, $user_doc_ids)) {
                $user_doc_ids = array_diff($user_doc_ids, array($doc_id));
                update_user_meta($user_id, 'user_doc_ids', $user_doc_ids);
        
                $response['status'] = 'success';
                $response['message'] = 'Document ID deleted successfully.';
            } else {
                $response['status'] = 'info';
                $response['message'] = 'Document ID does not exist for this user.';
            }

            $doc_id = sanitize_text_field($_POST['_doc_id']);
            $response['html_contain'] = $this->display_doc_user_list($doc_id);
            wp_send_json($response);
        }

        // site-profile list
        function get_site_list_data() {
            $search_query = sanitize_text_field($_POST['_site_title']);
            $args = array(
                'post_type'      => 'site-profile',
                'posts_per_page' => -1,
                's'              => $search_query,
            );
            $query = new WP_Query($args);
        
            $_array = array();
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $site_id = get_the_ID();
                    $site_title = get_the_title();
                    $_list = array();
                    $_list["site_id"] = $site_id;
                    $_list["site_title"] = $site_title;
                    array_push($_array, $_list);
                endwhile;
                wp_reset_postdata();
            }
            wp_send_json($_array);
        }
        
        function get_site_dialog_data() {
            $response = array();
            if( isset($_POST['_site_id']) ) {
                $site_id = sanitize_text_field($_POST['_site_id']);
                $response["site_title"] = get_the_title($site_id);
            }
            wp_send_json($response);
        }

        function get_site_profile_content() {
            // Check if the site_id is passed
            if(isset($_POST['site_id'])) {
                $site_id = intval($_POST['site_id']);
        
                // Retrieve the post content
                $post = get_post($site_id);
        
                if($post && $post->post_type == 'site-profile') {
                    wp_send_json_success(array(
                        'content' => apply_filters('the_content', $post->post_content),
                        'unified_number' => get_post_meta($site_id, 'unified_number', true),
                    ));
                } else {
                    wp_send_json_error(array('message' => 'Invalid site ID or post type.'));
                }
            } else {
                wp_send_json_error(array('message' => 'No site ID provided.'));
            }
        }
        
        function approve_NDA_assignment($user_id=false) {
            if (empty($user_id)) return;
            if (!is_site_admin()) return;
            $site_id = get_user_meta($user_id, 'site_id', true);
            $site_title = get_the_title($site_id);
            $unified_number = get_post_meta($site_id, 'unified_number', true);
            $user = get_userdata($user_id);
            $display_name = $user->display_name;
            $identity_number = get_user_meta($user_id, 'identity_number', true);
            $nda_content = get_user_meta($user_id, 'nda_content', true);
            $nda_signature = get_user_meta($user_id, 'nda_signature', true);
            $submit_date = get_user_meta($user_id, 'submit_date', true);
            ?>
            <div class="ui-widget" id="result-container">
                <h2 style="display:inline; text-align:center;"><?php echo __( 'Non-Disclosure Agreement', 'textdomain' );?></h2>
                <div>
                    <label for="site-title"><b><?php echo __( 'Party A', 'textdomain' );?></b></label>
                    <input type="text" id="site-title" value="<?php echo $site_title;?>" class="text ui-widget-content ui-corner-all" disabled />
                    <label for="unified-number"><?php echo __( 'Unified Number', 'textdomain' );?></label>
                    <input type="text" id="unified-number" value="<?php echo $unified_number;?>" class="text ui-widget-content ui-corner-all" disabled />
                    <input type="hidden" id="site-id" value="<?php echo $site_id;?>"/>
                </div>
                <div>
                    <label for="display-name"><b><?php echo __( 'Party B', 'textdomain' );?></b></label>
                    <input type="text" id="display-name" value="<?php echo $display_name;?>" class="text ui-widget-content ui-corner-all" disabled />
                    <label for="identity-number"><?php echo __( 'ID Number', 'textdomain' );?></label>
                    <input type="text" id="identity-number" value="<?php echo $identity_number;?>" class="text ui-widget-content ui-corner-all" disabled />
                    <input type="hidden" id="user-id" value="<?php echo $user_id;?>"/>
                </div>
                <div id="nda-content"><?php echo $nda_content;?></div>
                <div style="display:flex;">
                    <?php echo __( 'Sign-off Date', 'textdomain' );?>
                    <input type="text" id="submit-date" value="<?php echo $submit_date;?>" disabled />
                </div>
                <div>
                    <label for="signature-pad"><?php echo __( 'Review Signature', 'textdomain' );?></label>
                    <div id="signature-pad-div">
                        <div>
                            <canvas id="signature-pad" width="500" height="200" style="border:1px solid #000;"></canvas>
                        </div>
                        <button id="clear-signature" style="margin:3px;"><?php echo __( 'Clear Signature', 'textdomain' );?></button>
                    </div>
                </div>
                <div style="display:flex;">
                    <?php echo __( 'Approval Date', 'textdomain' );?>
                    <input type="date" id="nda-date" value="<?php echo wp_date('Y-m-d', time())?>"/>
                </div>
                <hr>
                <button type="submit" id="nda-approve"><?php echo __( 'Approve', 'textdomain' );?></button>
                <button type="submit" id="nda-reject"><?php echo __( 'Reject', 'textdomain' );?></button>
            </div>
            <?php
        }
        
        function get_NDA_assignment($user_id=false) {
            $user = get_userdata($user_id);
            ?>
            <div class="ui-widget" id="result-container">
                <h2 style="display:inline; text-align:center;"><?php echo __( 'Non-Disclosure Agreement', 'textdomain' );?></h2>
                <div>
                    <label for="select-nda-site"><b><?php echo __( 'Party A', 'textdomain' );?></b></label>
                    <select id="select-nda-site" class="text ui-widget-content ui-corner-all" >
                        <option value=""><?php echo __( 'Select Option', 'textdomain' );?></option>
                        <?php
                            $site_args = array(
                                'post_type'      => 'site-profile',
                                'posts_per_page' => -1,
                            );
                            $sites = get_posts($site_args);
        
                            // Check if "site-profile" posts are empty
                            if (empty($sites)) {
                                // Insert a new "site-profile" post with the title "iso-helper.com"
                                $new_site_id = wp_insert_post(array(
                                    'post_title'  => 'iso-helper.com',
                                    'post_type'   => 'site-profile',
                                    'post_status' => 'publish',
                                ));
                                // Retrieve the updated list of "site-profile" posts
                                $sites = get_posts($site_args);
                            }
        
                            // Display the options in a dropdown
                            foreach ($sites as $site) {
                                echo '<option value="' . esc_attr($site->ID) . '">' . esc_html($site->post_title) . '</option>';
                            }
                        ?>
                    </select>
                    <label for="unified-number"><?php echo __( 'Unified Number', 'textdomain' );?></label>
                    <input type="text" id="unified-number" class="text ui-widget-content ui-corner-all" disabled />
                </div>
                <div>
                    <label for="display-name"><b><?php echo __( 'Party B', 'textdomain' );?></b></label>
                    <input type="text" id="display-name" value="<?php echo $user->display_name;?>" class="text ui-widget-content ui-corner-all" />
                    <label for="identity-number"><?php echo __( 'ID Number', 'textdomain' );?></label>
                    <input type="text" id="identity-number" class="text ui-widget-content ui-corner-all" />
                    <input type="hidden" id="user-id" value="<?php echo $user_id;?>"/>
                </div>
                <div id="site-content">
                    <!-- The site content will be displayed here -->
                </div>
                <div>
                    <label for="signature-pad"><?php echo __( 'Canvas', 'textdomain' );?></label>
                    <div id="signature-pad-div">
                        <div>
                            <canvas id="signature-pad" width="500" height="200" style="border:1px solid #000;"></canvas>
                        </div>
                        <button id="clear-signature" style="margin:3px;"><?php echo __( 'Clear Signature', 'textdomain' );?></button>
                    </div>
                </div>
                <div style="display:flex;">
                    <?php echo __( 'Approval Date', 'textdomain' );?>
                    <input type="date" id="submit-date" value="<?php echo wp_date('Y-m-d', time())?>"/>
                </div>
                <hr>
                <button type="submit" id="nda-submit"><?php echo __( 'Submit', 'textdomain' );?></button>
                <button type="submit" id="nda-exit"><?php echo __( 'Exit', 'textdomain' );?></button>
            </div>
            <?php
        }
        
        function set_NDA_assignment() {
            $response = array();
            $line_bot_api = new line_bot_api();
            if(isset($_POST['_user_id']) && isset($_POST['_site_id']) && isset($_POST['_reject_date'])) {
                $user_id = intval($_POST['_user_id']);
                $user = get_userdata($user_id);
                $site_id = intval($_POST['_site_id']);
                $activated_site_users = get_post_meta($site_id, 'activated_site_users', true);
                if (!is_array($activated_site_users)) $activated_site_users = array();
                $activated_site_users[] = $user_id;
                update_user_meta( $user_id, 'reject_id', get_current_user_id());
                update_user_meta( $user_id, 'reject_date', $_POST['_reject_date']);

                $line_user_id = get_user_meta($user_id, 'line_user_id', true);
                $line_bot_api->send_flex_message([
                    'to' => $line_user_id,
                    'header_contents' => [['type' => 'text', 'text' => __( 'Notification', 'textdomain' ), 'weight' => 'bold']],
                    'body_contents' => [
                        [
                            'type' => 'text',
                            'text' => sprintf(
                                __( 'The NDA of %s has been rejected. Check with the administrator.', 'textdomain' ),
                                esc_html( $user->display_name )
                            ),
                            'wrap' => true,
                        ],
                    ],                    
                    'footer_contents' => [['type' => 'button', 'action' => ['type' => 'uri', 'label' => __( 'View Details', 'textdomain' ), 'uri' => home_url("/display-profiles/?_select_profile=my-profile")], 'style' => 'primary']],
                ]);

                $params = array(
                    'log_message' => sprintf(
                        __('The NDA of %s has been rejected by %s', 'textdomain'),
                        $user->display_name,
                        wp_get_current_user()->display_name
                    ),     
                    'user_id' => $user_id,
                );
                $todo_class = new to_do_list();
                $todo_class->create_action_log_and_go_next($params);    

                $response = array('nda'=>'rejected', 'user_id'=>$user_id, 'activated_site_users'=>$activated_site_users);
            }

            if(isset($_POST['_user_id']) && isset($_POST['_site_id']) && isset($_POST['_approve_date'])) {
                $user_id = intval($_POST['_user_id']);
                $user = get_userdata($user_id);
                $site_id = intval($_POST['_site_id']);
                $activated_site_users = get_post_meta($site_id, 'activated_site_users', true);
                if (!is_array($activated_site_users)) $activated_site_users = array();
                $activated_site_users[] = $user_id;
                update_user_meta( $user_id, 'approve_id', get_current_user_id());
                update_user_meta( $user_id, 'approve_date', $_POST['_approve_date']);
                update_post_meta( $site_id, 'activated_site_users', $activated_site_users);

                $line_user_id = get_user_meta($user_id, 'line_user_id', true);
                $line_bot_api->send_flex_message([
                    'to' => $line_user_id,
                    'header_contents' => [['type' => 'text', 'text' => __( 'Notification', 'textdomain' ), 'weight' => 'bold']],
                    'body_contents' => [
                        [
                            'type' => 'text',
                            'text' => sprintf(
                                __('The NDA of %s has been approved. Go to your profile.', 'textdomain'),
                                $user->display_name
                            ),
                            'wrap' => true
                        ]
                    ],                    
                    'footer_contents' => [['type' => 'button', 'action' => ['type' => 'uri', 'label' => __( 'View Details', 'textdomain' ), 'uri' => home_url("/display-profiles/?_select_profile=my-profile")], 'style' => 'primary']],
                ]);

                $params = array(
                    'log_message' => sprintf(
                        __('The NDA of %s has been approved by %s', 'textdomain'),
                        $user->display_name,
                        wp_get_current_user()->display_name
                    ),                    
                    'user_id' => $user_id,
                );
                $todo_class = new to_do_list();
                $todo_class->create_action_log_and_go_next($params);    

                $response = array('nda'=>'approved', 'user_id'=>$user_id, 'activated_site_users'=>$activated_site_users);
            }

            if(isset($_POST['_user_id']) && isset($_POST['_site_id']) && isset($_POST['_identity_number'])) {
                $user_id = intval($_POST['_user_id']);
                $user = get_userdata($user_id);
                $site_id = intval($_POST['_site_id']);
                update_user_meta( $user_id, 'site_id', $site_id);
                update_user_meta( $user_id, 'display_name', $_POST['_display_name']);
                update_user_meta( $user_id, 'identity_number', $_POST['_identity_number']);
                update_user_meta( $user_id, 'nda_content', $_POST['_nda_content']);
                update_user_meta( $user_id, 'nda_signature', $_POST['_nda_signature']);
                update_user_meta( $user_id, 'submit_date', $_POST['_submit_date']);
        
                $site_admin_ids = get_site_admin_ids_for_site($site_id);
                foreach ($site_admin_ids as $site_admin_id) {
                    $line_user_id = get_user_meta($site_admin_id, 'line_user_id', true);
                    $line_bot_api->send_flex_message([
                        'to' => $line_user_id,
                        'header_contents' => [['type' => 'text', 'text' => __( 'Notification', 'textdomain' ), 'weight' => 'bold']],
                        'body_contents' => [
                            [
                                'type' => 'text',
                                'text' => sprintf(
                                    __('%s has signed the NDA of %s.', 'textdomain'),
                                    $user->display_name,
                                    get_the_title($site_id)
                                ),
                                'wrap' => true
                            ]
                        ],                        
                        'footer_contents' => [['type' => 'button', 'action' => ['type' => 'uri', 'label' =>  __( 'View Details', 'textdomain' ), 'uri' => home_url("/display-profiles/?_nda_user_id=$user_id")], 'style' => 'primary']],
                    ]);
                }
                $response = array('nda'=>'submitted');
            }
            wp_send_json($response);
        }
    }
    $profiles_class = new display_profiles();
}