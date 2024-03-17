<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register site post type
function register_site_post_type() {
    $labels = array(
        'name'               => _x( 'Sites', 'post type general name', 'your-text-domain' ),
        'singular_name'      => _x( 'Site', 'post type singular name', 'your-text-domain' ),
        'add_new'            => _x( 'Add New Site', 'book', 'your-text-domain' ),
        'add_new_item'       => __( 'Add New Site', 'your-text-domain' ),
        'edit_item'          => __( 'Edit Site', 'your-text-domain' ),
        'new_item'           => __( 'New Site', 'your-text-domain' ),
        'all_items'          => __( 'All Sites', 'your-text-domain' ),
        'view_item'          => __( 'View Site', 'your-text-domain' ),
        'search_items'       => __( 'Search Sites', 'your-text-domain' ),
        'not_found'          => __( 'No sites found', 'your-text-domain' ),
        'not_found_in_trash' => __( 'No sites found in the Trash', 'your-text-domain' ),
        'parent_item_colon'  => '',
        'menu_name'          => 'Sites'
    );

    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'rewrite'       => array('slug' => 'sites'),
        //'supports'      => array( 'title', 'editor', 'custom-fields' ),
        'supports'      => array( 'title', 'custom-fields' ),
        'has_archive'   => true,
        'menu_icon'     => 'dashicons-admin-multisite',
    );
    register_post_type( 'site', $args );
}
add_action('init', 'register_site_post_type');

// Custom columns
function add_site_custom_field_column($columns) {
    // Insert the custom field column after the 'title' column
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            // Add the custom field column after the 'title' column
            $new_columns['cust_no_column'] = __('Cust.#', 'your-text-domain');
            $new_columns['contact_column'] = __('Contact', 'your-text-domain');
            $new_columns['phone_column'] = __('Phone', 'your-text-domain');
        }
    }
    return $new_columns;
}
add_filter('manage_site_posts_columns', 'add_site_custom_field_column');

function add_sortable_site_custom_field_column($sortable_columns) {
    $sortable_columns['cust_no_column'] = 'cust_no';
    $sortable_columns['contact_column'] = 'contact';
    $sortable_columns['phone_column'] = 'phone';
    return $sortable_columns;
}
add_filter('manage_edit-site_sortable_columns', 'add_sortable_site_custom_field_column');

function display_site_custom_field_in_admin_list($column, $post_id) {
    if ($column === 'cust_no_column') {
        echo esc_html(get_post_meta( $post_id, 'cust_no', true));
    }
    if ($column === 'contact_column') {
        echo esc_html(get_post_meta( $post_id, 'contact', true));
    }
    if ($column === 'phone_column') {
        echo esc_html(get_post_meta( $post_id, 'phone', true));
    }
}
add_action('manage_site_posts_custom_column', 'display_site_custom_field_in_admin_list', 10, 2);

