<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('display_documents')) {
    class display_documents {
        // Class constructor
        public function __construct() {
            add_shortcode( 'display-documents', array( $this, 'display_shortcode'  ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_display_document_scripts' ) );
            add_action( 'init', array( $this, 'register_document_post_type' ) );
            add_action( 'add_meta_boxes', array( $this, 'add_document_settings_metabox' ) );
            add_action( 'init', array( $this, 'register_doc_report_post_type' ) );
            add_action( 'init', array( $this, 'register_doc_field_post_type' ) );
            add_action( 'init', array( $this, 'register_doc_category_post_type' ) );

            add_action( 'wp_ajax_get_document_dialog_data', array( $this, 'get_document_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_document_dialog_data', array( $this, 'get_document_dialog_data' ) );
            add_action( 'wp_ajax_set_document_dialog_data', array( $this, 'set_document_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_document_dialog_data', array( $this, 'set_document_dialog_data' ) );
            add_action( 'wp_ajax_del_document_dialog_data', array( $this, 'del_document_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_document_dialog_data', array( $this, 'del_document_dialog_data' ) );

            add_action( 'wp_ajax_get_doc_frame_contain', array( $this, 'get_doc_frame_contain' ) );
            add_action( 'wp_ajax_nopriv_get_doc_frame_contain', array( $this, 'get_doc_frame_contain' ) );

            add_action( 'wp_ajax_get_doc_report_list_data', array( $this, 'get_doc_report_list_data' ) );
            add_action( 'wp_ajax_nopriv_get_doc_report_list_data', array( $this, 'get_doc_report_list_data' ) );

            add_action( 'wp_ajax_get_doc_report_dialog_data', array( $this, 'get_doc_report_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_doc_report_dialog_data', array( $this, 'get_doc_report_dialog_data' ) );
            add_action( 'wp_ajax_set_doc_report_dialog_data', array( $this, 'set_doc_report_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_doc_report_dialog_data', array( $this, 'set_doc_report_dialog_data' ) );
            add_action( 'wp_ajax_del_doc_report_dialog_data', array( $this, 'del_doc_report_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_doc_report_dialog_data', array( $this, 'del_doc_report_dialog_data' ) );

            add_action( 'wp_ajax_duplicate_doc_report_data', array( $this, 'duplicate_doc_report_data' ) );
            add_action( 'wp_ajax_nopriv_duplicate_doc_report_data', array( $this, 'duplicate_doc_report_data' ) );

            add_action( 'wp_ajax_get_doc_field_dialog_data', array( $this, 'get_doc_field_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_doc_field_dialog_data', array( $this, 'get_doc_field_dialog_data' ) );
            add_action( 'wp_ajax_set_doc_field_dialog_data', array( $this, 'set_doc_field_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_doc_field_dialog_data', array( $this, 'set_doc_field_dialog_data' ) );
            add_action( 'wp_ajax_del_doc_field_dialog_data', array( $this, 'del_doc_field_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_doc_field_dialog_data', array( $this, 'del_doc_field_dialog_data' ) );

            add_action( 'wp_ajax_sort_doc_field_list_data', array( $this, 'sort_doc_field_list_data' ) );
            add_action( 'wp_ajax_nopriv_sort_doc_field_list_data', array( $this, 'sort_doc_field_list_data' ) );

            add_action( 'wp_ajax_set_new_site_by_title', array( $this, 'set_new_site_by_title' ) );
            add_action( 'wp_ajax_nopriv_set_new_site_by_title', array( $this, 'set_new_site_by_title' ) );
            add_action( 'wp_ajax_set_initial_iso_document', array( $this, 'set_initial_iso_document' ) );
            add_action( 'wp_ajax_nopriv_set_initial_iso_document', array( $this, 'set_initial_iso_document' ) );
            add_action( 'wp_ajax_reset_document_todo_status', array( $this, 'reset_document_todo_status' ) );
            add_action( 'wp_ajax_nopriv_reset_document_todo_status', array( $this, 'reset_document_todo_status' ) );                                                                    
        }

        function enqueue_display_document_scripts() {
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);        
            $version = time(); // Update this version number when you make changes
            wp_enqueue_script('display-documents', plugins_url('display-documents.js', __FILE__), array('jquery'), $version);
            wp_localize_script('display-documents', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('display-documents-nonce'), // Generate nonce
            ));                
        }        

        // Register document post type
        function register_document_post_type() {
            $labels = array(
                'menu_name'     => _x('Documents', 'admin menu', 'textdomain'),
            );
        
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'document', $args );
        }
        
        function add_document_settings_metabox() {
            add_meta_box(
                'document_settings_id',
                'Document Settings',
                array($this, 'document_settings_content'),
                'document',
                'normal',
                'high'
            );
        }
        
        function document_settings_content($post) {
            $doc_title = esc_attr(get_post_meta($post->ID, 'doc_title', true));
            ?>
            <label for="doc_title"> doc_title: </label>
            <input type="text" id="doc_title" name="doc_title" value="<?php echo $doc_title;?>" style="width:100%" >
            <?php
        }
        
        // Register doc report post type
        function register_doc_report_post_type() {
            $labels = array(
                'menu_name'     => _x('doc-report', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'doc-report', $args );
        }
        
        // Register doc field post type
        function register_doc_field_post_type() {
            $labels = array(
                'menu_name'     => _x('doc-field', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'doc-field', $args );
        }
        
        // Register doc category post type
        function register_doc_category_post_type() {
            $labels = array(
                'menu_name'     => _x('doc-category', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'doc-category', $args );
        }
        
        // Shortcode to display
        function display_shortcode() {
            // Check if the user is logged in
            if (is_user_logged_in()) {

                // Display ISO document statement
                if (isset($_GET['_initial'])) {
                    $doc_number = sanitize_text_field($_GET['_initial']);
                    echo '<div class="ui-widget" id="result-container">';
                    echo $this->display_iso_document_statement($doc_number);
                    echo '</div>';
                }

                // Get shared document if shared doc ID is existed
                if (isset($_GET['_get_shared_doc_id'])) {
                    $doc_id = sanitize_text_field($_GET['_get_shared_doc_id']);
                    $this->get_shared_document($doc_id);
                }
            
                // Display document details if doc_id is existed
                if (isset($_GET['_doc_id'])) {
                    $doc_id = sanitize_text_field($_GET['_doc_id']);
                    echo '<div class="ui-widget" id="result-container">';
                    echo $this->display_document_dialog($doc_id);
                    echo '</div>';
                }
            
                if (isset($_GET['_doc_report'])) {
                    $doc_id = sanitize_text_field($_GET['_doc_report']);
                    echo '<div class="ui-widget" id="result-container">';
                    echo $this->display_doc_report_list($doc_id);
                    echo '</div>';
                }

                if (isset($_GET['_doc_frame'])) {
                    $doc_id = sanitize_text_field($_GET['_doc_frame']);
                    echo '<div class="ui-widget" id="result-container">';
                    echo $this->display_doc_frame_contain($doc_id);
                    echo '</div>';
                }

                // Display document list if no specific document IDs are existed
                if (!isset($_GET['_doc_id']) && !isset($_GET['_doc_report']) && !isset($_GET['_doc_frame']) && !isset($_GET['_initial'])) {
                    echo $this->display_document_list();
                }
            
            } else {
                user_did_not_login_yet();
            }
        }

        function display_document_list() {
            if (isset($_GET['_is_admin'])) {
                echo '<input type="hidden" id="is-admin" value="1" />';
            }
            $profiles_class = new display_profiles();
            ?>
            <div class="ui-widget" id="result-container">
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( '文件總覽', 'your-text-domain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <select id="select-category"><?php echo $profiles_class->select_doc_category_option_data($_GET['_category']);?></select>
                    </div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-document" style="display:inline" placeholder="Search..." />
                        <span id="document-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic button"></span>
                    </div>
                </div>

            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( '文件編號', 'your-text-domain' );?></th>
                            <th><?php echo __( '文件名稱', 'your-text-domain' );?></th>
                            <th><?php echo __( '文件版本', 'your-text-domain' );?></th>
                            <th><?php echo __( '待辦狀態', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_document_list_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages

                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $doc_id = (int) get_the_ID();
                            $doc_number = get_post_meta($doc_id, 'doc_number', true);
                            $doc_title = get_post_meta($doc_id, 'doc_title', true);
                            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
                            $doc_title = get_post_meta($doc_id, 'doc_title', true);
                            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);

                            if ($is_doc_report > 0) {
                                $doc_title = '*' . $doc_title;
                            } elseif ($is_doc_report < 0) {
                                $doc_title = '**' . $doc_title;
                            }
                            
                            $action_query = $profiles_class->retrieve_doc_action_list_data($doc_id);
                            $unassigned = ($action_query->have_posts()) ? '' : '<span style="color:red;">(U)</span>';
                            $doc_title .= $unassigned;

                            $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
                            $todo_id = get_post_meta($doc_id, 'todo_status', true);
                            $todo_status = ($todo_id) ? get_the_title($todo_id) : 'Draft';
                            $todo_status = ($todo_id==-1) ? '文件發行' : $todo_status;
                            $todo_status = ($todo_id==-2) ? '文件廢止' : $todo_status;
                            ?>
                            <tr id="edit-document-<?php echo $doc_id;?>">
                                <td style="text-align:center;"><?php echo esc_html($doc_number);?></td>
                                <td><?php echo $doc_title;?></td>
                                <td style="text-align:center;"><?php echo esc_html($doc_revision);?></td>
                                <td style="text-align:center;"><?php echo esc_html($todo_status);?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <div id="new-document" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>
            </fieldset>
            </div>
            <?php
        }
        
        function retrieve_document_list_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $site_filter = array(
                'key'     => 'site_id',
                'value'   => $site_id,
                'compare' => '=',
            );

            $select_category = sanitize_text_field($_GET['_category']);
            $category_filter = array(
                'key'     => 'doc_category',
                'value'   => $select_category,
                'compare' => '=',
            );

            $search_query = sanitize_text_field($_GET['_search']);
            if ($search_query) $paged = 1;
            $number_filter = array(
                'key'     => 'doc_number',
                'value'   => $search_query,
                'compare' => 'LIKE',
            );
            $title_filter = array(
                'key'     => 'doc_title',
                'value'   => $search_query,
                'compare' => 'LIKE',
            );

            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    'relation' => 'OR',
                    array(
                        'relation' => 'AND',
                        ($site_id) ? $site_filter : '',
                        ($select_category) ? $category_filter : '',
                        ($search_query) ? $number_filter : '',
                    ),
                    array(
                        'relation' => 'AND',
                        ($site_id) ? $site_filter : '',
                        ($select_category) ? $category_filter : '',
                        ($search_query) ? $title_filter : '',
                    )
                ),
                'orderby'        => 'meta_value',
                'meta_key'       => 'doc_number',
                'order'          => 'ASC',
            );

            $query = new WP_Query($args);
            return $query;
        }
        
        function display_document_dialog($doc_id=false) {
            $profiles_class = new display_profiles();
            $todo_class = new to_do_list();

            $job_title = get_the_title($doc_id);
            $job_content = get_post_field('post_content', $doc_id);
            $job_number = get_post_meta($doc_id, 'job_number', true);
            $department = get_post_meta($doc_id, 'department', true);

            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
            $doc_category = get_post_meta($doc_id, 'doc_category', true);
            $doc_frame = get_post_meta($doc_id, 'doc_frame', true);
            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
            $parent_report_id = get_post_meta($doc_id, 'parent_report_id', true);
            $doc_report_frequence_setting = get_post_meta($doc_id, 'doc_report_frequence_setting', true);
            $doc_report_frequence_start_time = get_post_meta($doc_id, 'doc_report_frequence_start_time', true);
            ob_start();
            ?>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <?php echo display_iso_helper_logo();?>
                    <h2 style="display:inline;"><?php echo esc_html($doc_title);?></h2>
                </div>
                <div style="text-align:right; display:flex;">    
                </div>
            </div>

            <input type="hidden" id="doc-id" value="<?php echo esc_attr($doc_id);?>" />
            <fieldset>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <label for="doc-number"><?php echo __( '文件編號', 'your-text-domain' );?></label>
                </div>
                <div style="text-align:right; display:flex;">
                    <span id="reset-document-<?php echo esc_attr($doc_id);?>" class="dashicons dashicons-trash button"></span>
                </div>
            </div>
            <input type="text" id="doc-number" value="<?php echo esc_html($doc_number);?>" class="text ui-widget-content ui-corner-all" />
            <label for="doc-title"><?php echo __( '文件名稱', 'your-text-domain' );?></label>
            <input type="text" id="doc-title" value="<?php echo esc_html($doc_title);?>" class="text ui-widget-content ui-corner-all" />
            <label for="doc-revision"><?php echo __( '文件版本', 'your-text-domain' );?></label>
            <input type="text" id="doc-revision" value="<?php echo esc_html($doc_revision);?>" class="text ui-widget-content ui-corner-all" />
            <label for="doc-category"><?php echo __( '文件類別', 'your-text-domain' );?></label><br>
            <select id="doc-category" class="text ui-widget-content ui-corner-all"><?php echo $profiles_class->select_doc_category_option_data($doc_category);?></select>
            <input type="hidden" id="is-doc-report" value="<?php echo $is_doc_report;?>" />
            <div id="doc-frame-div" style="display:none;">
                <label id="doc-frame-label" class="button" for="doc-frame"><?php echo __( '文件地址', 'your-text-domain' );?></label>
                <span id="doc-frame-preview" class="dashicons dashicons-external button" style="margin-left:5px; vertical-align:text-top;"></span>
                <textarea id="doc-frame" rows="3" style="width:100%;"><?php echo $doc_frame;?></textarea>
                <label id="doc-frame-job-setting" class="button"><?php echo __( '本文件的職務設定', 'your-text-domain' );?></label>
            </div>
            <div id="doc-report-div" style="display:none;">
                <label id="doc-field-label" class="button" for="doc-field"><?php echo __( '欄位設定', 'your-text-domain' );?></label>
                <span id="doc-report-preview" class="dashicons dashicons-external button" style="margin-left:5px; vertical-align:text-top;"></span>
                <?php echo $this->display_doc_field_list($doc_id);?>
                <label id="parent-report-label" class="button"><?php echo __( 'Parent report', 'your-text-domain' );?></label>
                <select id="parent-report-id"  class="text ui-widget-content ui-corner-all">
                    <option><?php echo __( 'Select a parent report', 'your-text-domain' );?></option>
                    <option value="-1" <?php echo ($parent_report_id==-1) ? 'selected' : ''?>><?php echo __( '文件清單', 'your-text-domain' );?></option>
                    <option value="-2" <?php echo ($parent_report_id==-2) ? 'selected' : ''?>><?php echo __( '客戶清單', 'your-text-domain' );?></option>
                    <option value="-3" <?php echo ($parent_report_id==-3) ? 'selected' : ''?>><?php echo __( '供應商清單', 'your-text-domain' );?></option>
                    <option value="-4" <?php echo ($parent_report_id==-4) ? 'selected' : ''?>><?php echo __( '產品清單', 'your-text-domain' );?></option>
                    <option value="-5" <?php echo ($parent_report_id==-5) ? 'selected' : ''?>><?php echo __( '設備清單', 'your-text-domain' );?></option>
                    <option value="-6" <?php echo ($parent_report_id==-6) ? 'selected' : ''?>><?php echo __( '儀器清單', 'your-text-domain' );?></option>
                    <option value="-7" <?php echo ($parent_report_id==-7) ? 'selected' : ''?>><?php echo __( '員工清單', 'your-text-domain' );?></option>
                </select>

                <label id="doc-report-job-setting" class="button"><?php echo __( '表單上的職務設定', 'your-text-domain' );?></label>
            </div>
            <div id="system-report-div" style="display:none;">
                <label id="system-report-label" class="button"><?php echo __( '系統表單', 'your-text-domain' );?></label>
                <span id="system-report-preview" class="dashicons dashicons-external button" style="margin-left:5px; vertical-align:text-top;"></span>
                <select id="select-system-report"  class="text ui-widget-content ui-corner-all">
                    <option><?php echo __( 'Select a system report', 'your-text-domain' );?></option>
                    <option value="-1" <?php echo ($is_doc_report==-1) ? 'selected' : ''?>><?php echo __( '文件清單', 'your-text-domain' );?></option>
                    <option value="-2" <?php echo ($is_doc_report==-2) ? 'selected' : ''?>><?php echo __( '客戶清單', 'your-text-domain' );?></option>
                    <option value="-3" <?php echo ($is_doc_report==-3) ? 'selected' : ''?>><?php echo __( '供應商清單', 'your-text-domain' );?></option>
                    <option value="-4" <?php echo ($is_doc_report==-4) ? 'selected' : ''?>><?php echo __( '產品清單', 'your-text-domain' );?></option>
                    <option value="-5" <?php echo ($is_doc_report==-5) ? 'selected' : ''?>><?php echo __( '設備清單', 'your-text-domain' );?></option>
                    <option value="-6" <?php echo ($is_doc_report==-6) ? 'selected' : ''?>><?php echo __( '儀器清單', 'your-text-domain' );?></option>
                    <option value="-7" <?php echo ($is_doc_report==-7) ? 'selected' : ''?>><?php echo __( '員工清單', 'your-text-domain' );?></option>
                </select>
            </div>

            <!-- Define the import map -->
            <script type="importmap">
            {
                "imports": {
                    "@wordpress/interactivity": "https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs"
                }
            }
            </script>

            <script type="module">
                import mermaid from '@wordpress/interactivity';
                mermaid.initialize({ startOnLoad: true });
            </script>

            <div id="mermaid-div">
            <pre class="mermaid">
                graph TD 
                <?php
                $query = $profiles_class->retrieve_doc_action_list_data($doc_id, true);
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        $action_title = get_the_title();
                        $action_content = get_post_field('post_content', get_the_ID());
                        $current_job = get_post_meta(get_the_ID(), 'doc_id', true);
                        $current_job_title = get_the_title($current_job);
                        $next_job = get_post_meta(get_the_ID(), 'next_job', true);
                        $next_job_title = get_the_title($next_job);
                        $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
                        if ($next_job==-1) {
                            $next_job_title = __( '文件發行', 'your-text-domain' );
                            if ($is_doc_report==1) $next_job_title = __( '記錄存檔', 'your-text-domain' );
                        }
                        if ($next_job==-2) {
                            $next_job_title = __( '文件廢止', 'your-text-domain' );
                            if ($is_doc_report==1) $next_job_title = __( '記錄作廢', 'your-text-domain' );
                        }
                        ?>
                        <?php echo $current_job_title;?>-->|<?php echo $action_title;?>|<?php echo $next_job_title;?>;
                        <?php
                    endwhile;
                    wp_reset_postdata();
                endif;    
                ?>
            </pre>
            </div>

            <div id="job-setting-div" style="display:none;">
                <label for="department"><?php echo __( '部門', 'your-text-domain' );?></label>
                <input type="text" id="department" value="<?php echo esc_html($department);?>" class="text ui-widget-content ui-corner-all" />
                <label for="job-number"><?php echo __( '職務編號', 'your-text-domain' );?></label>
                <input type="text" id="job-number" value="<?php echo esc_html($job_number);?>" class="text ui-widget-content ui-corner-all" />
                <label for="job-title"><?php echo __( '職務名稱', 'your-text-domain' );?></label>
                <input type="text" id="job-title" value="<?php echo esc_html($job_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="job-content"><?php echo __( '職務說明', 'your-text-domain' );?></label>
                <textarea id="job-content" rows="3" style="width:100%;"><?php echo $job_content;?></textarea>
                <label for="action-list"><?php echo __( '執行按鍵設定', 'your-text-domain' );?></label>
                <?php echo $profiles_class->display_doc_action_list($doc_id);?>
            </div>

            <div id="doc-report-div1" style="display:none;">            
                <label for="doc-report-frequence-setting"><?php echo __( '循環表單啟動設定', 'your-text-domain' );?></label>
                <select id="doc-report-frequence-setting" class="text ui-widget-content ui-corner-all"><?php echo $todo_class->select_doc_report_frequence_setting_option($doc_report_frequence_setting);?></select>
                <div id="frquence-start-time-div" style="display:none;">
                    <label for="doc-report-frequence-start-time"><?php echo __( '循環表單啟動時間', 'your-text-domain' );?></label><br>
                    <input type="date" id="doc-report-frequence-start-date" value="<?php echo wp_date('Y-m-d', $doc_report_frequence_start_time);?>" />
                    <input type="time" id="doc-report-frequence-start-time" value="<?php echo wp_date('H:i', $doc_report_frequence_start_time);?>" />
                    <input type="hidden" id="prev-start-time" value="<?php echo $doc_report_frequence_start_time;?>" />
                </div>
            </div>
            <hr>
            <input type="button" id="save-document-button" value="<?php echo __( 'Save', 'your-text-domain' );?>" style="margin:3px;" />
            <input type="button" id="del-document-button" value="<?php echo __( 'Delete', 'your-text-domain' );?>" style="margin:3px;" />
            </fieldset>
            <?php
            return ob_get_clean();
        }
        
        function get_document_dialog_data() {
            $response = array();
            if (isset($_POST['_doc_id'])) {
                $doc_id = sanitize_text_field($_POST['_doc_id']);
                $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
                $todo_status = get_post_meta($doc_id, 'todo_status', true);
                $profiles_class = new display_profiles();
                $is_site_admin = $profiles_class->is_site_admin();
                if (current_user_can('administrator')) $is_site_admin = true;
                $is_user_doc = $profiles_class->is_user_doc($doc_id);
                $response['is_doc_report'] = $is_doc_report;
                $response['todo_status'] = $todo_status;
                $response['is_site_admin'] = $is_site_admin;
                $response['is_user_doc'] = $is_user_doc;
            }
            wp_send_json($response);
        }
        
        function set_document_dialog_data() {
            $response = array();
            if( isset($_POST['_doc_id']) ) {
                // Update the Document data
                $doc_id = sanitize_text_field($_POST['_doc_id']);
                $doc_post_args = array(
                    'ID'           => $doc_id,
                    'post_title'   => sanitize_text_field($_POST['_job_title']),
                    'post_content' => $_POST['_job_content'],
                );
                wp_update_post($doc_post_args);
                $job_number = sanitize_text_field($_POST['_job_number']);
                if ($job_number) update_post_meta( $doc_id, 'job_number', $job_number);
                else update_post_meta( $doc_id, 'job_number', sanitize_text_field($_POST['_doc_number']));
                update_post_meta( $doc_id, 'department', sanitize_text_field($_POST['_department']));

                update_post_meta( $doc_id, 'doc_number', sanitize_text_field($_POST['_doc_number']));
                update_post_meta( $doc_id, 'doc_title', sanitize_text_field($_POST['_doc_title']));
                update_post_meta( $doc_id, 'doc_revision', sanitize_text_field($_POST['_doc_revision']));
                update_post_meta( $doc_id, 'doc_category', sanitize_text_field($_POST['_doc_category']));
                update_post_meta( $doc_id, 'doc_frame', $_POST['_doc_frame']);
                update_post_meta( $doc_id, 'is_doc_report', sanitize_text_field($_POST['_is_doc_report']));
                update_post_meta( $doc_id, 'parent_report_id', sanitize_text_field($_POST['_parent_report_id']));

                $doc_report_frequence_setting = sanitize_text_field($_POST['_doc_report_frequence_setting']);
                update_post_meta( $doc_id, 'doc_report_frequence_setting', $doc_report_frequence_setting);
                // Get the timezone offset from WordPress settings
                $timezone_offset = get_option('gmt_offset');
                // Convert the timezone offset to seconds
                $offset_seconds = $timezone_offset * 3600; // Convert hours to seconds
                $doc_report_frequence_start_date = sanitize_text_field($_POST['_doc_report_frequence_start_date']);
                $doc_report_frequence_start_time = sanitize_text_field($_POST['_doc_report_frequence_start_time']);
                $doc_report_frequence = strtotime($doc_report_frequence_start_date.' '.$doc_report_frequence_start_time);
                update_post_meta( $doc_id, 'doc_report_frequence_start_time', $doc_report_frequence - $offset_seconds);
                $params = array(
                    'interval' => $doc_report_frequence_setting,
                    'start_time' => $doc_report_frequence - $offset_seconds,
                    'prev_start_time' => sanitize_text_field($_POST['_prev_start_time']),
                    'doc_id' => $doc_id,
                );            
                $todo_class = new to_do_list();
                if ($doc_report_frequence_setting) $hook_name=$todo_class->schedule_post_event_callback($params);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => 'No title',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'document',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta( $post_id, 'site_id', $site_id);
                update_post_meta( $post_id, 'doc_number', '-');
                update_post_meta( $post_id, 'doc_revision', 'A');
                update_post_meta( $post_id, 'doc_report_frequence_start_time', time());
            }
            wp_send_json($response);
        }
        
        function del_document_dialog_data() {
            $response = array();
            wp_delete_post($_POST['_doc_id'], true);
            wp_send_json($response);
        }
        
        // doc-frame
        function display_doc_frame_contain($doc_id=false) {
            ob_start();
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
            $doc_frame = get_post_meta($doc_id, 'doc_frame', true);
            ?>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <?php echo display_iso_helper_logo();?>
                    <span><?php echo esc_html($doc_number);?></span>
                    <h2 style="display:inline;"><?php echo esc_html($doc_title);?></h2>
                    <span><?php echo esc_html($doc_revision);?></span>
                </div>
                <div style="text-align:right; display:flex;">
                    <input type="button" id="signature-record" value="<?php echo __( '文件簽核記錄', 'your-text-domain' );?>" style="margin:3px; font-size:small;" />
                    <input type="button" id="share-document" value="<?php echo __( '文件分享', 'your-text-domain' );?>" style="margin:3px; font-size:small;" />
                    <input type="button" id="doc-frame-exit" value="<?php echo __( 'Exit', 'your-text-domain' );?>" style="margin:3px; font-size:small;" />
                    <span id='doc-frame-unpublished' style='margin-left:5px;' class='dashicons dashicons-trash button'></span>
                </div>
            </div>
        
            <div id="signature-record-div" style="display:none;">
                <?php $todo_class = new to_do_list();?>
                <?php $signature_record_list = $todo_class->get_signature_record_list($doc_id);?>
                <?php echo $signature_record_list['html']?>
            </div>
            
            <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />

            <fieldset style="overflow-x:auto; white-space:nowrap;">
                <?php echo $doc_frame; ?>
            </fieldset>

            <?php
                // transaction data vs card key/value
                $key_pairs = array(
                    '_document'   => $doc_id,
                );
                $profiles_class = new display_profiles();
                $profiles_class->get_transactions_by_card_key_value($key_pairs);
            ?>
            <?php
            return ob_get_clean();
        }
        
        function get_doc_frame_contain() {
            $result = array();
            if (isset($_POST['_doc_id'])) {
                $doc_id = sanitize_text_field($_POST['_doc_id']);
                $result['html_contain'] = $this->display_doc_frame_contain($doc_id);
            } else {
                $result['html_contain'] = 'Invalid AJAX request!';
            }
            wp_send_json($result);
        }
        
        // doc-report
        function display_doc_report_list($doc_id=false, $search_doc_report=false) {

            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
            ob_start();
            ?>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <?php echo display_iso_helper_logo();?>
                    <span><?php echo esc_html($doc_number);?></span>
                    <h2 style="display:inline;"><?php echo esc_html($doc_title);?></h2>
                    <span><?php echo esc_html($doc_revision);?></span>            
                </div>
                <div style="text-align:right; display:flex;">
                    <input type="button" id="signature-record" value="<?php echo __( '文件簽核記錄', 'your-text-domain' );?>" style="margin:3px; font-size:small;" />
                    <input type="button" id="share-document" value="<?php echo __( '文件分享', 'your-text-domain' );?>" style="margin:3px; font-size:small;" />
                    <input type="button" id="doc-report-exit" value="<?php echo __( 'Exit', 'your-text-domain' );?>" style="margin:3px; font-size:small;" />
                    <span id='doc-report-unpublished' style='margin-left:5px;' class='dashicons dashicons-trash button'></span>
                </div>
            </div>
        
            <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />
            
            <div id="signature-record-div" style="display:none;">
                <?php $todo_class = new to_do_list();?>
                <?php $signature_record_list = $todo_class->get_signature_record_list($doc_id);?>
                <?php echo $signature_record_list['html']?>
            </div>
        
                <div id="doc-report-setting-dialog" title="Doc-report setting" style="display:none">
                    <fieldset>
                        <label for="doc-title"><?php echo __( 'Document:', 'your-text-domain' );?></label>
                        <input type="text" id="doc-title" value="<?php echo $doc_title;?>" class="text ui-widget-content ui-corner-all" disabled />
                        <label for="doc-field-setting"><?php echo __( 'Field setting:', 'your-text-domain' );?></label>
                        <?php echo $this->display_doc_field_list($doc_id);?>
                    </fieldset>
                </div>        

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div></div>                    
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-doc-report" style="display:inline" placeholder="Search..." />
                        <span id="doc-report-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic button"></span>
                    </div>
                </div>

            <fieldset>
                <?php
                $this->display_doc_report_native_list($doc_id, $search_doc_report);
                ?>
            
                <div id="new-doc-report" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>

                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>
            </fieldset>
            <?php
            return ob_get_clean();
        }
        
        function display_doc_report_native_list($doc_id=false, $search_doc_report=false, $key_pairs=array()) {
            ?>
                <table style="width:100%;">
                    <thead>
                        <?php
                        $params = array(
                            'doc_id'     => $doc_id,
                            'is_listing' => true,
                        );                
                        $query = $this->retrieve_doc_field_data($params);
                        if ($query->have_posts()) {
                            echo '<tr>';
                            while ($query->have_posts()) : $query->the_post();
                                $field_title = get_post_meta(get_the_ID(), 'field_title', true);
                                echo '<th>'.esc_html($field_title).'</th>';
                            endwhile;
                            echo '<th>'. __( '待辦', 'your-text-domain' ).'</th>';
                            echo '</tr>';
                            wp_reset_postdata();
                        }
                        ?>
                    </thead>
                    <tbody>
                        <?php
                        $paged = max(1, get_query_var('paged')); // Get the current page number
                        $params = array(
                            'doc_id'     => $doc_id,
                            'paged'     => $paged,
                            'search_doc_report' => $search_doc_report,
                            'key_pairs' => $key_pairs,
                        );                
                        $query = $this->retrieve_doc_report_list_data($params);
                        $total_posts = $query->found_posts;
                        $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
            
                        if ($query->have_posts()) {
                            while ($query->have_posts()) : $query->the_post();
                                $report_id = get_the_ID();
                                echo '<tr id="edit-doc-report-'.$report_id.'">';
                                $params = array(
                                    'doc_id'     => $doc_id,
                                    'is_listing'  => true,
                                );                
                                $inner_query = $this->retrieve_doc_field_data($params);
                                if ($inner_query->have_posts()) {
                                    while ($inner_query->have_posts()) : $inner_query->the_post();
                                        $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                                        $field_type = get_post_meta(get_the_ID(), 'field_type', true);
                                        $listing_style = get_post_meta(get_the_ID(), 'listing_style', true);
                                        $field_value = get_post_meta($report_id, $field_name, true);
                                        $is_checked = ($field_value==1) ? 'checked' : '';
                                        echo '<td style="text-align:'.$listing_style.';">';
                                        if ($field_type=='checkbox') {
                                            echo '<input type="checkbox" '.$is_checked.' />';
                                        } elseif ($field_type=='radio') {
                                            echo '<input type="radio" '.$is_checked.' />';
                                        } elseif ($field_type=='_document') {
                                            $doc_title = get_post_meta($field_value, 'doc_title', true);
                                            $doc_number = get_post_meta($field_value, 'doc_number', true);
                                            echo esc_html($doc_title.'('.$doc_number.')');
                                        } elseif ($field_type=='_customer') {
                                            $customer_code = get_post_meta($field_value, 'customer_code', true);
                                            echo esc_html(get_the_title($field_value).'('.$customer_code.')');
                                        } elseif ($field_type=='_vendor') {
                                            $vendor_code = get_post_meta($field_value, 'vendor_code', true);
                                            echo esc_html(get_the_title($field_value).'('.$vendor_code.')');
                                        } elseif ($field_type=='_product') {
                                            $product_code = get_post_meta($field_value, 'product_code', true);
                                            echo esc_html(get_the_title($field_value).'('.$product_code.')');
                                        } elseif ($field_type=='_equipment') {
                                            $equipment_code = get_post_meta($field_value, 'equipment_code', true);
                                            echo esc_html(get_the_title($field_value).'('.$equipment_code.')');
                                        } elseif ($field_type=='_instrument') {
                                            $instrument_code = get_post_meta($field_value, 'instrument_code', true);
                                            echo esc_html(get_the_title($field_value).'('.$instrument_code.')');
                                        } elseif ($field_type=='_department') {
                                            $instrument_code = get_post_meta($field_value, 'department_code', true);
                                            echo esc_html(get_the_title($field_value).'('.$department_code.')');
                                        } elseif ($field_type=='_employee') {
                                            $user = get_userdata($field_value);
                                            echo $user->display_name;
                                        } else {
                                            echo esc_html($field_value);
                                        }
                                        echo '</td>';
                                    endwhile;                
                                    wp_reset_postdata();
                                }
                                $todo_id = get_post_meta($report_id, 'todo_status', true);
                                $todo_status = ($todo_id) ? get_the_title($todo_id) : 'Draft';
                                $todo_status = ($todo_id==-1) ? '記錄存檔' : $todo_status;
                                $todo_status = ($todo_id==-2) ? '記錄作廢' : $todo_status;
                                echo '<td style="text-align:center;">'.esc_html($todo_status).'</td>';
                                echo '</tr>';
                            endwhile;                
                            wp_reset_postdata();
                        }
                        ?>
                    </tbody>
                </table>
            <?php
        }

        function retrieve_doc_report_list_data($params) {
            // Construct the meta query array
            $meta_query = array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                ),
                array(
                    'relation' => 'OR',
                ),
            );
    
            if (!empty($params['doc_id'])) {
                $doc_id = $params['doc_id'];
                $meta_query[] = array(
                    'key'   => 'doc_id',
                    'value' => $doc_id,
                );
            }

            if (!empty($params['paged'])) {
                $paged = $params['paged'];
            } else {
                $paged = 1;
            }

            if (!empty($params['search_doc_report'])) {
                $search_doc_report = $params['search_doc_report'];
            }

            if (!empty($params['key_pairs'])) {
                $key_pairs = $params['key_pairs'];
            }

            $args = array(
                'post_type'      => 'doc-report',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => $meta_query,
                'orderby'        => array(), // Initialize orderby parameter as an array
            );
        
            $order_field_name = ''; // Initialize variable to store the meta key for ordering
            $order_field_value = ''; // Initialize variable to store the order direction
        
            $inner_query = $this->retrieve_doc_field_data(array('doc_id' => $doc_id));
        
            if ($inner_query->have_posts()) {
                while ($inner_query->have_posts()) : $inner_query->the_post();
                    $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                    $field_type = get_post_meta(get_the_ID(), 'field_type', true);

                    if ($key_pairs) {
                        foreach ($key_pairs as $key => $value) {
                            if ($key==$field_type) {
                                $args['meta_query'][0][] = array(
                                    'key'   => $field_name,
                                    'value' => $value,
                                );    
                            }
                        }    
                    }

                    // Check if the order_field_value is valid
                    $order_field_value = get_post_meta(get_the_ID(), 'order_field', true);
                    if ($order_field_value === 'ASC' || $order_field_value === 'DESC') {
                        // Add the field_name and order_field_value to orderby array
                        $args['orderby'][$field_name] = $order_field_value;
                        
                        $order_field_name = $field_name; // Assign the field_name if order_field_value is valid
                    }
        
                    if ($search_doc_report) {
                        $args['meta_query'][1][] = array( // Append to the OR relation
                            'key'     => $field_name,
                            'value'   => $search_doc_report,
                            'compare' => 'LIKE',
                        );
                    }
                endwhile;
        
                // Reset only the inner loop's data
                wp_reset_postdata();
            }
        
            $args['orderby'] = array(
                'index' => 'ASC',
            );
        
            $args['orderby']  = 'meta_value';
            $args['order']    = 'ASC';    
            $args['meta_key'] = $order_field_name;
        
            $query = new WP_Query($args);
            return $query;
        }
        
        function get_doc_report_list_data() {
            $result = array();
            if (isset($_POST['_doc_id'])) {
                $doc_id = sanitize_text_field($_POST['_doc_id']);
                $search_doc_report = sanitize_text_field($_POST['_search_doc_report']);
                if ($search_doc_report) {
                    $result['html_contain'] = $this->display_doc_report_list($doc_id, $search_doc_report);
                } else {
                    $result['html_contain'] = $this->display_doc_report_list($doc_id);
                }
            }
            wp_send_json($result);
        }
        
        function display_doc_report_dialog($report_id=false) {
            $todo_status = get_post_meta($report_id, 'todo_status', true);
            $doc_id = get_post_meta($report_id, 'doc_id', true);
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
            if ($is_doc_report) $doc_title .= '(電子表單)';
            ob_start();
            ?>
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <?php echo display_iso_helper_logo();?>
                    <h2 style="display:inline;"><?php echo esc_html($doc_title);?></h2>
                </div>
                <div style="text-align:right; display:flex;">        
                <?php if ($todo_status){?>
                    <button id="signature-record" style="margin-right:5px; font-size:small;" class="button"><?php echo __('表單簽核記錄', 'your-text-domain')?></button>
                    <span id='report-unpublished-<?php echo esc_attr($report_id);?>' style='margin-left:5px;' class='dashicons dashicons-trash button'></span>
                <?php }?>
                </div>
            </div>
        
            <div id="report-signature-record-div" style="display:none;">
                <?php $todo_class = new to_do_list();?>
                <?php $signature_record_list = $todo_class->get_signature_record_list(false, $report_id);?>
                <?php echo $signature_record_list['html']?>
            </div>
        
            <input type="hidden" id="report-id" value="<?php echo esc_attr($report_id);?>" />
            <input type="hidden" id="doc-id" value="<?php echo esc_attr($doc_id);?>" />
            <fieldset>
            <?php
                $params = array(
                    'doc_id'     => $doc_id,
                    'report_id'     => $report_id,
                );                
                $this->display_doc_field_contains($params);
            ?>
            <hr>
            <?php
            if (empty($todo_status)){
                ?>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                <?php
                $profiles_class = new display_profiles();
                $query = $profiles_class->retrieve_doc_action_list_data($doc_id);
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        echo '<input type="button" id="doc-report-dialog-button-'.get_the_ID().'" value="'.get_the_title().'" style="margin:5px;" />';
                    endwhile;
                    wp_reset_postdata();
                }
                ?>
                </div>
                <div style="text-align:right; display:flex;">
                    <input type="button" id="del-doc-report-<?php echo $report_id;?>" value="<?php echo __( 'Delete', 'your-text-domain' );?>" style="margin:3px;" />
                </div>
                </div>
                <input type="checkbox" id="proceed-to-todo" />
                <label for="proceed-to-todo"><?php echo __('Proceed to Todo', 'your-text-domain')?></label>
                <?php
            } else {
                ?>
                <div>
                    <input type="button" id="doc-report-dialog-exit" value="<?php echo __( 'Exit', 'your-text-domain' );?>" style="margin:5px;" />
                    <input type="button" id="duplicate-doc-report-<?php echo $report_id;?>" value="<?php echo __( 'Duplicate', 'your-text-domain' );?>" style="margin:3px;" />
                </div>
                <?php
            }
            ?>
            </fieldset>
            <?php
            return ob_get_clean();
        }
        
        function get_doc_report_dialog_data() {
            $result = array();
            if (isset($_POST['_report_id'])) {
                $report_id = sanitize_text_field($_POST['_report_id']);
                $todo_status = get_post_meta($report_id, 'todo_status', true);
                if ($todo_status<1) {
                    $result['html_contain'] = $this->display_doc_report_dialog($report_id);
                    $doc_id = get_post_meta($report_id, 'doc_id', true);
                    $result['doc_id'] = $doc_id;
                    $result['doc_fields'] = $this->display_doc_field_keys($doc_id);
                }
            }
            wp_send_json($result);
        }
        
        function set_doc_report_dialog_data() {
            if( isset($_POST['_report_id']) ) {
                // Update the post
                $report_id = sanitize_text_field($_POST['_report_id']);
                $doc_id = get_post_meta($report_id, 'doc_id', true);
                $params = array(
                    'doc_id'     => $doc_id,
                );                
                $query = $this->retrieve_doc_field_data($params);
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                        $field_value = $_POST[$field_name];
                        update_post_meta( $report_id, $field_name, $field_value);
                    endwhile;
                    wp_reset_postdata();
                }
                $proceed_to_todo = sanitize_text_field($_POST['_proceed_to_todo']);
                $action_id = sanitize_text_field($_POST['_action_id']);
                $params = array(
                    'doc_id'     => $doc_id,
                );                
                if ($proceed_to_todo==1) $this->set_todo_from_doc_report($action_id, $report_id);
            } else {
                // Create the post
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'doc-report',
                );    
                $post_id = wp_insert_post($new_post);
                $doc_id = sanitize_text_field($_POST['_doc_id']);
                update_post_meta( $post_id, 'doc_id', $doc_id);
                $params = array(
                    'doc_id'     => $doc_id,
                );                
                $query = $this->retrieve_doc_field_data($params);
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                        $default_value = get_post_meta(get_the_ID(), 'default_value', true);
                        // put the custom function here to support the default value for the new record
                        if ($default_value=='today') $default_value=wp_date('Y-m-d', time());
                        update_post_meta( $post_id, $field_name, $default_value);
                    endwhile;
                    wp_reset_postdata();
                }
            }
            wp_send_json($response);
        }

        function del_doc_report_dialog_data() {
            $response = array();
            wp_delete_post($_POST['_report_id'], true);
            wp_send_json($response);
        }
        
        function duplicate_doc_report_data() {
            if( isset($_POST['_report_id']) ) {
                // Create the post
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'doc-report',
                );    
                $post_id = wp_insert_post($new_post);
                $report_id = sanitize_text_field($_POST['_report_id']);
                $doc_id = get_post_meta($report_id, 'doc_id', true);
                update_post_meta( $post_id, 'doc_id', $doc_id);
        
                $params = array(
                    'doc_id'     => $doc_id,
                );                
                $query = $this->retrieve_doc_field_data($params);
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                        $field_value = sanitize_text_field($_POST[$field_name]);
                        update_post_meta( $post_id, $field_name, $field_value);
                    endwhile;
                    wp_reset_postdata();
                }
            }
            wp_send_json($response);
        }
        
        // doc-field
        function display_doc_field_list($doc_id=false) {
            ob_start();
            ?>
            <div id="fields-container">
            <fieldset>
                <table style="width:100%;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo __( 'Title', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Field', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Type', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Default', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody id="sortable-doc-field-list">
                        <?php
                        $x = 0;
                        if ($doc_id) $params = array('doc_id' => $doc_id);
                        $query = $this->retrieve_doc_field_data($params);
                        if ($query->have_posts()) {
                            while ($query->have_posts()) : $query->the_post();
                                $order_field = get_post_meta(get_the_ID(), 'order_field', true);
                                if ($order_field=='ASC') $order_field='checked';
                                echo '<tr class="doc-field-list-'.$x.'" id="edit-doc-field-'.esc_attr(get_the_ID()).'" data-field-id="'.esc_attr(get_the_ID()).'">';
                                echo '<td style="text-align:center;"><input type="radio" '.$order_field.' name="order_field"></td>';
                                echo '<td style="text-align:center;">'.esc_html(get_post_meta(get_the_ID(), 'field_title', true)).'</td>';
                                echo '<td style="text-align:center;">'.esc_html(get_post_meta(get_the_ID(), 'field_name', true)).'</td>';
                                echo '<td style="text-align:center;">'.esc_html(get_post_meta(get_the_ID(), 'field_type', true)).'</td>';
                                echo '<td style="text-align:center;">'.esc_html(get_post_meta(get_the_ID(), 'default_value', true)).'</td>';
                                echo '</tr>';
                                $x += 1;
                            endwhile;
                            wp_reset_postdata();
                        }
                        ?>
                    </tbody>
                </table>
                <div id="new-doc-field" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
            </fieldset>
            </div>
            <div id="doc-field-dialog" title="Field dialog"></div>
            <?php
            return ob_get_clean();
        }
        
        function retrieve_doc_field_data($params = array()) {
            $args = array(
                'post_type'      => 'doc-field',
                'posts_per_page' => -1,
                'meta_key'       => 'sorting_key',
                'orderby'        => 'meta_value_num', // Specify meta value as numeric
                'order'          => 'ASC',
            );

            if (!empty($params['doc_id'])) {
                $args['meta_query'][] = array(
                    'key'   => 'doc_id',
                    'value' => $params['doc_id'],
                );
            }

            if (!empty($params['is_listing'])) {
                $args['meta_query'][] = array(
                    'key'     => 'listing_style',
                    'value'   => '',
                    'compare' => '!=',
                );
            }

            if (!empty($params['is_editing'])) {
                $args['meta_query'][] = array(
                    'key'     => 'field_type',
                    'value'   => '',
                    'compare' => '!=',
                );
            }

            $query = new WP_Query($args);
            return $query;
        }

        function display_doc_field_dialog($field_id=false) {
            $field_name = get_post_meta($field_id, 'field_name', true);
            $field_title = get_post_meta($field_id, 'field_title', true);
            $field_type = get_post_meta($field_id, 'field_type', true);
            $listing_style = get_post_meta($field_id, 'listing_style', true);
            $default_value = get_post_meta($field_id, 'default_value', true);
            $order_field = get_post_meta($field_id, 'order_field', true);
            ob_start();
            ?>
            <div id="doc-field-dialog-backup">
            <fieldset>
                <input type="hidden" id="field-id" value="<?php echo esc_attr($field_id);?>" />
                <label for="field-title"><?php echo __( '欄位顯示：', 'your-text-domain' );?></label>
                <input type="text" id="field-title" value="<?php echo esc_attr($field_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="field-name"><?php echo __( '欄位名稱：', 'your-text-domain' );?></label>
                <input type="text" id="field-name" value="<?php echo esc_attr($field_name);?>" class="text ui-widget-content ui-corner-all" />
                <label for="field-type"><?php echo __( '欄位型態：', 'your-text-domain' );?></label>
                <select id="field-type" class="text ui-widget-content ui-corner-all">
                    <option value="text" <?php echo ($field_type=='text') ? 'selected' : ''?>><?php echo __( 'Text', 'your-text-domain' );?></option>
                    <option value="number" <?php echo ($field_type=='number') ? 'selected' : ''?>><?php echo __( 'Number', 'your-text-domain' );?></option>
                    <option value="date" <?php echo ($field_type=='date') ? 'selected' : ''?>><?php echo __( 'Date', 'your-text-domain' );?></option>
                    <option value="time" <?php echo ($field_type=='time') ? 'selected' : ''?>><?php echo __( 'Time', 'your-text-domain' );?></option>
                    <option value="checkbox" <?php echo ($field_type=='checkbox') ? 'selected' : ''?>><?php echo __( 'Checkbox', 'your-text-domain' );?></option>
                    <option value="radio" <?php echo ($field_type=='radio') ? 'selected' : ''?>><?php echo __( 'Radio', 'your-text-domain' );?></option>
                    <option value="textarea" <?php echo ($field_type=='textarea') ? 'selected' : ''?>><?php echo __( 'Textarea', 'your-text-domain' );?></option>
                    <option value="heading" <?php echo ($field_type=='heading') ? 'selected' : ''?>><?php echo __( 'Caption', 'your-text-domain' );?></option>
                    <option value="image" <?php echo ($field_type=='image') ? 'selected' : ''?>><?php echo __( 'Picture', 'your-text-domain' );?></option>
                    <option value="video" <?php echo ($field_type=='video') ? 'selected' : ''?>><?php echo __( 'Video', 'your-text-domain' );?></option>
                    <option value="_document" <?php echo ($field_type=='_document') ? 'selected' : ''?>><?php echo __( 'Document', 'your-text-domain' );?></option>
                    <option value="_customer" <?php echo ($field_type=='_customer') ? 'selected' : ''?>><?php echo __( 'Customer', 'your-text-domain' );?></option>
                    <option value="_vendor" <?php echo ($field_type=='_vendor') ? 'selected' : ''?>><?php echo __( 'Vendor', 'your-text-domain' );?></option>
                    <option value="_product" <?php echo ($field_type=='_product') ? 'selected' : ''?>><?php echo __( 'Product', 'your-text-domain' );?></option>
                    <option value="_equipment" <?php echo ($field_type=='_equipment') ? 'selected' : ''?>><?php echo __( 'Equipment', 'your-text-domain' );?></option>
                    <option value="_instrument" <?php echo ($field_type=='_instrument') ? 'selected' : ''?>><?php echo __( 'Instrument', 'your-text-domain' );?></option>
                    <option value="_employee" <?php echo ($field_type=='_employee') ? 'selected' : ''?>><?php echo __( 'Employee', 'your-text-domain' );?></option>
                </select>
                <label for="listing-style"><?php echo __( '列表排列：', 'your-text-domain' );?></label>
                <select id="listing-style" class="text ui-widget-content ui-corner-all">
                    <option value=""></option>
                    <option value="left" <?php echo ($listing_style=='left') ? 'selected' : ''?>><?php echo __( '靠左', 'your-text-domain' );?></option>
                    <option value="center" <?php echo ($listing_style=='center') ? 'selected' : ''?>><?php echo __( '置中', 'your-text-domain' );?></option>
                    <option value="right" <?php echo ($listing_style=='right') ? 'selected' : ''?>><?php echo __( '靠右', 'your-text-domain' );?></option>
                </select>
                <label for="default-value"><?php echo __( '初始值：', 'your-text-domain' );?></label>
                <input type="text" id="default-value" value="<?php echo esc_attr($default_value);?>" class="text ui-widget-content ui-corner-all" />
                <input type="checkbox" id="order-field" <?php echo ($order_field=='ASC') ? 'checked' : '';?> />
                <label for="order-field"><?php echo __( '索引鍵', 'your-text-domain' );?></label>
            </fieldset>
            </div>
            <?php
            return ob_get_clean();
        }

        function get_doc_field_dialog_data() {
            $response = array();
            if( isset($_POST['_field_id']) ) {
                $field_id = sanitize_text_field($_POST['_field_id']);
                $response['html_contain'] = $this->display_doc_field_dialog($field_id);
            }
            wp_send_json($response);
        }

        function set_doc_field_dialog_data() {
            $response = array();
            if( isset($_POST['_field_id']) ) {
                // Update the post
                $field_id = sanitize_text_field($_POST['_field_id']);
                update_post_meta( $field_id, 'field_name', sanitize_text_field($_POST['_field_name']));
                update_post_meta( $field_id, 'field_title', sanitize_text_field($_POST['_field_title']));
                update_post_meta( $field_id, 'field_type', sanitize_text_field($_POST['_field_type']));
                update_post_meta( $field_id, 'default_value', sanitize_text_field($_POST['_default_value']));
                update_post_meta( $field_id, 'listing_style', sanitize_text_field($_POST['_listing_style']));
                update_post_meta( $field_id, 'order_field', sanitize_text_field($_POST['_order_field']));
            } else {
                // Create the post
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'doc-field',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta( $post_id, 'doc_id', sanitize_text_field($_POST['_doc_id']));
                update_post_meta( $post_id, 'field_name', 'new_field');
                update_post_meta( $post_id, 'field_title', 'Field title');
                update_post_meta( $post_id, 'field_type', 'text');
                update_post_meta( $post_id, 'listing_style', 'center');
                update_post_meta( $post_id, 'sorting_key', -1);
            }
            $doc_id = sanitize_text_field($_POST['_doc_id']);
            $response['html_contain'] = $this->display_doc_field_list($doc_id);
            wp_send_json($response);
        }

        function del_doc_field_dialog_data() {
            $response = array();
            wp_delete_post($_POST['_field_id'], true);
            $doc_id = sanitize_text_field($_POST['_doc_id']);
            $response['html_contain'] = $this->display_doc_field_list($doc_id);
            wp_send_json($response);
        }

        function sort_doc_field_list_data() {
            $response = array('success' => false, 'error' => 'Invalid data format');
            if (isset($_POST['_field_id_array']) && is_array($_POST['_field_id_array'])) {
                $field_id_array = array_map('absint', $_POST['_field_id_array']);        
                foreach ($field_id_array as $index => $field_id) {
                    update_post_meta( $field_id, 'sorting_key', $index);
                }
                $response = array('success' => true);
            }
            wp_send_json($response);
        }

        function display_doc_field_keys($doc_id=false) {
            if ($doc_id) $params = array('doc_id' => $doc_id);
            $query = $this->retrieve_doc_field_data($params);
            $_array = array();
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $_list = array();
                    $_list["field_name"] = get_post_meta(get_the_ID(), 'field_name', true);
                    $_list["field_type"] = get_post_meta(get_the_ID(), 'field_type', true);
                    array_push($_array, $_list);
                endwhile;
                wp_reset_postdata();
            }    
            return $_array;
        }

        function display_doc_field_contains($args) {

            $doc_id = isset($args['doc_id']) ? $args['doc_id'] : 0;
            $report_id = isset($args['report_id']) ? $args['report_id'] : 0;

            $params = array(
                'doc_id'     => $doc_id,
                'is_editing'  => true,
            );                
            $query = $this->retrieve_doc_field_data($params);

            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                    $field_title = get_post_meta(get_the_ID(), 'field_title', true);
                    $field_type = get_post_meta(get_the_ID(), 'field_type', true);
                    $default_value = get_post_meta(get_the_ID(), 'default_value', true);

                    if ($report_id) {
                        $field_value = get_post_meta($report_id, $field_name, true);
                    } else {
                        $default_value = get_post_meta(get_the_ID(), 'default_value', true);
                        if ($default_value=='today') $default_value=wp_date('Y-m-d', time());

                        if (substr($default_value, 0, strlen('thermometer')) == 'thermometer') {
                            // Use a regular expression to match the number inside the parentheses
                            if (preg_match('/-(\d+)$/', $default_value, $matches)) {
                                $topic = $matches[1]; // Extract the number from the first capturing group
                                $default_value = get_option($topic);
                                // Find the post by title
                                $post = get_page_by_title($topic, OBJECT, 'http-client');
                                $default_value = get_post_meta($post->ID, 'temperature', true);
                            }
                        }

                        if (substr($default_value, 0, strlen('hygrometer')) == 'hygrometer') {
                            // Use a regular expression to match the number inside the parentheses
                            if (preg_match('/-(\d+)$/', $default_value, $matches)) {
                                $topic = $matches[1]; // Extract the number from the first capturing group
                                $default_value = get_option($topic);
                                // Find the post by title
                                $post = get_page_by_title($topic, OBJECT, 'http-client');
                                $default_value = get_post_meta($post->ID, 'humidity', true);
                            }
                        }

                        $field_value = $default_value;
                    }

                    switch (true) {
                        case ($field_type=='video'):
                            echo '<label class="video-button button" for="'.esc_attr($field_name).'">'.esc_html($field_title).'</label>';
                            $field_value = ($field_value) ? $field_value : get_option('default_video_url');
                            echo '<div style="display:flex;" class="video-display" id="'.esc_attr($field_name.'_video').'">'.$field_value.'</div>';
                            echo '<textarea class="video-url" id="'.esc_attr($field_name).'" rows="3" style="width:100%; display:none;" >'.esc_html($field_value).'</textarea>';
                            break;
        
                        case ($field_type=='image'):
                            echo '<label class="image-button button" for="'.esc_attr($field_name).'">'.esc_html($field_title).'</label>';
                            $field_value = ($field_value) ? $field_value : get_option('default_image_url');
                            echo '<img style="width:100%;" class="image-display" src="'.$field_value.'" />';
                            echo '<textarea class="image-url" id="'.esc_attr($field_name).'" rows="3" style="width:100%; display:none;" >'.esc_html($field_value).'</textarea>';
                            break;

                        case ($field_type=='_document'):
                            ?>
                            <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_name);?>" class="text ui-widget-content ui-corner-all"><?php echo $this->select_document_list_options($field_value);?></select>
                            <?php
                            break;

                        case ($field_type=='_customer'):
                            $cards_class = new erp_cards();
                            ?>
                            <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_name);?>" class="text ui-widget-content ui-corner-all"><?php echo $cards_class->select_customer_card_options($field_value);?></select>
                            <?php
                            break;

                        case ($field_type=='_vendor'):
                            $cards_class = new erp_cards();
                            ?>
                            <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_name);?>" class="text ui-widget-content ui-corner-all"><?php echo $cards_class->select_vendor_card_options($field_value);?></select>
                            <?php
                            break;

                        case ($field_type=='_product'):
                            $cards_class = new erp_cards();
                            ?>
                            <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_name);?>" class="text ui-widget-content ui-corner-all"><?php echo $cards_class->select_product_card_options($field_value);?></select>
                            <?php
                            break;

                        case ($field_type=='_equipment'):
                            $cards_class = new erp_cards();
                            ?>
                            <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_name);?>" class="text ui-widget-content ui-corner-all"><?php echo $cards_class->select_equipment_card_options($field_value);?></select>
                            <?php
                            break;

                        case ($field_type=='_instrument'):
                            $cards_class = new erp_cards();
                            ?>
                            <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_name);?>" class="text ui-widget-content ui-corner-all"><?php echo $cards_class->select_instrument_card_options($field_value);?></select>
                            <?php
                            break;


                        case ($field_type=='_department'):
                            $cards_class = new erp_cards();
                            ?>
                            <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_name);?>" class="text ui-widget-content ui-corner-all"><?php echo $cards_class->select_department_card_options($field_value);?></select>
                            <?php
                            break;

                        case ($field_type=='_employee'):
                            $cards_class = new erp_cards();
                            ?>
                            <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_name);?>" class="text ui-widget-content ui-corner-all"><?php echo $cards_class->select_employee_card_options($field_value);?></select>
                            <?php
                            break;
            
                        case ($field_type=='heading'):
                            $default_value = ($default_value) ? $default_value : 'b';
                            ?>
                            <div><<?php echo esc_html($default_value);?>><?php echo esc_html($field_title);?></<?php echo esc_html($default_value);?>></div>
                            <?php
                            break;
        
                        case ($field_type=='textarea'):
                            ?>
                            <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                            <textarea id="<?php echo esc_attr($field_name);?>" rows="3" style="width:100%;"><?php echo esc_html($field_value);?></textarea>
                            <?php    
                            break;
        
                        case ($field_type=='checkbox'):
                            $is_checked = ($field_value==1) ? 'checked' : '';
                            ?>
                            <input type="checkbox" id="<?php echo esc_attr($field_name);?>" <?php echo $is_checked;?> />
                            <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label><br>
                            <?php
                            break;
            
                        case ($field_type=='radio'):
                            $is_checked = ($field_value==1) ? 'checked' : '';
                            ?>
                            <input type="radio" id="<?php echo esc_attr($field_name);?>" name="<?php echo esc_attr(substr($field_name, 0, 5));?>" <?php echo $is_checked;?> />
                            <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label><br>
                            <?php
                            break;
            
                        case ($field_type=='date'):
                            ?>
                            <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                            <input type="date" id="<?php echo esc_attr($field_name);?>" value="<?php echo esc_html($field_value);?>" class="text ui-widget-content ui-corner-all" />
                            <?php
                            break;
            
                        case ($field_type=='time'):
                            ?>
                            <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                            <input type="time" id="<?php echo esc_attr($field_name);?>" value="<?php echo esc_html($field_value);?>" class="text ui-widget-content ui-corner-all" />
                            <?php
                            break;
                
                        case ($field_type=='number'):
                            ?>
                            <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                            <input type="number" id="<?php echo esc_attr($field_name);?>" value="<?php echo esc_html($field_value);?>" class="text ui-widget-content ui-corner-all" />
                            <?php
                            break;
            
                        default:
                            ?>
                            <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                            <input type="text" id="<?php echo esc_attr($field_name);?>" value="<?php echo esc_html($field_value);?>" class="text ui-widget-content ui-corner-all" />
                            <?php
                            break;
                    }
                endwhile;
                wp_reset_postdata();
            }
        }
        
        // document misc
        function select_document_list_options($selected_option=0) {
            $query = $this->retrieve_document_list_data();
            $options = '<option value="">Select document</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $doc_title = get_post_meta(get_the_ID(), 'doc_title', true);
                $doc_number = get_post_meta(get_the_ID(), 'doc_number', true);
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html($doc_title.'('.$doc_number.')') . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        function set_new_site_by_title() {
            $response = array('success' => false, 'error' => 'Invalid data format');
            if (isset($_POST['_new_site_title'])) {
                // Sanitize input values
                $new_site_title = sanitize_text_field($_POST['_new_site_title']);
                
                // Check if a site with the same title already exists
                $existing_site = get_page_by_title($new_site_title, OBJECT, 'site');
                
                if ($existing_site) {
                    // A site with the same title already exists
                    $response['error'] = 'A site with the same title already exists.';
                } else {
                    // Insert the new site
                    $current_user_id = get_current_user_id();
                    $new_site_args = array(
                        'post_title'    => $new_site_title,
                        'post_status'   => 'publish',
                        'post_author'   => $current_user_id,
                        'post_type'     => 'site',
                    );
                    $new_site_id = wp_insert_post($new_site_args);
                    
                    if (is_wp_error($new_site_id)) {
                        // Error occurred while inserting the site
                        $response['error'] = $new_site_id->get_error_message();
                    } else {
                        // Successfully created a new site
                        $response['new_site_id'] = $new_site_id;
                        $response['success'] = 'Completed to create a new site';
                    }
                }
            }
            wp_send_json($response);
        }
        
        function count_doc_category($doc_category){
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
        
            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'doc_category',
                        'value'   => $doc_category,
                        'compare' => '=',
                    ),
                ),
            );    
            $query = new WP_Query($args);    
            $total_posts = $query->found_posts;
            return $total_posts;
        }
        
        function display_iso_document_statement($doc_number){
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();
            if ($is_site_admin) {
                $args = array(
                    'post_type'      => 'document',
                    'posts_per_page' => 1,
                    'meta_query'     => array(
                        array(
                            'key'     => 'doc_number',
                            'value'   => sanitize_text_field($doc_number),
                            'compare' => '='
                        ),
                    ),
                );
                $query = new WP_Query($args);
                $doc_id = null; // Initialize the variable to avoid potential undefined variable errors
    
                if ($query->have_posts()) {
                    $doc_id = $query->posts[0]->ID; // Get the ID of the first (and only) post
                    wp_reset_postdata();
                }
    
                $doc_title = get_post_meta($doc_id, 'doc_title', true);
                $doc_number = get_post_meta($doc_id, 'doc_number', true);
                $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
                $category_id = get_post_meta($doc_id, 'doc_category', true);
                $doc_category = get_the_title( $category_id );
                $count_category = $this->count_doc_category($category_id);
                ob_start();
                ?>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <?php echo display_iso_helper_logo();?>
                        <span><?php echo esc_html($doc_number);?></span>
                        <h2 style="display:inline;"><?php echo esc_html($doc_title);?></h2>
                        <span><?php echo esc_html($doc_revision);?></span>            
                    </div>
                </div>
            
                <input type="hidden" id="doc-id" value="<?php echo esc_attr($doc_id);?>" />
                <input type="hidden" id="doc-category" value="<?php echo esc_attr($doc_category);?>" />
                <input type="hidden" id="doc-category-id" value="<?php echo esc_attr($category_id);?>" />
                <input type="hidden" id="doc-site-id" value="<?php echo esc_attr($doc_site);?>" />
                <input type="hidden" id="count-category" value="<?php echo esc_attr($count_category);?>" />
                <input type="hidden" id="site-id" value="<?php echo esc_attr($site_id);?>" />
            
                <fieldset>
                    <?php
                    $args = array(
                        'post_type'      => 'doc-report',
                        'posts_per_page' => -1,
                        'meta_query'     => array(
                            array(
                                'key'     => 'doc_id',
                                'value'   => $doc_id,
                                'compare' => '='
                            ),
                        ),
                        'orderby'    => 'meta_value',
                        'meta_key'   => 'index',
                        'order'      => 'ASC',
                    );
                    $query = new WP_Query($args);
                            
                    if ($query->have_posts()) {
                        while ($query->have_posts()) : $query->the_post();
                            $report_id = get_the_ID();
                            $index = get_post_meta($report_id, 'index', true);
                            $description = get_post_meta($report_id, 'description', true);
                            $is_checkbox = get_post_meta($report_id, 'is_checkbox', true);
                            $is_url = get_post_meta($report_id, 'is_url', true);
                            $is_bold = get_post_meta($report_id, 'is_bold', true);
                            if ($is_checkbox==1) echo '<input type="checkbox" id="'.$index.'" checked /> 適用';
                            if ($is_url) {
                                echo '<span class="is-url">：<a href="'.$is_url.'">'.$description.'</a></span><br>';
                            } else {
                                if ($is_bold==1) echo '<b>';
                                echo $description;
                                if ($is_bold==1) echo '</b>';
                                echo '<br>';
                            }
                        endwhile;                
                        wp_reset_postdata();
                    }
                    ?>
                </fieldset>
                <button id="initial-next-step" class="button" style="margin:5px;"><?php echo __( '下ㄧ步(Next)', 'your-text-domain' );?></button>
                <?php
                return ob_get_clean();
    
            } else {
                return 'You are not site administrator! Apply to existing administrator for the rights. <button id="apply-site-admin">Apply</button>';
            }

        }
        
        function set_initial_iso_document() {
            $response = array('success' => false, 'error' => 'Invalid data format');
        
            if (isset($_POST['_doc_category_id'])) {
                $doc_category = sanitize_text_field($_POST['_doc_category_id']);
                $args = array(
                    'post_type'      => 'document',
                    'posts_per_page' => -1,
                    'meta_query'     => array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'doc_category',
                            'value'   => $doc_category,
                            'compare' => '=',
                        ),
                    ),
                );
                
                $query = new WP_Query($args);
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                    $this->get_shared_document(get_the_ID());
                    endwhile;
                    wp_reset_postdata();
                    $response = array('success' => true);
                endif;
            }
            wp_send_json($response);
        }
        
        function get_shared_document($doc_id){
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $job_number = get_post_meta($doc_id, 'job_number', true);
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
            $doc_category = get_post_meta($doc_id, 'doc_category', true);
            $doc_frame = get_post_meta($doc_id, 'doc_frame', true);
            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
            // Create the post
            $new_post = array(
                'post_title'    => get_the_title($doc_id),
                'post_content'  => get_post_field('post_content', $doc_id),
                'post_status'   => 'publish',
                'post_author'   => $current_user_id,
                'post_type'     => 'document',
            );    
            $post_id = wp_insert_post($new_post);
        
            update_post_meta( $post_id, 'site_id', $site_id);
            update_post_meta( $post_id, 'job_number', $job_number);
            update_post_meta( $post_id, 'doc_title', $doc_title);
            update_post_meta( $post_id, 'doc_number', $doc_number);
            update_post_meta( $post_id, 'doc_revision', $doc_revision);
            update_post_meta( $post_id, 'doc_category', $doc_category);
            update_post_meta( $post_id, 'doc_frame', $doc_frame);
            update_post_meta( $post_id, 'is_doc_report', $is_doc_report);
        
            // Create the Action list for $post_id
            $profiles_class = new display_profiles();
            $query = $profiles_class->retrieve_doc_action_list_data($doc_id);
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $new_post = array(
                        'post_title'    => get_the_title($doc_id),
                        'post_content'  => get_post_field('post_content', $doc_id),
                        'post_status'   => 'publish',
                        'post_author'   => $current_user_id,
                        'post_type'     => 'action',
                    );    
                    $new_action_id = wp_insert_post($new_post);
                    $new_next_job = get_post_meta(get_the_ID(), 'next_job', true);
                    $new_next_leadtime = get_post_meta(get_the_ID(), 'next_leadtime', true);
                    update_post_meta( $new_action_id, 'doc_id', $post_id);
                    update_post_meta( $new_action_id, 'next_job', $new_next_job);
                    update_post_meta( $new_action_id, 'next_leadtime', $new_next_leadtime);
                endwhile;
                wp_reset_postdata();
            }

            if ($is_doc_report==1){
                $params = array(
                    'doc_id'     => $doc_id,
                );                
                $query = $this->retrieve_doc_field_data($params);
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                        $field_title = get_post_meta(get_the_ID(), 'field_title', true);
                        $field_type = get_post_meta(get_the_ID(), 'field_type', true);
                        $default_value = get_post_meta(get_the_ID(), 'default_value', true);
                        $listing_style = get_post_meta(get_the_ID(), 'listing_style', true);
                        $sorting_key = get_post_meta(get_the_ID(), 'sorting_key', true);
                        $new_post = array(
                            'post_status'   => 'publish',
                            'post_author'   => $current_user_id,
                            'post_type'     => 'doc-field',
                        );    
                        $field_id = wp_insert_post($new_post);
                        update_post_meta( $field_id, 'doc_id', $post_id);
                        update_post_meta( $field_id, 'field_name', $field_name);
                        update_post_meta( $field_id, 'field_title', $field_title);
                        update_post_meta( $field_id, 'field_type', $field_type);
                        update_post_meta( $field_id, 'default_value', $default_value);
                        update_post_meta( $field_id, 'listing_style', $listing_style);
                        update_post_meta( $field_id, 'sorting_key', $sorting_key);
                    endwhile;
                    wp_reset_postdata();
                }    
            }
        }
        
        function set_todo_from_doc_report($action_id=false, $report_id=false) {
        
            $current_user_id = get_current_user_id();
            $doc_id = get_post_meta($report_id, 'doc_id', true);
            $todo_title = get_the_title($doc_id);
        
            // Create the new To-do
            $new_post = array(
                'post_title'    => $todo_title,
                'post_status'   => 'publish',
                'post_author'   => $current_user_id,
                'post_type'     => 'todo',
            );    
            $todo_id = wp_insert_post($new_post);    
        
            update_post_meta( $todo_id, 'report_id', $report_id);
            update_post_meta( $todo_id, 'submit_user', $current_user_id);
            update_post_meta( $todo_id, 'submit_action', $action_id);
            update_post_meta( $todo_id, 'submit_time', time());
        
            $next_job = get_post_meta($action_id, 'next_job', true);
            update_post_meta( $report_id, 'todo_status', $next_job);
            update_post_meta( $doc_id, 'todo_status', -1);
        
            // set next todo and actions
            $params = array(
                'next_job' => $next_job,
                'prev_report_id' => $report_id,
            );        
            $todo_class = new to_do_list();
            if ($next_job>0) $todo_class->update_next_todo_and_actions($params);
        }
        
        function reset_document_todo_status() {
            $response = array();
            if( isset($_POST['_report_id']) ) {
                $report_id = sanitize_text_field($_POST['_report_id']);
                delete_post_meta($report_id, 'todo_status');
            }
            if( isset($_POST['_doc_id']) ) {
                $doc_id = sanitize_text_field($_POST['_doc_id']);
                delete_post_meta($doc_id, 'todo_status');
                delete_post_meta($doc_id, 'due_date');
                delete_post_meta($doc_id, 'start_job');
            }
            wp_send_json($response);
        }
                
    }
    $documents_class = new display_documents();
}

