<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('display_documents')) {
    class display_documents {
        // Class constructor
        public function __construct() {
            add_shortcode( 'display-documents', array( $this, 'display_documents'  ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_display_document_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this,'add_mermaid_script' ) );
            add_action( 'init', array( $this, 'register_document_post_type' ) );
            add_action( 'add_meta_boxes', array( $this, 'add_document_settings_metabox' ) );
            add_action( 'init', array( $this, 'register_doc_report_post_type' ) );
            //add_action( 'init', array( $this, 'register_doc_field_post_type' ) );

            add_action( 'wp_ajax_set_document_dialog_data', array( $this, 'set_document_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_document_dialog_data', array( $this, 'set_document_dialog_data' ) );
            add_action( 'wp_ajax_del_document_dialog_data', array( $this, 'del_document_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_document_dialog_data', array( $this, 'del_document_dialog_data' ) );

            add_action( 'wp_ajax_get_doc_content_data', array( $this, 'get_doc_content_data' ) );
            add_action( 'wp_ajax_nopriv_get_doc_content_data', array( $this, 'get_doc_content_data' ) );

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

            add_action( 'wp_ajax_reset_doc_report_todo_status', array( $this, 'reset_doc_report_todo_status' ) );
            add_action( 'wp_ajax_nopriv_reset_doc_report_todo_status', array( $this, 'reset_doc_report_todo_status' ) );                                                                    

            add_action( 'wp_ajax_set_iso_statement_data', array( $this, 'set_iso_statement_data' ) );
            add_action( 'wp_ajax_nopriv_set_iso_statement_data', array( $this, 'set_iso_statement_data' ) );
        }

        function enqueue_display_document_scripts() {
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);        

            wp_enqueue_script('display-documents', plugins_url('js/display-documents.js', __FILE__), array('jquery'), time());
            wp_localize_script('display-documents', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('display-documents-nonce'), // Generate nonce
            ));                
        }

        function add_mermaid_script() {
            // Add Mermaid script
            wp_enqueue_script('mermaid', 'https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.min.js', array(), null, true);
        }

        // Shortcode to display
        function display_documents() {
            // Check if the user is logged in
            if (!is_user_logged_in()) user_is_not_logged_in();
            elseif (is_site_not_configured()) display_NDA_assignment();
            else {
                // Display document list if no specific parameters are existed
                if (($_GET['_category']!='embedded') && !isset($_GET['_doc_id']) && !isset($_GET['_duplicate_document']) && !isset($_GET['_statement'])) {
                    echo $this->display_document_list();
                }

                $items_class = new embedded_items();
                if ($_GET['_category']=='embedded') {
                    if (isset($_GET['_embedded_id'])) echo $items_class->display_embedded_dialog($_GET['_embedded_id']);
                    else echo $items_class->display_embedded_list();
                }
                
                // Display ISO statement
                if (isset($_GET['_statement'])) {
                    $iso_category_id = sanitize_text_field($_GET['_statement']);
                    $paged = 1;
                    if (isset($_GET['_paged'])) {
                        $paged = sanitize_text_field($_GET['_paged']);
                    }
                    echo $this->display_iso_statement_content($iso_category_id, $paged);
                }

                // Display document dialog if doc_id is existed
                if (isset($_GET['_doc_id'])) {
                    $doc_id = sanitize_text_field($_GET['_doc_id']);
                    $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
                    if (isset($_GET['_report_id'])) {
                        $report_id = sanitize_text_field($_GET['_report_id']);
                        echo $this->display_doc_report_dialog($report_id);
                    } else {
                        if (is_site_admin()) echo $this->display_document_dialog($doc_id);
                        else {
                            if ($is_doc_report==1) echo $this->display_doc_report_list(array('doc_id' => $doc_id));
                            else echo $this->display_doc_content($doc_id);
                        }
                    }
                }

                // Get shared document if shared doc ID is existed
                if (isset($_GET['_duplicate_document'])) {
                    $doc_id = sanitize_text_field($_GET['_duplicate_document']);
                    $this->generate_draft_document_data($doc_id);
                }

            }
        }

        // document post type
        function register_document_post_type() {
            $labels = array(
                'menu_name'     => _x('Documents', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
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

        function display_document_list() {
            if (isset($_GET['_is_admin'])) {
                echo '<input type="hidden" id="is-admin" value="1" />';
            }
            $doc_category = (isset($_GET['_category'])) ? sanitize_text_field($_GET['_category']) : 0;
            $items_class = new embedded_items();
            ?>
            <div class="ui-widget" id="result-container">
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( '文件總覽', 'your-text-domain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <select id="select-category"><?php echo $items_class->select_doc_category_options($doc_category);?></select>
                    </div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-document" style="display:inline" placeholder="Search..." />
                        <span id="document-setting-button" style="margin-left:5px;" class="dashicons dashicons-admin-generic button"></span>
                    </div>
                </div>

            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( '文件編號', 'your-text-domain' );?></th>
                            <th><?php echo __( '文件名稱', 'your-text-domain' );?></th>
                            <th><?php echo __( '文件版本', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_document_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts'));

                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $doc_number = get_post_meta(get_the_ID(), 'doc_number', true);
                            $doc_title = get_post_meta(get_the_ID(), 'doc_title', true);
                            $doc_revision = get_post_meta(get_the_ID(), 'doc_revision', true);
                            $is_doc_report = get_post_meta(get_the_ID(), 'is_doc_report', true);

                            if ($is_doc_report == 1) {
                                $doc_title = '<span style="color:blue;">*' . $doc_title . '</span>';
                            }

                            ?>
                            <tr id="edit-document-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html($doc_number);?></td>
                                <td><?php echo $doc_title;?></td>
                                <td style="text-align:center;"><?php echo esc_html($doc_revision);?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <?php if (is_site_admin()) {?>
                    <div id="new-document" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
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
            </div>
            <?php
        }
        
        function retrieve_document_data($paged=1, $is_doc_report=2) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $site_filter = array(
                'key'     => 'site_id',
                'value'   => $site_id,
                'compare' => '=',
            );

            $select_category = (isset($_GET['_category'])) ? sanitize_text_field($_GET['_category']) : 0;
            $category_filter = array(
                'key'     => 'doc_category',
                'value'   => $select_category,
                'compare' => '=',
            );

            $search_query = (isset($_GET['_search'])) ? sanitize_text_field($_GET['_search']) : false;
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
                    'relation' => 'AND',
                    ($site_id) ? $site_filter : '',
                    ($select_category) ? $category_filter : '',
                    array(
                        'relation' => 'OR',
                        ($search_query) ? $number_filter : '',
                        ($search_query) ? $title_filter : '',
                    ),
                ),
                'orderby'        => 'meta_value',
                'meta_key'       => 'doc_number',
                'order'          => 'ASC',
            );

            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }

            if ($is_doc_report == 0) {
                $args['meta_query'][] = array(
                    'key'     => 'is_doc_report',
                    'value'   => 0,
                    'compare' => '=',    
                    'type'    => 'NUMERIC'
                );
            }

            if ($is_doc_report == 1) {
                $args['meta_query'][] = array(
                    'key'     => 'is_doc_report',
                    'value'   => 1,
                    'compare' => '=',    
                    'type'    => 'NUMERIC'
                );
            }

            $query = new WP_Query($args);
            return $query;
        }
        
        function get_previous_doc_id($current_doc_id) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            // Get the current document's `doc_number`
            $current_doc_number = get_post_meta($current_doc_id, 'doc_number', true);
        
            if (!$current_doc_number) {
                return null; // Return null if the current doc_number is not set
            }
        
            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => 1,
                'meta_key'       => 'doc_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value as a string
                'order'          => 'DESC', // Descending order to get the previous document
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                        'compare' => '=',    
                    ),
                    array(
                        'key'     => 'doc_number',
                        'value'   => $current_doc_number,
                        'compare' => '<', // Find `doc_number` less than the current one
                        'type'    => 'CHAR', // Treat `doc_number` as a string
                    ),
                ),
            );
        
            $query = new WP_Query($args);
        
            // Return the previous document ID or null if no previous document is found
            return $query->have_posts() ? $query->posts[0]->ID : null;
        }

        function get_next_doc_id($current_doc_id) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            // Get the current document's `doc_number`
            $current_doc_number = get_post_meta($current_doc_id, 'doc_number', true);
        
            if (!$current_doc_number) {
                return null; // Return null if the current doc_number is not set
            }
        
            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => 1,
                'meta_key'       => 'doc_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value as a string
                'order'          => 'ASC', // Ascending order to get the next document
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                        'compare' => '=',    
                    ),
                    array(
                        'key'     => 'doc_number',
                        'value'   => $current_doc_number,
                        'compare' => '>', // Find `doc_number` greater than the current one
                        'type'    => 'CHAR', // Treat `doc_number` as a string
                    ),
                ),
            );
        
            $query = new WP_Query($args);
        
            // Return the next document ID or null if no next document is found
            return $query->have_posts() ? $query->posts[0]->ID : null;
        }

        function display_document_dialog($doc_id=false) {
            ob_start();
            $prev_doc_id = $this->get_previous_doc_id($doc_id); // Fetch the previous ID
            $next_doc_id = $this->get_next_doc_id($doc_id);     // Fetch the next ID
            ?>
            <input type="hidden" id="prev-doc-id" value="<?php echo esc_attr($prev_doc_id); ?>" />
            <input type="hidden" id="next-doc-id" value="<?php echo esc_attr($next_doc_id); ?>" />
            <?php
            $todo_class = new to_do_list();
            $items_class = new embedded_items();
            $profiles_class = new display_profiles();

            $job_title = get_the_title($doc_id);
            $job_number = get_post_meta($doc_id, 'job_number', true);
            $department = get_post_meta($doc_id, 'department', true);
            $department_id = get_post_meta($doc_id, 'department_id', true);

            $doc_content = get_post_field('post_content', $doc_id);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
            $doc_category = get_post_meta($doc_id, 'doc_category', true);
            $doc_frame = get_post_meta($doc_id, 'doc_frame', true);
            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
            $is_report_display = ($is_doc_report==1) ? '' : 'display:none;';
            $is_content_display = ($is_doc_report==1) ? 'display:none;' : '';
            $system_doc = get_post_meta($doc_id, 'system_doc', true);

            $doc_report_frequence_setting = get_post_meta($doc_id, 'doc_report_frequence_setting', true);
            $doc_report_frequence_start_time = get_post_meta($doc_id, 'doc_report_frequence_start_time', true);
            ?>
            <div class="ui-widget" id="result-container">
            <div>
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo esc_html($doc_title);?></h2>
                <input type="hidden" id="doc-id" value="<?php echo esc_attr($doc_id);?>" />
            </div>

            <fieldset>
                <label for="doc-number"><?php echo __( '文件編號', 'your-text-domain' );?></label>
                <input type="text" id="doc-number" value="<?php echo esc_html($doc_number);?>" class="text ui-widget-content ui-corner-all" />
                <label for="doc-title"><?php echo __( '文件名稱', 'your-text-domain' );?></label>
                <input type="text" id="doc-title" value="<?php echo esc_html($doc_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="doc-revision"><?php echo __( '文件版本', 'your-text-domain' );?></label>
                <input type="text" id="doc-revision" value="<?php echo esc_html($doc_revision);?>" class="text ui-widget-content ui-corner-all" />
                <label for="doc-category"><?php echo __( '文件類別', 'your-text-domain' );?></label><br>
                <select id="doc-category" class="text ui-widget-content ui-corner-all"><?php echo $items_class->select_doc_category_options($doc_category);?></select>

                <input type="hidden" id="is-doc-report" value="<?php echo $is_doc_report;?>" />

                <div id="doc-content-div" style="<?php echo $is_content_display;?>">
                    <label id="doc-content-label" class="button" for="doc-content"><?php echo __( '文件內容', 'your-text-domain' );?></label>
                    <?php if (is_site_admin()) {?>
                        <input type="button" id="doc-content-preview" value="<?php echo __( 'Preview', 'your-text-domain' );?>" style="margin:3px;font-size:small;" />
                    <?php }?>
                    <textarea id="doc-content" class="visual-editor"><?php echo $doc_content;?></textarea>
                </div>

                <div id="doc-report-div" style="<?php echo $is_report_display;?>">
                    <label id="doc-field-label" class="button" for="doc-field"><?php echo __( '欄位設定', 'your-text-domain' );?></label>
                    <?php if (is_site_admin()) {?>
                        <input type="button" id="doc-report-preview" value="<?php echo __( 'Preview', 'your-text-domain' );?>" style="margin:3px;font-size:small;" />
                    <?php }?>
                    <?php echo $this->display_doc_field_list($doc_id);?>
                    <label id="doc-report-job-setting" class="button"><?php echo __( '職務設定', 'your-text-domain' );?></label>
                
                    <div id="mermaid-div">
                        <pre class="mermaid">
                            graph TD 
                            <?php                        
                            $query = $profiles_class->retrieve_doc_action_data($doc_id, true);
                            if ($query->have_posts()) :
                                while ($query->have_posts()) : $query->the_post();
                                    $action_title = get_the_title();
                                    $action_content = get_post_field('post_content', get_the_ID());
                                    $current_job = get_post_meta(get_the_ID(), 'doc_id', true);
                                    $current_job_title = get_the_title($current_job);
                                    $next_job = get_post_meta(get_the_ID(), 'next_job', true);
                                    $next_job_title = get_the_title($next_job);
                                    $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
                                    if ($next_job==-1) $next_job_title = __( '發行', 'your-text-domain' );
                                    if ($next_job==-2) $next_job_title = __( '廢止', 'your-text-domain' );
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
                        <label for="job-number"><?php echo __( '職務編號', 'your-text-domain' );?></label>
                        <input type="text" id="job-number" value="<?php echo esc_html($job_number);?>" class="text ui-widget-content ui-corner-all" />
                        <label for="job-title"><?php echo __( '職務名稱', 'your-text-domain' );?></label>
                        <input type="text" id="job-title" value="<?php echo esc_html($job_title);?>" class="text ui-widget-content ui-corner-all" />
                        <label for="job-content"><?php echo __( '職務說明', 'your-text-domain' );?></label>
                        <textarea id="job-content" class="visual-editor"><?php echo $doc_content;?></textarea>
                        <label for="action-list"><?php echo __( '按鍵設定', 'your-text-domain' );?></label>
                        <?php echo $profiles_class->display_doc_action_list($doc_id);?>
                        <label for="department"><?php echo __( '部門', 'your-text-domain' );?></label>
                        <select id="department-id" class="text ui-widget-content ui-corner-all"><?php echo $items_class->select_department_card_options($department_id);?></select>
                        <label for="user-list"><?php echo __( 'User list:', 'your-text-domain' );?></label>
                        <?php echo $profiles_class->display_doc_user_list($doc_id);?>
                    </div>

                    <label for="system-doc"><?php echo __( '系統文件', 'your-text-domain' );?></label>
                    <input type="text" id="system-doc" value="<?php echo esc_html($system_doc);?>" class="text ui-widget-content ui-corner-all" />
                </div>

                <?php
                    // transaction data vs card key/value
                    $this->get_transactions_by_key_value_pair(array('_document' => $doc_id));

                    $content = (isset($_GET['_prompt'])) ? generate_content($doc_title.' '.$_GET['_prompt']) : '';
                    ?>
                    <div class="content">
                        <?php echo $content;?>
                        <div style="margin:1em; padding:10px; border:solid; border-radius:1.5rem;">
                            <input type="text" id="ask-gemini" placeholder="問問 Gemini" class="text ui-widget-content ui-corner-all" />
                        </div>
                    </div>            

                <hr>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <?php if (is_site_admin()) {?>
                            <input type="button" id="save-document-button" value="<?php echo __( 'Save', 'your-text-domain' );?>" style="margin:3px;" />
                            <input type="button" id="del-document-button" value="<?php echo __( 'Delete', 'your-text-domain' );?>" style="margin:3px;" />
                        <?php }?>
                    </div>
                    <div style="text-align: right">
                        <input type="button" id="exit-document-dialog" value="Exit" style="margin:5px;" />
                    </div>
                </div>
            </fieldset>
            </div>
            <?php
            return ob_get_clean();
        }

        function set_document_dialog_data() {
            $response = array();
            if( isset($_POST['_doc_id']) ) {
                // Update the Document data
                $doc_id = (isset($_POST['_doc_id'])) ? sanitize_text_field($_POST['_doc_id']) : 0;
                $doc_number = (isset($_POST['_doc_number'])) ? sanitize_text_field($_POST['_doc_number']) : '';
                $doc_title = (isset($_POST['_doc_title'])) ? sanitize_text_field($_POST['_doc_title']) : '';
                $doc_revision = (isset($_POST['_doc_revision'])) ? sanitize_text_field($_POST['_doc_revision']) : '';
                $doc_category = (isset($_POST['_doc_category'])) ? sanitize_text_field($_POST['_doc_category']) : 0;
                $job_number = (isset($_POST['_job_number'])) ? sanitize_text_field($_POST['_job_number']) : '';
                $job_title = (isset($_POST['_job_title'])) ? sanitize_text_field($_POST['_job_title']) : '';
                $department_id = (isset($_POST['_department_id'])) ? sanitize_text_field($_POST['_department_id']) : 0;
                $is_doc_report = (isset($_POST['_is_doc_report'])) ? sanitize_text_field($_POST['_is_doc_report']) : 0;
                $system_doc = (isset($_POST['_system_doc'])) ? sanitize_text_field($_POST['_system_doc']) : '';
                $doc_content = ($is_doc_report==1) ? $_POST['_job_content'] : $_POST['_doc_content'];
                $doc_post_args = array(
                    'ID'           => $doc_id,
                    'post_title'   => $job_title,
                    'post_content' => $doc_content,
                );
                wp_update_post($doc_post_args);
                if ($job_number) update_post_meta($doc_id, 'job_number', $job_number);
                else update_post_meta($doc_id, 'job_number', $doc_number);
                update_post_meta($doc_id, 'department_id', $department_id);
                update_post_meta($doc_id, 'doc_number', $doc_number);
                update_post_meta($doc_id, 'doc_title', $doc_title);
                update_post_meta($doc_id, 'doc_revision', $doc_revision);
                update_post_meta($doc_id, 'doc_category', $doc_category);
                update_post_meta($doc_id, 'is_doc_report', $is_doc_report);
                update_post_meta($doc_id, 'system_doc', $system_doc);

                $params = array(
                    'log_message' => $doc_title.'(#'.$doc_number.') has been updated',
                );
                $todo_class = new to_do_list();
                $todo_class->create_action_log_and_go_next($params);    

            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_type'     => 'document',
                    'post_title'    => 'New job',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'doc_number', '-');
                update_post_meta($post_id, 'doc_revision', 'draft');
                update_post_meta($post_id, 'is_doc_report', 0);
                $response['html_contain'] = $this->display_document_dialog($post_id);
            }
            wp_send_json($response);
        }
        
        function del_document_dialog_data() {
            $response = array();
            $doc_id = (isset($_POST['_doc_id'])) ? sanitize_text_field($_POST['_doc_id']) : 0;
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $params = array(
                'log_message' => $doc_title.'(#'.$doc_number.') has been deleted',
            );
            $todo_class = new to_do_list();
            $todo_class->create_action_log_and_go_next($params);    

            wp_delete_post($_POST['_doc_id'], true);
            wp_send_json($response);
        }

        // doc-content
        function display_doc_content($doc_id=false) {
            ob_start();
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
            $doc_content = get_post_field('post_content', $doc_id);
            ?>
            <div class="ui-widget" id="result-container">
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <?php echo display_iso_helper_logo();?>
                    <span><?php echo esc_html($doc_number);?></span>
                    <h2 style="display:inline;"><?php echo esc_html($doc_title);?></h2>
                    <span><?php echo esc_html($doc_revision);?></span>
                </div>
                <div style="text-align:right; display:flex;">
                </div>
            </div>

            <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />

            <fieldset>
                <?php echo $doc_content;?>
            </fieldset>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <input type="button" id="share-document" value="<?php echo __( '文件分享', 'your-text-domain' );?>" style="margin:3px;" />
                </div>
                <div style="text-align:right; display:flex;">
                    <input type="button" id="exit-doc-content" value="<?php echo __( 'Exit', 'your-text-domain' );?>" style="margin:3px;" />
                </div>
            </div>
            </div>
            <?php
            return ob_get_clean();
        }

        function get_doc_content_data() {
            $response = array();
            if (isset($_POST['_doc_id'])) {
                $doc_id = sanitize_text_field($_POST['_doc_id']);
                $response['html_contain'] = $this->display_doc_content($doc_id);
            }
            wp_send_json($response);
        }

        // doc-report
        function register_doc_report_post_type() {
            $labels = array(
                'menu_name'     => _x('doc-report', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'doc-report', $args );
        }

        function display_doc_report_list($params) {
            ob_start();
            $profiles_class = new display_profiles();
            $doc_id = isset($params['doc_id']) ? $params['doc_id'] : 0;
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
            ?>
            <div class="ui-widget" id="result-container">
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <?php echo display_iso_helper_logo();?>
                    <span><?php echo esc_html($doc_number);?></span>
                    <h2 style="display:inline;"><?php echo esc_html($doc_title);?></h2>
                    <span><?php echo esc_html($doc_revision);?></span>            
                </div>
                <div style="text-align:right; display:flex;">
                </div>
            </div>
        
            <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />
            
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div></div>
                <div style="text-align:right; display:flex;">
                    <input type="text" id="search-doc-report" style="display:inline" placeholder="Search..." />
                    <span id="doc-field-setting-button" style="margin-left:5px;" class="dashicons dashicons-admin-generic button"></span>
                </div>
            </div>

            <div id="doc-field-setting-dialog" title="Field setting" style="display:none">
                <fieldset>
                    <label for="doc-field-setting"><?php echo __( 'Field setting:', 'your-text-domain' );?></label>
                    <?php echo $this->display_doc_field_list($doc_id);?>
                </fieldset>
            </div>        

            <fieldset>
                <?php
                $params = array(
                    'doc_id'     => $doc_id,
                    //'search_doc_report' => $search_doc_report,
                );
                $paged = max(1, get_query_var('paged')); // Get the current page number
                $params['paged'] = $paged;
                $query = $this->retrieve_doc_report_data($params);
                $total_posts = $query->found_posts;
                $total_pages = ceil($total_posts / get_option('operation_row_counts'));

                $this->get_doc_report_contain_list($params);
                ?>
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>
            </fieldset>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <?php if ($profiles_class->is_user_doc($doc_id)) {?>
                        <input type="button" id="export-to-excel" value="<?php echo __( 'Export to Excel', 'your-text-domain' );?>" style="margin:3px;" />
                    <?php }?>
                    <style>
                    /* Hide button on mobile devices */
                    @media screen and (max-width: 768px) {
                        #export-to-excel {
                            display: none;
                        }
                    }
                    </style>
                </div>
                <div style="text-align:right; display:flex;">
                    <input type="button" id="exit-doc-report-list" value="<?php echo __( 'Exit', 'your-text-domain' );?>" style="margin:3px;" />
                </div>
            </div>
            </div>
            <?php
            return ob_get_clean();
        }

        function get_doc_report_contain_list($params) {
            $params['is_listing'] = true;
            ?>
            <table style="width:100%;">
                <thead>
                    <?php
                    $query = $this->retrieve_doc_field_data($params);
                    if ($query->have_posts()) {
                        echo '<tr>';
                        while ($query->have_posts()) : $query->the_post();
                            $field_title = get_post_meta(get_the_ID(), 'field_title', true);
                            echo '<th>'.esc_html($field_title).'</th>';
                        endwhile;
                        if (current_user_can('administrator')) {
                            echo '<th>'. __( '待辦', 'your-text-domain' ).'</th>';
                        }
                        echo '</tr>';
                        wp_reset_postdata();
                    }
                    ?>
                </thead>
                <tbody>
                    <?php
                    $query = $this->retrieve_doc_report_data($params);
                    if ($query->have_posts()) {
                        while ($query->have_posts()) : $query->the_post();
                            $report_id = get_the_ID();
                            echo '<tr id="edit-doc-report-'.$report_id.'">';
                            $inner_query = $this->retrieve_doc_field_data($params);
                            if ($inner_query->have_posts()) {
                                while ($inner_query->have_posts()) : $inner_query->the_post();
                                    $field_type = get_post_meta(get_the_ID(), 'field_type', true);
                                    $listing_style = get_post_meta(get_the_ID(), 'listing_style', true);
                                    $field_value = get_post_meta($report_id, get_the_ID(), true);
                                    $is_checked = ($field_value==1) ? 'checked' : '';
                                    echo '<td style="text-align:'.$listing_style.';">';
                                    if ($field_type=='checkbox') {
                                        if ($field_value==1) echo 'V';
                                    } elseif ($field_type=='radio') {
                                        if ($field_value==1) echo 'V';
                                    } elseif ($field_type=='_employee'||$field_type=='_employees') {
                                        if (is_array($field_value)) {
                                            $user_names = array(); // Array to hold user display names
                                            // Loop through each selected user ID
                                            foreach ($field_value as $user_id) {
                                                // Get user data
                                                $user = get_userdata($user_id);
                                                // Check if the user data is retrieved successfully
                                                if ($user) {
                                                    // Add the user's display name to the array
                                                    $user_names[] = esc_html($user->display_name);
                                                } else {
                                                    // Optionally handle the case where user data is not found
                                                    $user_names[] = 'User not found for ID: ' . esc_html($user_id);
                                                }
                                            }
                                            // Display the user names as a comma-separated list
                                            echo implode(', ', $user_names);
                                        } else {
                                            // Handle the case where $field_value is not an array
                                            // Get user data
                                            $user = get_userdata($field_value);
                                            // Check if the user data is retrieved successfully
                                            if ($user) {
                                                // Add the user's display name to the array
                                                echo esc_html($user->display_name);
                                            } else {
                                                // Optionally handle the case where user data is not found
                                                echo 'User not found for ID: ' . esc_html($field_value);
                                            }
                                        }
                                    //} elseif ($field_type=='_embedded'||$field_type=='_planning'||$field_type=='_select') {
                                    } elseif ($field_type=='_embedded') {
                                        echo esc_html(get_the_title($field_value).'('.$field_value.')');
                                    } elseif ($field_type=='_document') {
                                        $doc_title = get_post_meta($field_value, 'doc_title', true);
                                        $doc_number = get_post_meta($field_value, 'doc_number', true);
                                        $doc_revision = get_post_meta($field_value, 'doc_revision', true);
                                        echo esc_html($doc_number.'-'.$doc_title.'-'.$doc_revision);
                                    } elseif ($field_type=='_department') {
                                        echo esc_html(get_the_title($field_value));
                                    } elseif ($field_type=='_iot_device') {
                                        $iot_device = get_post_meta($field_value, 'iot_device', true);
                                        echo esc_html(get_the_title($field_value));
                                    } else {
                                        $get_system_doc_id = $this->get_system_doc_id($field_type);
                                        if ($get_system_doc_id) {
                                            if ($field_value) echo esc_html(get_the_title($field_value));
                                        } else {
                                            echo esc_html($field_value);
                                        }
                                    }
                                    echo '</td>';
                                endwhile;                
                                wp_reset_postdata();
                            }
                            if (current_user_can('administrator')) {
                                $next_job = get_post_meta($report_id, 'todo_status', true);
                                $todo_status = ($next_job) ? get_the_title($next_job) : 'Draft';
                                $todo_status = ($next_job==-1) ? '發行' : $todo_status;
                                $todo_status = ($next_job==-2) ? '作廢' : $todo_status;
                                echo '<td style="text-align:center;">'.esc_html($todo_status).'</td>';
                            }
                            echo '</tr>';
                        endwhile;                
                        wp_reset_postdata();
                    }
                    ?>
                </tbody>
            </table>
            <?php
        }

        function retrieve_doc_report_data($params) {
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

            if (!empty($params['key_value_pair'])) {
                $meta_query[] = array(
                    'key'   => 'todo_status',
                    'value' => -1,
                );
            }

            $args = array(
                'post_type'      => 'doc-report',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => $meta_query,
                'orderby' => 'date',
                'order' => 'DESC',
            );

            if (!empty($params['todo_in_summary'])) {
                $todo_in_summary = $params['todo_in_summary'];
                $report_ids = array();
                foreach ($todo_in_summary as $todo_id) {
                    $report_id = get_post_meta($todo_id, 'prev_report_id', true);
                    $report_ids[] = $report_id;
                }
                $args['post__in'] = $report_ids;
            }

            $inner_query = $this->retrieve_doc_field_data(array('doc_id' => $doc_id));
            if ($inner_query->have_posts()) {
                while ($inner_query->have_posts()) : $inner_query->the_post();
                    $field_type = get_post_meta(get_the_ID(), 'field_type', true);

                    if (!empty($params['key_value_pair'])) {
                        $key_value_pair = $params['key_value_pair'];
                        foreach ($key_value_pair as $key => $value) {
                            if ($key==$field_type) {
                                if ($field_type=='_employees') {
                                    if (is_array($value)) {
                                        foreach ($value as $val) {
                                            $args['meta_query'][0][] = array(
                                                'key'     => get_the_ID(),
                                                'value'   => sprintf(':"%s";', (string)$val),
                                                'compare' => 'LIKE', // Use 'LIKE' to match any part of the serialized array
                                            );
                                        }
                                    } else {
                                        // If $value is not an array, treat it as a single value
                                        $args['meta_query'][0][] = array(
                                            'key'     => get_the_ID(),
                                            'value'   => sprintf(':"%s";', (string)$value),
                                            'compare' => 'LIKE', // Use 'LIKE' to match any part of the serialized array
                                        );
                                    }
                                } else {
                                    $args['meta_query'][0][] = array(
                                        'key'     => get_the_ID(),
                                        'value' => (string)$value,
                                    );
                                }
                            }
                        }    
                    }

                    if (isset($_GET['_search'])) {
                        $search_doc_report = sanitize_text_field($_GET['_search']);
                        $args['meta_query'][1][] = array( // Append to the OR relation
                            'key'     => get_the_ID(),
                            'value'   => $search_doc_report,
                            'compare' => 'LIKE',
                        );
                    }
                endwhile;
                // Reset only the inner loop's data
                wp_reset_postdata();
            }
            $query = new WP_Query($args);
            return $query;
        }

        function get_previous_report_id($current_report_id) {
            $doc_id = get_post_meta($current_report_id, 'doc_id', true);
        
            if (!$doc_id) {
                return null; // Return null if the 'doc_id' meta is not set
            }
        
            $args = array(
                'post_type'      => 'doc-report',
                'posts_per_page' => 1,
                'orderby'        => 'date', // Sort by post date
                'order'          => 'ASC', // Find the earliest report after the current one
                'meta_query'     => array(
                    array(
                        'key'   => 'doc_id',
                        'value' => $doc_id,
                        'compare' => '=', // Ensure only reports matching the same 'doc_id'
                    ),
                ),
                'date_query'     => array(
                    array(
                        'after' => get_post_field('post_date', $current_report_id), // Get posts after the current report's date
                        'inclusive' => false,
                    ),
                ),
            );
        
            $query = new WP_Query($args);
        
            // Return the previous report ID or null if no match is found
            return $query->have_posts() ? $query->posts[0]->ID : null;
        }

        function get_next_report_id($current_report_id) {
            $doc_id = get_post_meta($current_report_id, 'doc_id', true);
        
            if (!$doc_id) {
                return null; // Return null if the 'doc_id' meta is not set
            }
        
            $args = array(
                'post_type'      => 'doc-report',
                'posts_per_page' => 1,
                'orderby'        => 'date', // Sort by post date
                'order'          => 'DESC', // Find the latest report before the current one
                'meta_query'     => array(
                    array(
                        'key'   => 'doc_id',
                        'value' => $doc_id,
                        'compare' => '=', // Ensure only reports matching the same 'doc_id'
                    ),
                ),
                'date_query'     => array(
                    array(
                        'before' => get_post_field('post_date', $current_report_id), // Get posts before the current report's date
                        'inclusive' => false,
                    ),
                ),
            );
        
            $query = new WP_Query($args);
        
            // Return the next report ID or null if no match is found
            return $query->have_posts() ? $query->posts[0]->ID : null;
        }

        function get_doc_report_list_data() {
            $result = array();
            // Check if _doc_id is set and not empty
            if (!empty($_POST['_doc_id'])) {
                $doc_id = sanitize_text_field($_POST['_doc_id']);
                // Optional search filter for doc report
                $search_doc_report = isset($_POST['_search_doc_report']) ? sanitize_text_field($_POST['_search_doc_report']) : '';
                $params = array(
                    'doc_id'     => $doc_id,
                    'search_doc_report' => $search_doc_report,
                );
                $result['html_contain'] = $this->display_doc_report_list($params);
            } else {
                $result['error'] = 'Document ID is missing.';
            }
            wp_send_json($result);
        }

        function display_doc_report_dialog($report_id=false) {
            ob_start();
            $prev_report_id = $this->get_previous_report_id($report_id); // Fetch the previous ID
            $next_report_id = $this->get_next_report_id($report_id);     // Fetch the next ID
            ?>
            <input type="hidden" id="prev-report-id" value="<?php echo esc_attr($prev_report_id); ?>" />
            <input type="hidden" id="next-report-id" value="<?php echo esc_attr($next_report_id); ?>" />
            <?php
            $todo_status = get_post_meta($report_id, 'todo_status', true);
            $doc_id = get_post_meta($report_id, 'doc_id', true);
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            ?>
            <div class="ui-widget" id="result-container">
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <?php echo display_iso_helper_logo();?>
                    <h2 style="display:inline;"><?php echo esc_html($doc_title.'('.$doc_number.')');?></h2>
                </div>
                <div style="text-align:right; display:flex;">
                    <span id='reset-doc-report-<?php echo esc_attr($report_id);?>' style='margin-left:5px;' class='dashicons dashicons-trash button'></span>
                </div>
            </div>

            <input type="hidden" id="report-id" value="<?php echo esc_attr($report_id);?>" />
            <input type="hidden" id="doc-id" value="<?php echo esc_attr($doc_id);?>" />
            <fieldset>
                <?php
                $params = array(
                    'doc_id'    => $doc_id,
                    'report_id' => $report_id,
                );                
                $this->get_doc_field_contains($params);

                $content = (isset($_GET['_prompt'])) ? generate_content($doc_title.' '.$_GET['_prompt']) : '';
                ?>
                <div class="content">
                    <?php echo $content;?>
                    <div style="margin:1em; padding:10px; border:solid; border-radius:1.5rem;">
                        <input type="text" id="ask-gemini" placeholder="問問 Gemini" class="text ui-widget-content ui-corner-all" />
                    </div>
                </div>            
            <hr>
                <?php
                // Action buttons
                if (empty($todo_status)){
                    ?>
                    <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                    <?php
                    $profiles_class = new display_profiles();
                    $query = $profiles_class->retrieve_doc_action_data($doc_id);
                    if ($query->have_posts()) {
                        while ($query->have_posts()) : $query->the_post();
                            if ($profiles_class->is_user_doc($doc_id)) {
                                echo '<input type="button" id="doc-report-dialog-button-'.get_the_ID().'" value="'.get_the_title().'" style="margin:5px;" />';
                            }
                        endwhile;
                        wp_reset_postdata();
                    }
                    ?>
                    </div>
                    <div style="text-align:right; display:flex;">
                    <?php if ($profiles_class->is_user_doc($doc_id)) {?>
                        <input type="button" id="save-doc-report-<?php echo $report_id;?>" value="<?php echo __( 'Save', 'your-text-domain' );?>" style="margin:3px;" />
                        <input type="button" id="del-doc-report-<?php echo $report_id;?>" value="<?php echo __( 'Delete', 'your-text-domain' );?>" style="margin:3px;" />
                    <?php }?>                    
                        <input type="button" id="exit-doc-report-dialog" value="<?php echo __( 'Exit', 'your-text-domain' );?>" style="margin:3px;" />
                    </div>
                    </div>
                    <?php
                } else {
                    ?>
                    <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <input type="button" id="action-log-button" value="<?php echo __('簽核記錄', 'your-text-domain')?>" style="margin:3px;" />
                        <input type="button" id="duplicate-doc-report-<?php echo $report_id;?>" value="<?php echo __( 'Duplicate', 'your-text-domain' );?>" style="margin:3px;" />
                    </div>
                    <div style="text-align:right;">
                        <input type="button" id="exit-doc-report-dialog" value="<?php echo __( 'Exit', 'your-text-domain' );?>" style="margin:5px;" />
                    </div>
                    </div>
                    <?php
                }
                ?>
            </fieldset>

            <div id="report-action-log-div" style="display:none;">
                <?php $todo_class = new to_do_list();?>
                <?php echo $todo_class->get_action_log($report_id);?>
            </div>
            
            </div>
            <?php
            return ob_get_clean();
        }
        
        function get_doc_report_dialog_data() {
            $response = array();
            if (isset($_POST['_report_id'])) {
                $report_id = sanitize_text_field($_POST['_report_id']);
                $is_admin = isset($_POST['_is_admin']) ? sanitize_text_field($_POST['_is_admin']) : false;

                // Retrieve meta data for report
                $doc_id = get_post_meta($report_id, 'doc_id', true);
                $todo_status = get_post_meta($report_id, 'todo_status', true);
                $_document = get_post_meta($report_id, '_document', true);

                // Determine content based on document type and status
                if ($_document && $todo_status == -1 && !$is_admin) {
                    $is_doc_report = get_post_meta($_document, 'is_doc_report', true);
                    if ($is_doc_report) {
                        $response['html_contain'] = $this->display_doc_report_list(array('doc_id' => $_document));
                    } else {
                        $response['html_contain'] = $this->display_doc_content($_document);
                    }
                } else {
                    $doc_id = get_post_meta($report_id, 'doc_id', true);
                    $response['doc_field_keys'] = $this->get_doc_field_keys($doc_id);
                    $items_class = new embedded_items();
                    $response['embedded_item_keys'] = $items_class->get_embedded_item_keys($doc_id);
                }
            }
            wp_send_json($response);
        }

        function set_doc_report_dialog_data() {
            $response = array();
            if (isset($_POST['_report_id'])) {
                // Update the existing post
                $report_id = sanitize_text_field($_POST['_report_id']);
                $doc_id = get_post_meta($report_id, 'doc_id', true);
                $system_doc = get_post_meta($doc_id, 'system_doc', true);
                if ($system_doc) {
                    // Update the post
                    $post_data = array(
                        'ID'           => $report_id,
                        'post_title'   => $_POST['_post_title'],
                        'post_content' => $_POST['_post_content'],
                    );        
                    wp_update_post($post_data);
                    update_post_meta($report_id, '_post_number', $_POST['_post_number']);

                    if (stripos($system_doc, 'customer') !== false || stripos($system_doc, 'vendor') !== false) {
                        // Code to execute if $system_doc includes 'customer' or 'vendor', case-insensitive
                        $this->upsert_site_profile($report_id);
                    }
                }

                $query = $this->retrieve_doc_field_data(array('doc_id' => $doc_id));
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $this->update_doc_field_contains($report_id, get_the_ID());
                    }
                    wp_reset_postdata();
                }

                $action_id = isset($_POST['_action_id']) ? sanitize_text_field($_POST['_action_id']) : 0;
                $proceed_to_todo = isset($_POST['_proceed_to_todo']) ? sanitize_text_field($_POST['_proceed_to_todo']) : 0;
        
                if ($proceed_to_todo == 1) {
                    $params = array(
                        'action_id' => $action_id,
                        'report_id' => $report_id,
                    );        
                    $todo_class = new to_do_list();
                    $todo_class->create_action_log_and_go_next($params);
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
            $response = array();
            if( isset($_POST['_report_id']) ) {
                // Create the post
                $new_post = array(
                    'post_type'     => 'doc-report',
                    'post_status'   => 'publish',
                    'post_author'   => get_current_user_id(),
                );    
                $post_id = wp_insert_post($new_post);
                $report_id = sanitize_text_field($_POST['_report_id']);
                $doc_id = get_post_meta($report_id, 'doc_id', true);
                update_post_meta($post_id, 'doc_id', $doc_id);

                $query = $this->retrieve_doc_field_data(array('doc_id' => $doc_id));
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        update_post_meta($post_id, get_the_ID(), $_POST[get_the_ID()]);
                    endwhile;
                    wp_reset_postdata();
                }
            }
            wp_send_json($response);
        }

        function upsert_site_profile($_id=false) {
            if (empty($_id)) {
                return false; // Ensure the ID is provided
            }

            // Get the title of the post
            $_title = get_the_title($_id);
            if (empty($_title)) {
                return false; // Ensure the title is valid
            }

            // Sanitize the input title
            $sanitized_title = sanitize_text_field($_title);
        
            // Query to find a matching post
            $args = array(
                'post_type'      => 'site-profile',
                'posts_per_page' => 1,
                'title'          => $sanitized_title, // Match the current title
            );
        
            $query = new WP_Query($args);
        
            if ($query->have_posts()) {
                // If a matching post exists, update its title
                $query->the_post();
                $post_id = get_the_ID();
                wp_update_post(array(
                    'ID'         => $post_id,
                    'post_title' => $sanitized_title, // Update with the sanitized title
                ));
                wp_reset_postdata();
                return $post_id; // Return the updated post ID
            } else {
                // If no matching post exists, create a new post
                $post_id = wp_insert_post(array(
                    'post_title'  => $sanitized_title, // Insert with the sanitized title
                    'post_type'   => 'site-profile',
                    'post_status' => 'publish', // Set to published
                ));
                return $post_id; // Return the new post ID
            }
        }
        
        function get_transactions_by_key_value_pair($key_value_pair = array()) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            if (!empty($key_value_pair)) {
                foreach ($key_value_pair as $key => $value) {
                    $args = array(
                        'post_type'  => 'doc-field',
                        'posts_per_page' => -1, // Retrieve all posts
                        'meta_query' => array(
                            array(
                                'key'   => 'field_type',
                                'value' => $key,
                                'compare' => '='
                            )
                        ),
                        'fields' => 'ids' // Only return post IDs
                    );
                    // Execute the query
                    $query = new WP_Query($args);

                    $doc_ids = array();
                    if ($query->have_posts()) {
                        foreach ($query->posts as $field_id) {
                            $doc_id = get_post_meta($field_id, 'doc_id', true);
                            $doc_site = get_post_meta($doc_id, 'site_id', true);
                            $doc_title = get_post_meta($doc_id, 'doc_title', true);
                            // Ensure the doc ID is unique
                            if (!isset($doc_ids[$doc_id]) && $doc_site == $site_id) {                                
                                $doc_ids[$doc_id] = $doc_title; // Use doc_id as key to ensure uniqueness
                                $params = array(
                                    'doc_id'         => $doc_id,
                                    'key_value_pair' => $key_value_pair,
                                );
                                $doc_report = $this->retrieve_doc_report_data($params);
                                if ($doc_report->have_posts()) {
                                    echo $doc_title. ':';
                                    echo '<fieldset>';
                                    $this->get_doc_report_contain_list($params);
                                    echo '</fieldset>';    
                                }        
                            }
                        }
                        return $query->posts; // Return the array of post IDs
                    }
                }    
            }
            return array(); // Return an empty array if no posts are found
        }

        // doc-field
        function register_doc_field_post_type() {
            $labels = array(
                'menu_name'     => _x('doc-field', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'doc-field', $args );
        }

        function display_doc_field_list($doc_id=false) {
            ob_start();
            ?>
            <div id="fields-container">
            <fieldset>
                <table style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Title', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Type', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Default', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Align', 'your-text-domain' );?></th>
                        </tr>
                    </thead>
                    <tbody id="sortable-doc-field-list">
                        <?php
                        //$x = 0;
                        //if ($doc_id) $params = array('doc_id' => $doc_id);
                        $query = $this->retrieve_doc_field_data(array('doc_id' => $doc_id));
                        if ($query->have_posts()) {
                            while ($query->have_posts()) : $query->the_post();
                                $field_title = get_post_meta(get_the_ID(), 'field_title', true);
                                $field_type = get_post_meta(get_the_ID(), 'field_type', true);
                                $default_value = get_post_meta(get_the_ID(), 'default_value', true);
                                $listing_style = get_post_meta(get_the_ID(), 'listing_style', true);

                                echo '<tr id="edit-doc-field-'.esc_attr(get_the_ID()).'" data-field-id="'.esc_attr(get_the_ID()).'">';
                                if ($field_type=='heading' || $field_type=='canvas' || $field_type=='image' || $field_type=='video') {
                                    if ($field_type=='heading') {
                                        echo '<td style="text-align:center;"><b>'.esc_html($field_title).'</b></td>';
                                    } else {
                                        echo '<td style="text-align:center;">'.esc_html($field_title).'</td>';
                                    }
                                    echo '<td style="text-align:center;">'.esc_html($field_type).'</td>';
                                    echo '<td></td>';
                                    echo '<td></td>';
                                } else {
                                    echo '<td style="text-align:center;">'.esc_html($field_title).'</td>';
                                    echo '<td style="text-align:center;">'.esc_html($field_type).'</td>';
                                    echo '<td style="text-align:center;">'.esc_html($default_value).'</td>';
                                    echo '<td style="text-align:center;">'.esc_html($listing_style).'</td>';
                                }
                                echo '</tr>';
                                //$x += 1;
                            endwhile;
                            wp_reset_postdata();
                        }
                        ?>
                    </tbody>
                </table>
                <?php if (is_site_admin()) {?>
                    <div id="new-doc-field" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php }?>
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

            $query = new WP_Query($args);
            return $query;
        }

        function display_doc_field_dialog($field_id=false) {
            ob_start();
            $field_title = get_post_meta($field_id, 'field_title', true);
            $field_type = get_post_meta($field_id, 'field_type', true);
            $listing_style = get_post_meta($field_id, 'listing_style', true);
            $default_value = get_post_meta($field_id, 'default_value', true);
            $order_field = get_post_meta($field_id, 'order_field', true);
            ?>
            <fieldset>
                <input type="hidden" id="field-id" value="<?php echo esc_attr($field_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="field-title"><?php echo __( '欄位名稱：', 'your-text-domain' );?></label>
                <input type="text" id="field-title" value="<?php echo esc_attr($field_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="field-type"><?php echo __( '欄位型態：', 'your-text-domain' );?></label>
                <select id="field-type" class="text ui-widget-content ui-corner-all">
                    <option value="text" <?php echo ($field_type=='text') ? 'selected' : ''?>><?php echo __( 'Text', 'your-text-domain' );?></option>
                    <option value="textarea" <?php echo ($field_type=='textarea') ? 'selected' : ''?>><?php echo __( 'Textarea', 'your-text-domain' );?></option>
                    <option value="number" <?php echo ($field_type=='number') ? 'selected' : ''?>><?php echo __( 'Number', 'your-text-domain' );?></option>
                    <option value="date" <?php echo ($field_type=='date') ? 'selected' : ''?>><?php echo __( 'Date', 'your-text-domain' );?></option>
                    <option value="time" <?php echo ($field_type=='time') ? 'selected' : ''?>><?php echo __( 'Time', 'your-text-domain' );?></option>
                    <option value="checkbox" <?php echo ($field_type=='checkbox') ? 'selected' : ''?>><?php echo __( 'Checkbox', 'your-text-domain' );?></option>
                    <option value="radio" <?php echo ($field_type=='radio') ? 'selected' : ''?>><?php echo __( 'Radio', 'your-text-domain' );?></option>
                    <option value="heading" <?php echo ($field_type=='heading') ? 'selected' : ''?>><?php echo __( 'Heading', 'your-text-domain' );?></option>
                    <option value="canvas" <?php echo ($field_type=='canvas') ? 'selected' : ''?>><?php echo __( 'Canvas', 'your-text-domain' );?></option>
<?php /*                    
                    <option value="_max_value" <?php echo ($field_type=='_max_value') ? 'selected' : ''?>><?php echo __( '_max_value', 'your-text-domain' );?></option>
                    <option value="_min_value" <?php echo ($field_type=='_min_value') ? 'selected' : ''?>><?php echo __( '_min_value', 'your-text-domain' );?></option>
                    <option value="_planning" <?php echo ($field_type=='_planning') ? 'selected' : ''?>><?php echo __( '_planning', 'your-text-domain' );?></option>
                    <option value="_select" <?php echo ($field_type=='_select') ? 'selected' : ''?>><?php echo __( '_select', 'your-text-domain' );?></option>
                    <option value='_employee' <?php echo ($field_type=='_employee') ? 'selected' : ''?>><?php echo __( '_employee', 'your-text-domain' );?></option>
*/?>                    
                    <option value="_embedded" <?php echo ($field_type=='_embedded') ? 'selected' : ''?>><?php echo __( '_embedded', 'your-text-domain' );?></option>
                    <option value="_iot_device" <?php echo ($field_type=='_iot_device') ? 'selected' : ''?>><?php echo __( '_iot_device', 'your-text-domain' );?></option>
                    <?php
                    $query = $this->get_system_doc_list_query();
                    if ($query->have_posts()) {
                        while ($query->have_posts()) {
                            $query->the_post();
                            $system_doc = get_post_meta(get_the_ID(), 'system_doc', true);
                            // Add to the list if meta exists and is not empty
                            if (!empty($system_doc)) {
                                ?>
                                <option value="<?php echo $system_doc;?>" <?php echo ($field_type==$system_doc) ? 'selected' : ''?>><?php echo __( $system_doc, 'your-text-domain' );?></option>
                                <?php
                            }
                        }
                        wp_reset_postdata();
                    }
                    ?>
                    <option value="_document" <?php echo ($field_type=='_document') ? 'selected' : ''?>><?php echo __( '_document', 'your-text-domain' );?></option>
                    <option value="_doc_report" <?php echo ($field_type=='_doc_report') ? 'selected' : ''?>><?php echo __( '_doc_report', 'your-text-domain' );?></option>
                    <option value="_department" <?php echo ($field_type=='_department') ? 'selected' : ''?>><?php echo __( '_department', 'your-text-domain' );?></option>
                    <option value='_employees' <?php echo ($field_type=='_employees') ? 'selected' : ''?>><?php echo __( '_employees', 'your-text-domain' );?></option>
                    <option value="image" <?php echo ($field_type=='image') ? 'selected' : ''?>><?php echo __( 'Picture', 'your-text-domain' );?></option>
                    <option value="video" <?php echo ($field_type=='video') ? 'selected' : ''?>><?php echo __( 'Video', 'your-text-domain' );?></option>
                </select>
                <label for="default-value"><?php echo __( '初始值：', 'your-text-domain' );?></label>
                <input type="text" id="default-value" value="<?php echo esc_attr($default_value);?>" class="text ui-widget-content ui-corner-all" />
                <label for="listing-style"><?php echo __( '列表排列：', 'your-text-domain' );?></label>
                <select id="listing-style" class="text ui-widget-content ui-corner-all">
                    <option value=""></option>
                    <option value="left" <?php echo ($listing_style=='left') ? 'selected' : ''?>><?php echo __( '靠左', 'your-text-domain' );?></option>
                    <option value="center" <?php echo ($listing_style=='center') ? 'selected' : ''?>><?php echo __( '置中', 'your-text-domain' );?></option>
                    <option value="right" <?php echo ($listing_style=='right') ? 'selected' : ''?>><?php echo __( '靠右', 'your-text-domain' );?></option>
                </select>
            </fieldset>
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
                update_post_meta($field_id, 'field_title', sanitize_text_field($_POST['_field_title']));
                update_post_meta($field_id, 'field_type', sanitize_text_field($_POST['_field_type']));
                update_post_meta($field_id, 'default_value', sanitize_text_field($_POST['_default_value']));
                update_post_meta($field_id, 'listing_style', sanitize_text_field($_POST['_listing_style']));
                update_post_meta($field_id, 'order_field', sanitize_text_field($_POST['_order_field']));
            } else {
                // Create the post
                $new_post = array(
                    'post_type'     => 'doc-field',
                    'post_status'   => 'publish',
                    'post_author'   => get_current_user_id(),
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'doc_id', sanitize_text_field($_POST['_doc_id']));
                update_post_meta($post_id, 'field_title', 'Field title');
                update_post_meta($post_id, 'field_type', 'text');
                update_post_meta($post_id, 'listing_style', 'center');
                update_post_meta($post_id, 'sorting_key', 999);
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
                    update_post_meta($field_id, 'sorting_key', $index);
                }
                $response = array('success' => true);
            }
            wp_send_json($response);
        }

        function get_doc_field_keys($doc_id=false) {
            if ($doc_id) $params = array('doc_id' => $doc_id);
            $query = $this->retrieve_doc_field_data($params);
            $_array = array();
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $field_type = get_post_meta(get_the_ID(), 'field_type', true);
                    $default_value = get_post_meta(get_the_ID(), 'default_value', true);
                    $_list = array();
                    $_list["field_id"] = get_the_ID();
                    $_list["field_type"] = $field_type;
                    $_list["default_value"] = $default_value;
                    array_push($_array, $_list);
                endwhile;
                wp_reset_postdata();
            }    
            return $_array;
        }

        function get_doc_field_default_value($field_id=false, $user_id=false) {
            // Ensure $field_id is provided
            if (!$field_id) {
                return false; // Return false or handle the error as needed
            }
            // Get the current user ID
            if (!$user_id) {
                $user_id = get_current_user_id();
            }
            // Get and sanitize the field name and default value
            $default_value = sanitize_text_field(get_post_meta($field_id, 'default_value', true));
            $field_type = sanitize_text_field(get_post_meta($field_id, 'field_type', true));

            if ($field_type=='date' && $default_value === 'today') $default_value = wp_date('Y-m-d', time());
            if ($field_type=='_employee' && $default_value === 'me') $default_value = $user_id;
            if ($field_type=='_employees' && $default_value === 'me') $default_value = array($user_id);
            if ($default_value=='_post_number') $default_value=time();
            if ($default_value=='_post_title') $default_value='';
            if ($default_value=='_post_content') $default_value='';

            return $default_value;
        }

        function get_doc_field_contains($params=array()) {
            $doc_id = isset($params['doc_id']) ? $params['doc_id'] : 0;
            $report_id = isset($params['report_id']) ? $params['report_id'] : 0;
            $prev_report_id = isset($params['prev_report_id']) ? $params['prev_report_id'] : 0;
            $todo_id = isset($params['todo_id']) ? $params['todo_id'] : 0;
            $is_todo = isset($params['is_todo']) ? $params['is_todo'] : 0;

            $query = $this->retrieve_doc_field_data($params);
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $field_id = get_the_ID();
                    $field_title = get_post_meta($field_id, 'field_title', true);
                    $field_type = get_post_meta($field_id, 'field_type', true);
                    $default_value = get_post_meta($field_id, 'default_value', true);

                    if ($report_id) {
                        $field_value = get_post_meta($report_id, $field_id, true);
                        $todo_status = get_post_meta($report_id, 'todo_status', true);
                    } elseif ($prev_report_id) {
                        $field_value = get_post_meta($prev_report_id, $field_id, true);
                        $todo_status = get_post_meta($prev_report_id, 'todo_status', true);
                    } else {
                        $field_value = $this->get_doc_field_default_value($field_id);
                    }

                    switch (true) {
                        case ($field_type=='_employee'):
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_id);?>" class="text ui-widget-content ui-corner-all"><?php echo $this->select_multiple_employees_options(array($field_value));?></select>
                            <?php 
                            break;

                        case ($field_type=='_employees'):
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <select multiple id="<?php echo esc_attr($field_id);?>" class="text ui-widget-content ui-corner-all multiple-select"><?php echo $this->select_multiple_employees_options($field_value);?></select>
                            <?php 
                            break;

                        case ($field_type=='_embedded'):
                            $items_class = new embedded_items();
                            $embedded_id = $items_class->get_embedded_id_by_number($default_value);
                            if ($embedded_id) {
                                $embedded_type = get_post_meta($embedded_id, 'embedded_type', true);
                                if ($embedded_type=='line-list') {
                                    ?>
                                    <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                                    <div id="line-report-list">
                                        <?php
                                        if ($report_id) echo $items_class->display_line_report_list($embedded_id, $report_id);
                                        elseif ($prev_report_id) echo $items_class->display_line_report_list($embedded_id, $prev_report_id);
                                        else echo $items_class->display_line_report_list($embedded_id);
                                        ?>
                                    </div>
                                    <?php
                                }
                                if ($embedded_type=='sub-form') {
                                    ?>
                                    <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                                    <input type="hidden" id="<?php echo esc_attr($field_id); ?>" value="<?php echo esc_attr($embedded_id);?>" />
                                    <div id="sub-form">
                                        <fieldset>
                                        <?php
                                        $inner_query = $items_class->retrieve_embedded_item_data($embedded_id);
                                        if ($inner_query->have_posts()) :
                                            while ($inner_query->have_posts()) : $inner_query->the_post();
                                                if ($report_id) {
                                                    $embedded_item_value = get_post_meta($report_id, $field_id.get_the_ID(), true);
                                                } elseif ($prev_report_id) {
                                                    $embedded_item_value = get_post_meta($prev_report_id, $field_id.get_the_ID(), true);
                                                } else {
                                                    $embedded_item_value = get_post_meta(get_the_ID(), 'embedded_item_default', true);
                                                }
                                                $items_class->get_embedded_item_contains($field_id, get_the_ID(), $embedded_item_value);
                                            endwhile;
                                            wp_reset_postdata();
                                        endif;
                                        ?>
                                        </fieldset>
                                    </div>
                                    <?php
                                }
                                if ($embedded_type=='select-options') {
                                    ?>
                                    <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                                    <select id="<?php echo esc_attr($field_id);?>" class="text ui-widget-content ui-corner-all"><?php echo $items_class->select_embedded_item_options($field_value, $embedded_id);?></select>
                                    <?php
                                }
                            }
                            break;

                        case ($field_type=='_doc_report'):
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_id);?>" class="text ui-widget-content ui-corner-all"><?php echo $this->select_document_list_options($field_value, 1);?></select>
                            <?php
                            break;

                        case ($field_type=='_document'):
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_id);?>" class="text ui-widget-content ui-corner-all"><?php echo $this->select_document_list_options($field_value, 0);?></select>
                            <?php
                            break;

                        case ($field_type=='_department'):
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_id);?>" class="text ui-widget-content ui-corner-all"><?php echo $items_class->select_department_card_options($field_value);?></select>
                            <?php
                            break;
    
                        case ($field_type=='_iot_device'):
                            $iot_messages = new iot_messages();
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_id);?>" class="text ui-widget-content ui-corner-all"><?php echo $iot_messages->select_iot_device_options($field_value);?></select>
                            <?php
                            break;
                        case ($field_type=='video'):
                            echo '<label class="video-button button" for="'.esc_attr($field_id).'">'.esc_html($field_title).'</label>';
                            $field_value = ($field_value) ? $field_value : get_option('default_video_url');
                            echo '<div style="display:flex;" class="video-display" id="'.esc_attr($field_id.'_video').'">'.$field_value.'</div>';
                            echo '<textarea class="video-url" id="'.esc_attr($field_id).'" rows="3" style="width:100%; display:none;" >'.esc_html($field_value).'</textarea>';
                            break;

                        case ($field_type=='image'):
                            echo '<label class="image-button button" for="'.esc_attr($field_id).'">'.esc_html($field_title).'</label>';
                            $field_value = ($field_value) ? $field_value : get_option('default_image_url');
                            echo '<img style="width:100%;" class="image-display" src="'.$field_value.'" />';
                            echo '<textarea class="image-url" id="'.esc_attr($field_id).'" rows="3" style="width:100%; display:none;" >'.esc_html($field_value).'</textarea>';
                            break;

                        case ($field_type=='canvas'):
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <div id="signature-image-div">
                                <?php if ($field_value) {?>
                                    <div><img id="<?php echo esc_attr($field_id);?>" src="<?php echo esc_attr($field_value);?>" alt="Signature Image" /></div>
                                <?php }?>
                                <?php if (!$todo_status) {?>
                                    <button id="redraw-signature" style="margin:3px;"><?php echo __( 'Redraw', 'your-text-domain' );?></button>
                                <?php }?>
                            </div>
                            <div style="display:none;" id="signature-pad-div">
                                <div>
                                    <canvas id="signature-pad" width="500" height="200" style="border:1px solid #000;"></canvas>
                                </div>
                                <button id="clear-signature" style="margin:3px;">Clear</button>
                            </div>
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
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <textarea id="<?php echo esc_attr($field_id);?>" rows="3" style="width:100%;"><?php echo esc_html($field_value);?></textarea>
                            <?php    
                            break;

                        case ($field_type=='checkbox'):
                            $is_checked = ($field_value==1) ? 'checked' : '';
                            ?>
                            <div>
                            <input type="checkbox" id="<?php echo esc_attr($field_id);?>" <?php echo $is_checked;?> />
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            </div>
                            <?php
                            break;

                        case ($field_type=='radio'):
                            $is_checked = ($field_value==1) ? 'checked' : '';
                            ?>
                            <div>
                            <input type="radio" id="<?php echo esc_attr($field_id);?>" name="<?php echo esc_attr(substr(time(), 0, 5));?>" <?php echo $is_checked;?> />
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            </div>
                            <?php
                            break;

                        case ($field_type=='date'):
                            ?>
                            <div>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <input type="date" id="<?php echo esc_attr($field_id);?>" value="<?php echo esc_html($field_value);?>" class="ui-widget-content ui-corner-all" />
                            </div>
                            <?php
                            break;

                        case ($field_type=='time'):
                            ?>
                            <div>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <input type="time" id="<?php echo esc_attr($field_id);?>" value="<?php echo esc_html($field_value);?>" class="ui-widget-content ui-corner-all" />
                            </div>
                            <?php
                            break;

                        case ($field_type=='number'):
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <input type="number" id="<?php echo esc_attr($field_id);?>" value="<?php echo esc_html($field_value);?>" class="text ui-widget-content ui-corner-all" />
                            <?php
                            break;

                        default:
                            $get_system_doc_id = $this->get_system_doc_id($field_type);
                            if ($get_system_doc_id) {
                                $params['doc_id'] = $get_system_doc_id;
                                ?>
                                <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                                <select id="<?php echo esc_attr($field_id);?>" class="text ui-widget-content ui-corner-all"><?php echo $this->select_system_doc_options($field_value, $params);?></select>
                                <?php
                            } else {
                                ?>
                                <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                                <input type="text" id="<?php echo esc_attr($field_id);?>" value="<?php echo esc_html($field_value);?>" class="text ui-widget-content ui-corner-all" />
                                <?php    
                            }
                            break;
                    }
                endwhile;
                wp_reset_postdata();
            }
        }

        // employees
        function select_multiple_employees_options($selected_options = array()) {
            if (!is_array($selected_options)) {
                $selected_options = array();
            }

            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            // Retrieve users based on site_id
            $meta_query_args = array(
                array(
                    'key'     => 'site_id',
                    'value'   => $site_id,
                    'compare' => '=',
                ),
            );
            $users = get_users(array('meta_query' => $meta_query_args));

            // Initialize options HTML
            $options = '';
            // Loop through the users
            foreach ($users as $user) {
                // Check if the current user ID is in the selected options array
                $selected = in_array($user->ID, $selected_options) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . esc_html($user->display_name) . '</option>';
            }
            // Return the options HTML
            return $options;
        }

        function update_doc_field_contains($report_id=false, $field_id=false, $is_default=false, $user_id=false) {
            // standard fields
            $field_type = get_post_meta($field_id, 'field_type', true);
            $default_value = get_post_meta($field_id, 'default_value', true);
            if ($is_default) {
                $field_value = $this->get_doc_field_default_value($field_id, $user_id);
            } else {
                $field_value = $_POST[$field_id];
            }
            update_post_meta($report_id, $field_id, $field_value);

            // special field-type
            if ($field_type=='_employees'){
                $employee_ids = get_post_meta($report_id, '_employees', true);
                // Ensure $employee_ids is an array, or initialize it as an empty array
                if (!is_array($employee_ids)) {
                    $employee_ids = array();
                }

                if ($default_value=='me'){
                    $current_user_id = get_current_user_id();
                    // Check if the $current_user_id is not already in the $employee_ids array
                    if (!in_array($current_user_id, $employee_ids)) {
                        // Add the value to the $employee_ids array
                        $employee_ids = array($current_user_id);
                    }
                } else {
                    // Loop through each value in $field_value to check and add to $employee_ids
                    foreach ($field_value as $value) {
                        // Check if the value is not already in the $employee_ids array
                        if (!in_array($value, $employee_ids)) {
                            // Add the value to the $employee_ids array
                            $employee_ids[] = $value;
                        }
                    }    
                }
                update_post_meta($report_id, '_employees', $employee_ids);
            }

            if ($field_type=='_employee'){
                update_post_meta($report_id, '_employee', $field_value);
            }

            if ($field_type=='_document'){
                update_post_meta($report_id, '_document', $field_value);
            }

            if ($field_type=='_department'){
                update_post_meta($report_id, '_department', $field_value);
            }

            if ($field_type=='_embedded'){
                $items_class = new embedded_items();
                $embedded_id = $items_class->get_embedded_id_by_number($default_value);
                if ($embedded_id) {
                    $embedded_type = get_post_meta($embedded_id, 'embedded_type', true);
                    if ($embedded_type=='line-list') {
                        $inner_query = $items_class->retrieve_line_list_data($embedded_id);
                        if ($inner_query->have_posts()) :
                            while ($inner_query->have_posts()) : $inner_query->the_post();
                                $embedded_item_value = $_POST[$field_id.get_the_ID()];
                                update_post_meta($report_id, $field_id.get_the_ID(), $embedded_item_value);
                            endwhile;
                            wp_reset_postdata();
                        endif;
                    }
                    if ($embedded_type=='sub-form') {
                        $inner_query = $items_class->retrieve_embedded_item_data($embedded_id);
                        if ($inner_query->have_posts()) :
                            while ($inner_query->have_posts()) : $inner_query->the_post();
                                $embedded_item_value = $_POST[$field_id.get_the_ID()];
                                update_post_meta($report_id, $field_id.get_the_ID(), $embedded_item_value);
                            endwhile;
                            wp_reset_postdata();
                        endif;
                    }
                }
/*                
                if (strpos($default_value, '=') !== false) {
                    list($key, $value) = explode('=', $default_value, 2);
                    if ($key=='_list') {
                        $embedded_id = $items_class->get_embedded_id_by_number($value);
                        $inner_query = $items_class->retrieve_line_report_data($embedded_id);
                        if ($inner_query->have_posts()) :
                            while ($inner_query->have_posts()) : $inner_query->the_post();
                                update_post_meta(get_the_ID(), 'report_id', $report_id);
                            endwhile;
                            wp_reset_postdata();
                        endif;
                    }
                    if ($key=='_form') {
                        $embedded_id = $items_class->get_embedded_id_by_number($value);
                        $inner_query = $items_class->retrieve_embedded_item_data($embedded_id);
                        if ($inner_query->have_posts()) :
                            while ($inner_query->have_posts()) : $inner_query->the_post();
                                $embedded_item_value = $_POST[$field_id.get_the_ID()];
                                update_post_meta($report_id, $field_id.get_the_ID(), $embedded_item_value);
                            endwhile;
                            wp_reset_postdata();
                        endif;
                    }
                    if ($key=='_select') {
                    }
                }
*/
            }
        }
        
        // document misc
        function get_system_doc_list_query() {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $args = array(
                'post_type'      => 'document', // Specify the post type
                'posts_per_page' => -1, // Retrieve all matching posts
                'meta_query'     => array(
                    'relation' => 'AND', // Combine all conditions
                    array(
                        'relation' => 'OR', // Either the 'system_doc' is not empty OR does not exist
                        array(
                            'key'     => 'system_doc',
                            'compare' => '!=', // 'system_doc' is not empty
                            'value'   => '',
                        ),
                        array(
                            'key'     => 'system_doc',
                            'compare' => 'NOT EXISTS', // 'system_doc' does not exist
                        ),
                    ),
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id, // Match the 'site_id' meta value
                        'compare' => '=', // Exact match
                    ),
                ),
            );
            $query = new WP_Query($args);
            return $query;
        }

        function get_system_doc_id($field_type = false) {
            // Ensure $field_type is provided
            if (!$field_type) {
                return false;
            }

            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            // If site_id is not set, return false early
            if (!$site_id) {
                return false;
            }

            // Query arguments
            $args = array(
                'post_type'      => 'document', // Specify the post type
                'posts_per_page' => 1, // Only retrieve one post (to optimize performance)
                'meta_query'     => array(
                    'relation' => 'AND', // Combine all conditions
                    array(
                        'key'     => 'system_doc', // The meta key to check
                        'value'   => $field_type,  // The value to match
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key'     => 'site_id', // Check site_id meta key
                        'value'   => $site_id, // Match the 'site_id' meta value
                        'compare' => '=', // Exact match
                    ),
                ),
            );
            // Perform the query
            $query = new WP_Query($args);

            // Check if posts exist
            if ($query->have_posts()) {
                $query->the_post(); // Set up the post data
                $post_id = get_the_ID(); // Retrieve the post ID
                wp_reset_postdata(); // Reset the global post object
                return $post_id; // Return the ID of the first matching post
            } else {
                return false; // No matches found
            }
        }

        function select_system_doc_options($selected_option=0, $params=array()) {
            $query = $this->retrieve_doc_report_data($params);
            $options = '<option value="">Select an option</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        function select_document_list_options($selected_option=0, $is_doc_report=2) {
            $query = $this->retrieve_document_data(0, $is_doc_report);
            $options = '<option value="">Select document</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $doc_title = get_post_meta(get_the_ID(), 'doc_title', true);
                $doc_number = get_post_meta(get_the_ID(), 'doc_number', true);
                $doc_revision = get_post_meta(get_the_ID(), 'doc_revision', true);
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html($doc_number.'-'.$doc_title.'-'.$doc_revision) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        function get_iso_helper_documents_by_iso_category($iso_category_id) {
            // Step 1: Get the 'site-profile' post with the title 'iso-helper.com'
            $args = array(
                'post_type'   => 'site-profile',
                'post_status' => 'publish', // Only look for published pages
                'title'       => 'iso-helper.com',
                'numberposts' => 1,         // Limit the number of results to one
            );            
            $posts = get_posts($args); // get_posts returns an array

            // Ensure there's a post returned
            if (!empty($posts)) {
                $site_id = $posts[0]->ID; // Retrieve the ID of the first post
            } else {
                return new WP_Query(); // Return an empty query if no 'site-profile' found
            }

            // Step 2: Get the IDs from the 'doc-category' post type where 'iso_category' meta = $iso_category_id and 'site_id' = $site_id
            $doc_category_query = new WP_Query(array(
                'post_type'  => 'doc-category',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                        'compare' => '='
                    ),
                    array(
                        'key'     => 'iso_category',
                        'value'   => $iso_category_id,
                        'compare' => '='
                    ),
                ),
                'posts_per_page' => -1, // Retrieve all matching posts from 'doc-category'
                'fields' => 'ids', // Retrieve only the post IDs for efficiency
            ));

            // Step 3: Check if we found posts in 'doc-category'
            if ($doc_category_query->have_posts()) {
                $doc_category_ids = $doc_category_query->posts; // Get all IDs of 'doc-category' posts
                wp_reset_postdata(); // Reset post data after query

                // Step 4: Use the retrieved doc-category IDs to query the 'document' post type
                $document_query = new WP_Query(array(
                    'post_type'  => 'document',
                    'meta_query' => array(
                        array(
                            'key'     => 'doc_category',
                            'value'   => $doc_category_ids,
                            'compare' => 'IN' // Match any of the retrieved 'doc_category' IDs
                        ),
                    ),
                    'posts_per_page' => -1, // Retrieve all matching posts
                    'meta_key' => 'doc_number', // Sort by 'doc_number' meta field
                    'orderby'  => 'meta_value', // Order by meta value
                    'order'    => 'ASC', // Sort in ascending order
                ));
                // Return the query object
                return $document_query;
            } else {
                // If no 'doc-category' posts are found, return an empty WP_Query
                return new WP_Query(); // Empty query object
            }
        }

        function display_iso_statement_content($iso_category_id=false, $paged=1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $embedded_id = get_post_meta($iso_category_id, 'embedded', true);
            $iso_category_title = get_the_title($iso_category_id);
            ?>
            <div class="ui-widget" id="result-container">
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <?php echo display_iso_helper_logo();?>
                        <h2 style="display:inline;"><?php echo esc_html($iso_category_title.' 啟動AI輔導');?></h2>
                    </div>
                </div>
                <input type="hidden" id="iso-category-title" value="<?php echo esc_attr($iso_category_title);?>" />
                <input type="hidden" id="iso-category-id" value="<?php echo esc_attr($iso_category_id);?>" />            
                <fieldset>
                    <?php
                    if ($paged==1) {
                        $prompt = (isset($_GET['_prompt'])) ? $_GET['_prompt'] : '適用性聲明書';
                        $content = generate_content($iso_category_title.$prompt);
                        $items_class = new embedded_items();
                        ?>
                        <div class="content">
                            <fieldset>
                                <p>Title:<input type="text" id="draft-title" value="<?php echo $iso_category_title.$prompt;?>" class="text ui-widget-content ui-corner-all" /></p>
                                <label for="draft-category"><?php echo __( '文件類別', 'your-text-domain' );?></label><br>
                                <select id="draft-category" class="text ui-widget-content ui-corner-all"><?php echo $items_class->select_doc_category_options();?></select>
                                <p>Content:<textarea id="draft-content" class="visual-editor"><?php echo $content;?></textarea></p>
                                <?php if (is_site_admin()) {?>
                                    <p><input type="button" id="save-draft" value="Generate draft" /></p>
                                <?php }?>
                            </fieldset>
                            <div style="margin:1em; padding:10px; border:solid; border-radius:1.5rem;">
                                <input type="text" id="ask-gemini" placeholder="問問 Gemini" class="text ui-widget-content ui-corner-all" />
                            </div>
                        </div>
                        <?php
                    } else {
                        $prompt = (isset($_GET['_prompt'])) ? $_GET['_prompt'] : '文件明細列表';
                        $content = generate_content($iso_category_title.$prompt);
                        ?>
                        <div class="content">
                            <?php echo $content;?>
                            <fieldset>
                                <?php
                                $query = $this->get_iso_helper_documents_by_iso_category($iso_category_id);
                                if ($query->have_posts()) :
                                    while ($query->have_posts()) : $query->the_post();
                                        $doc_title = get_post_meta(get_the_ID(), 'doc_title', true);
                                        $doc_number = get_post_meta(get_the_ID(), 'doc_number', true);
                                        $doc_category = get_post_meta(get_the_ID(), 'doc_category', true);
                                        $site_id = get_post_meta(get_the_ID(), 'site_id', true);
                                        ?>
                                        <div>
                                            <input type="checkbox" class="copy-document-class" id="<?php the_ID();?>" checked />
                                            <label for="<?php the_ID();?>"><?php echo $doc_title.'('.$doc_number.')';?></label>
                                        </div>
                                        <?php
                                    endwhile;
                                    wp_reset_postdata();
                                    if (is_site_admin()) {?>
                                        <button id="proceed-copy-statement" class="button" style="margin:5px;"><?php echo __( 'Copy the checked documents from iso-helper.com', 'your-text-domain' );?></button>
                                    <?php }
                                endif;
                                ?>
                            </fieldset>
                            <div style="margin:1em; padding:10px; border:solid; border-radius:1.5rem;">
                                <input type="text" id="ask-gemini" placeholder="問問 Gemini" class="text ui-widget-content ui-corner-all" />
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </fieldset>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <?php if ($paged==1) {?>
                        <div>
                            <button id="exit-statement" class="button" style="margin:5px;"><?php echo __( 'Back', 'your-text-domain' );?></button>
                        </div>
                        <div style="text-align: right">
                            <button id="statement-page1-next-step" class="button" style="margin:5px;"><?php echo __( 'Next', 'your-text-domain' );?></button>
                        </div>
                    <?php } else {?>
                        <div>
                            <button id="statement-page2-prev-step" class="button" style="margin:5px;"><?php echo __( 'Back', 'your-text-domain' );?></button>
                        </div>
                        <div style="text-align: right">
                            <button id="exit-statement" class="button" style="margin:5px;"><?php echo __( 'Done', 'your-text-domain' );?></button>
                        </div>
                    <?php }?>
                </div>
            </div>
            <?php
        }

        function set_iso_statement_data() {
            $response = array('success' => false, 'error' => 'Invalid data format');

            if (isset($_POST['_keyValuePairs']) && is_array($_POST['_keyValuePairs'])) {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
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
                $response = array('success' => true, 'data' => $processedKeyValuePairs);

            } elseif (isset($_POST['_draft_title']) && isset($_POST['_draft_content'])) {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $draft_title = sanitize_text_field($_POST['_draft_title']);
                $draft_category = sanitize_text_field($_POST['_draft_category']);
                $draft_content = $_POST['_draft_content'];
                $draft_post = array(
                    'post_type'    => 'document',
                    'post_title'   => $draft_title,
                    'post_content' => $draft_content,
                    'post_status'  => 'publish',
                    'post_author'  => $current_user_id,
                );
                $draft_id = wp_insert_post($draft_post);
                update_post_meta($draft_id, 'site_id', $site_id);
                update_post_meta($draft_id, 'doc_title', $draft_title);
                update_post_meta($draft_id, 'doc_number', '-');
                update_post_meta($draft_id, 'doc_revision', 'draft');
                update_post_meta($draft_id, 'doc_category', $draft_category);

                $params = array(
                    'log_message' => 'Generate the draft of '.$draft_title,
                );
                $todo_class = new to_do_list();
                $todo_class->create_action_log_and_go_next($params);

                $response = array('success' => true, 'data' => $draft_id);

            } elseif (isset($_POST['_duplicated_ids'])) {
                $duplicated_ids = $_POST['_duplicated_ids'];
                foreach ($duplicated_ids as $duplicated_id) {
                    if ($this->current_site_is_iso_helper()) {
                        if (current_user_can('administrator')) {
                            $this->generate_draft_document_data($duplicated_id);
                        }
                    }
                    else {
                        $this->generate_draft_document_data($duplicated_id);
                    }
                }
                $response = array('success' => true, 'data' => $duplicated_ids);
            }
            wp_send_json($response);
        }
        
        function current_site_is_iso_helper() {
            // Query the "site-profile" post type to find a post with the title "iso-helper.com"
            $args = array(
                'post_type'      => 'site-profile',
                'title'          => 'iso-helper.com',
                'posts_per_page' => 1,
                'fields'         => 'ids' // Only fetch the post ID
            );
            $query = new WP_Query($args);

            // Check if we have any matching "site-profile" posts
            if ($query->have_posts()) {
                //return "Matching 'site-profile' post found for site_id: $site_id with title 'iso-helper.com'.";
                return true;
            } else {
                //return "No matching 'site-profile' post found for site_id: $site_id with title 'iso-helper.com'.";
                return false;
            }
        }

        function generate_draft_document_data($doc_id=false){
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $job_number = get_post_meta($doc_id, 'job_number', true);
            $doc_title = get_post_meta($doc_id, 'doc_title', true);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $doc_frame = get_post_meta($doc_id, 'doc_frame', true);
            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
            // Create the post
            $new_post = array(
                'post_type'     => 'document',
                'post_title'    => get_the_title($doc_id),
                'post_content'  => get_post_field('post_content', $doc_id),
                'post_status'   => 'publish',
                'post_author'   => $current_user_id,
            );    
            $post_id = wp_insert_post($new_post);

            update_post_meta($post_id, 'site_id', $site_id);
            update_post_meta($post_id, 'job_number', $job_number);
            update_post_meta($post_id, 'doc_title', $doc_title);
            update_post_meta($post_id, 'doc_number', $doc_number);
            update_post_meta($post_id, 'doc_revision', 'draft');
            update_post_meta($post_id, 'doc_frame', $doc_frame);
            update_post_meta($post_id, 'is_doc_report', $is_doc_report);

            $params = array(
                'log_message' => 'Generate the draft of '.$doc_title,
            );
            $todo_class = new to_do_list();
            $todo_class->create_action_log_and_go_next($params);

            // Create the Action list for $post_id
            $profiles_class = new display_profiles();
            $query = $profiles_class->retrieve_doc_action_data($doc_id);
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $new_post = array(
                        'post_type'     => 'action',
                        'post_title'    => get_the_title(),
                        'post_content'  => get_the_content(),
                        'post_status'   => 'publish',
                        'post_author'   => $current_user_id,
                    );    
                    $new_action_id = wp_insert_post($new_post);
                    $new_next_job = get_post_meta(get_the_ID(), 'next_job', true);
                    $new_next_leadtime = get_post_meta(get_the_ID(), 'next_leadtime', true);
                    update_post_meta($new_action_id, 'doc_id', $post_id);
                    update_post_meta($new_action_id, 'next_job', $new_next_job);
                    update_post_meta($new_action_id, 'next_leadtime', $new_next_leadtime);
                endwhile;
                wp_reset_postdata();
            }

            if ($is_doc_report==1){
                $query = $this->retrieve_doc_field_data(array('doc_id' => $doc_id));
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        $field_title = get_post_meta(get_the_ID(), 'field_title', true);
                        $field_type = get_post_meta(get_the_ID(), 'field_type', true);
                        $default_value = get_post_meta(get_the_ID(), 'default_value', true);
                        $listing_style = get_post_meta(get_the_ID(), 'listing_style', true);
                        $sorting_key = get_post_meta(get_the_ID(), 'sorting_key', true);
                        $new_post = array(
                            'post_type'     => 'doc-field',
                            'post_status'   => 'publish',
                            'post_author'   => $current_user_id,
                        );    
                        $field_id = wp_insert_post($new_post);
                        update_post_meta($field_id, 'doc_id', $post_id);
                        update_post_meta($field_id, 'field_title', $field_title);
                        update_post_meta($field_id, 'field_type', $field_type);
                        update_post_meta($field_id, 'default_value', $default_value);
                        update_post_meta($field_id, 'listing_style', $listing_style);
                        update_post_meta($field_id, 'sorting_key', $sorting_key);
                    endwhile;
                    wp_reset_postdata();
                }    
            }
        }
        
        function reset_doc_report_todo_status() {
            $response = array();
            if( isset($_POST['_report_id']) ) {
                $report_id = sanitize_text_field($_POST['_report_id']);
                delete_post_meta($report_id, 'todo_status');
            }
            wp_send_json($response);
        }
    }
    $documents_class = new display_documents();
}