// Meta boxes
function add_site_image_metabox() {
    add_meta_box(
        'site_image_id',
        'Site Image',
        'site_image_content',
        'site',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'add_site_image_metabox');

// Function to check if the string is a valid URL
function isURL($str) {
    $pattern = '/^(http|https):\/\/[^ "]+$/';
    return preg_match($pattern, $str) === 1;
}

function site_image_content($post) {
    wp_nonce_field('site_image_nonce', 'site_image_nonce');
    $image_url = get_post_meta( $post->ID, 'image_url', true);
    ?>
    <div id="custom-image-container">
        <?php echo (isURL($image_url)) ? '<img src="' . esc_attr($image_url) . '" style="object-fit:cover; width:250px; height:250px;">' : '<a href="#" id="custom-image-href">Set image URL</a>'; ?>
    </div>
    <div id="image-url-dialog" style="display:none;">
        <fieldset>
            <label for="image-url">Image URL:</label>
            <textarea id="image-url" name="image_url" rows="3" style="width:99%;"><?php echo $image_url;?></textarea>
            <button id="set-image-url">Set</button>
        </fieldset>
    </div>
    <?php
}

function save_site_image_content($post_id) {
    if (!isset($_POST['site_image_nonce']) || !wp_verify_nonce($_POST['site_image_nonce'], 'site_image_nonce') || !current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    if (isset($_POST['image_url'])) {
        update_post_meta( $post_id, 'image_url', sanitize_text_field($_POST['image_url']));
    }
}
add_action('save_post', 'save_site_image_content');

function add_site_settings_metabox() {
    add_meta_box(
        'site_settings_id',
        'Site Settings',
        'site_settings_content',
        'site',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_site_settings_metabox');

function site_settings_content($post) {
    wp_nonce_field('site_settings_nonce', 'site_settings_nonce');
    $cust_no = esc_attr(get_post_meta( $post->ID, 'cust_no', true));
    $contact = esc_attr(get_post_meta( $post->ID, 'contact', true));
    $email = esc_attr(get_post_meta( $post->ID, 'email', true));
    $phone = esc_attr(get_post_meta( $post->ID, 'phone', true));
    $address = esc_attr(get_post_meta( $post->ID, 'address', true));
    $country = esc_attr(get_post_meta( $post->ID, 'country', true));
    $site_url = esc_attr(get_post_meta( $post->ID, 'site_url', true));
    ?>
    <label for="cust-no"> Cust No: </label>
    <input type="text" id="cust-no" name="cust_no" value="<?php echo $cust_no;?>" style="width:100%" >
    <label for="contact"> Contact: </label>
    <input type="text" id="contact" name="contact" value="<?php echo $contact;?>" style="width:100%" >
    <label for="email"> Email: </label>
    <input type="text" id="email" name="email" value="<?php echo $email;?>" style="width:100%" >
    <label for="phone"> Phone: </label>
    <input type="text" id="phone" name="phone" value="<?php echo $phone;?>" style="width:100%" >
    <label for="address"> Address: </label>
    <input type="text" id="address" name="address" value="<?php echo $address;?>" style="width:100%" >
    <label for="country"> Country: </label>
    <input type="text" id="country" name="country" value="<?php echo $country;?>" style="width:100%" >
    <label for="site-url"> Site URL: </label>
    <input type="text" id="site-url" name="site_url" value="<?php echo $site_url;?>" style="width:100%" >
    <?php
    // Run the import function when this script is executed
    //import_sites_from_csv();
    //import_sites_from_encona_csv();
    
}

function save_site_settings_content($post_id) {
    if (!isset($_POST['site_settings_nonce']) || !wp_verify_nonce($_POST['site_settings_nonce'], 'site_settings_nonce') || !current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    if (isset($_POST['cust_no'])) {
        update_post_meta( $post_id, 'cust_no', sanitize_text_field($_POST['cust_no']));
    }
    if (isset($_POST['contact'])) {
        update_post_meta( $post_id, 'contact', sanitize_text_field($_POST['contact']));
    }
    if (isset($_POST['email'])) {
        update_post_meta( $post_id, 'email', sanitize_text_field($_POST['email']));
    }
    if (isset($_POST['phone'])) {
        update_post_meta( $post_id, 'phone', sanitize_text_field($_POST['phone']));
    }
    if (isset($_POST['address'])) {
        update_post_meta( $post_id, 'address', sanitize_text_field($_POST['address']));
    }
    if (isset($_POST['country'])) {
        update_post_meta( $post_id, 'country', sanitize_text_field($_POST['country']));
    }
    if (isset($_POST['site_url'])) {
        update_post_meta( $post_id, 'site_url', sanitize_text_field($_POST['site_url']));
    }
}
add_action('save_post', 'save_site_settings_content');

function display_customer_csv() {

    // Call the function with the CSV file name
    //processCsvFromMediaLibrary('customer.csv');

    $file_url = 'https://encona.tw/wp-content/uploads/2024/01/customer.csv';
    $encona_file_url = 'https://encona.tw/wp-content/uploads/2024/01/encona.csv';
    

    // Download the CSV file
    $csv_data = file_get_contents($file_url);

    // Process the CSV data
    if ($csv_data !== false) {
        $lines = explode("\n", $csv_data);
    
        // Iterate through each CSV row
        foreach ($lines as $line) {
            $data = str_getcsv($line);
    
            // Process each column data
            foreach ($data as $column) {
                // Your processing logic here
                echo $column . ' ';
            }
    
            echo "<br>";
        }
    } else {
        echo 'Error downloading CSV file.';
    }

}

function import_sites_from_encona_csv() {
    // Specify the path to your CSV file
    $csv_file = 'https://encona.tw/wp-content/uploads/2024/01/encona.csv';

    // Fetch CSV content
    $csv_content = file_get_contents($csv_file);

    // Convert CSV to an array of rows
    $csv_rows = str_getcsv($csv_content, "\n");

    foreach ($csv_rows as $csv_row) {
        $data = str_getcsv($csv_row);

        // Extract data from the CSV columns
        $cust_no = isset($data[0]) ? $data[0] : '';
        $title = isset($data[1]) ? $data[1] : '';
        $item1 = isset($data[2]) ? $data[2] : '';
        $item2 = isset($data[3]) ? $data[3] : '';
        $contact = isset($data[4]) ? $data[4] : '';
        $email = isset($data[5]) ? $data[5] : '';
        $phone = isset($data[6]) ? $data[6] : '';
        $address = isset($data[7]) ? $data[7] : '';

        // Create post data
        $post_data = array(
            'post_title' => $title,
            'post_type' => 'site',
            // Add any additional post data here
        );

        // Insert the post
        $post_id = wp_insert_post($post_data);

        // Add custom fields (metadata)
        if ($post_id && $cust_no) {
            update_post_meta( $post_id, 'cust_no', $cust_no);
        }
        if ($post_id && $item1) {
            update_post_meta( $post_id, 'item1', $item1);
        }
        if ($post_id && $item2) {
            update_post_meta( $post_id, 'item2', $item2);
        }
        if ($post_id && $contact) {
            update_post_meta( $post_id, 'contact', $contact);
        }
        if ($post_id && $email) {
            update_post_meta( $post_id, 'email', $email);
        }
        if ($post_id && $phone) {
            update_post_meta( $post_id, 'phone', $phone);
        }
        if ($post_id && $address) {
            update_post_meta( $post_id, 'address', $address);
        }
    }
}

function import_sites_from_csv() {
    // Specify the path to your CSV file
    $csv_file = 'https://encona.tw/wp-content/uploads/2024/01/customer.csv';

    // Fetch CSV content
    $csv_content = file_get_contents($csv_file);

    // Convert CSV to an array of rows
    $csv_rows = str_getcsv($csv_content, "\n");

    foreach ($csv_rows as $csv_row) {
        $data = str_getcsv($csv_row);

        // Extract data from the CSV columns
        $cust_no = isset($data[0]) ? $data[0] : '';
        $title = isset($data[1]) ? $data[1] : '';
        $country = isset($data[2]) ? $data[2] : '';

        // Create post data
        $post_data = array(
            'post_title' => $title,
            'post_type' => 'site',
            // Add any additional post data here
        );

        // Insert the post
        $post_id = wp_insert_post($post_data);

        // Add custom fields (metadata)
        if ($post_id && $cust_no) {
            update_post_meta( $post_id, 'cust_no', $cust_no);
        }

        if ($post_id && $country) {
            update_post_meta( $post_id, 'country', $country);
        }
    }
}
