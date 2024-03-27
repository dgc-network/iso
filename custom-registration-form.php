<?php
/*
 * Template Name: Custom Registration Form
 */

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_registration"])) {
    // Retrieve form data
    $line_user_id = sanitize_text_field($_POST['line_user_id']);

    // Generate OTP
    $otp = generate_otp(); // Function to generate OTP (implementation not provided)

    // Send OTP to Line user
    send_otp_to_line($line_user_id, $otp); // Function to send OTP to Line user (implementation not provided)

    // Store OTP in session for verification
    session_start();
    $_SESSION['registration_otp'] = $otp;
    $_SESSION['line_user_id'] = $line_user_id;

    // Redirect to OTP verification page
    wp_redirect(home_url('/otp-verification')); // Redirect to OTP verification page (change URL as needed)
    exit();
}

// Display registration form
get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <h1 class="entry-title">Custom Registration Form</h1>
            </header><!-- .entry-header -->

            <div class="entry-content">
                <form id="registration-form" method="post" action="<?php the_permalink(); ?>">
                    <p>
                        <label for="line_user_id">Line User ID:</label>
                        <input type="text" id="line_user_id" name="line_user_id" required>
                    </p>
                    <p>
                        <input type="submit" name="submit_registration" value="Submit">
                    </p>
                </form>
            </div><!-- .entry-content -->
        </article><!-- #post-<?php the_ID(); ?> -->

    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>

<?php

// Function to generate a random OTP
function generate_otp() {
    // Generate a random 6-digit OTP
    return rand(100000, 999999);
}

// Function to save OTP to user meta
function save_otp_to_user_meta($user_identifier, $otp) {
    // Retrieve user by meta field and meta value
    $users = get_users(array(
        'meta_key'    => 'line_user_id',
        'meta_value'  => $user_identifier,
        'number'      => 1, // Limit to 1 user
        'count_total' => false // Improve performance
    ));

    // Check if user is found
    if (!empty($users)) {
        $user = reset($users); // Get the first user
        $expiration = time() + (15 * 60); // OTP expires after 15 minutes
        update_user_meta($user->ID, 'otp', $otp);
        update_user_meta($user->ID, 'otp_expiration', $expiration);
        return true; // OTP saved successfully
    }
    return false; // User not found
}
/*
// Function to save OTP to user meta
function save_otp_to_user_meta($user_identifier, $otp) {
    // Check if the user identifier is an email address
    $user = get_user_by('email', $user_identifier);
    if (!$user) {
        // If user is not found by email, try username
        $user = get_user_by('login', $user_identifier);
    }
    // If user is found, save OTP and expiration timestamp to user meta
    if ($user) {
        $expiration = time() + (15 * 60); // OTP expires after 15 minutes
        update_user_meta($user->ID, 'otp', $otp);
        update_user_meta($user->ID, 'otp_expiration', $expiration);
        return true; // OTP saved successfully
    }
    return false; // User not found
}
/*
// Function to save OTP to user meta
function save_otp_to_user_meta($user_id, $otp) {
    // Save OTP and expiration timestamp to user meta
    $expiration = time() + (15 * 60); // OTP expires after 15 minutes
    update_user_meta($user_id, 'otp', $otp);
    update_user_meta($user_id, 'otp_expiration', $expiration);
}
*/
// Function to send OTP to user (via email, SMS, etc.)
function send_otp_to_user($user_email, $otp) {
    // Send OTP to user's email
    $subject = 'Your One-Time Password';
    $message = 'Your one-time password is: ' . $otp;
    wp_mail($user_email, $subject, $message);
}

// Function to send OTP to user by Line user ID
function send_otp_to_line_user($line_user_id, $otp) {
    // Line API endpoint and access token (replace with your actual values)
    $api_endpoint = 'https://api.line.me/v2/bot/message/push';
    $access_token = 'YOUR_LINE_ACCESS_TOKEN';

    // Prepare the message payload
    $message = array(
        'to' => $line_user_id,
        'messages' => array(
            array(
                'type' => 'text',
                'text' => 'Your one-time password is: ' . $otp
            )
        )
    );

    // Convert the message to JSON
    $message_json = json_encode($message);

    // Set up the HTTP headers
    $headers = array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    );

    // Initialize cURL session
    $ch = curl_init();

    // Set the cURL options
    curl_setopt($ch, CURLOPT_URL, $api_endpoint);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $message_json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        // Handle the error
        $error_message = curl_error($ch);
        curl_close($ch);
        return "Error: $error_message";
    } else {
        // Close cURL session
        curl_close($ch);
        return "Message sent successfully!";
    }
}

// Function to verify OTP during login
function verify_otp_login($user_login, $user) {
    // Get submitted OTP
    $submitted_otp = isset($_POST['otp']) ? intval($_POST['otp']) : 0;

    // Get stored OTP and expiration timestamp from user meta
    $stored_otp = get_user_meta($user->ID, 'otp', true);
    $expiration = get_user_meta($user->ID, 'otp_expiration', true);

    // Check if submitted OTP matches stored OTP and is within expiration time
    if ($submitted_otp === $stored_otp && $expiration > time()) {
        // OTP is valid, log the user in
        wp_set_auth_cookie($user->ID, true);
        // Clear OTP and expiration from user meta
        delete_user_meta($user->ID, 'otp');
        delete_user_meta($user->ID, 'otp_expiration');
        // Redirect to home page or dashboard
        wp_redirect(home_url());
        exit;
    } else {
        // Invalid OTP, display error message
        $error = new WP_Error('invalid_otp', 'Invalid one-time password.');
        return $error;
    }
}

// Hook into the authentication process
add_action('wp_authenticate_user', function($user, $password) {
    // Check if OTP login form is submitted
    if (isset($_POST['otp_login'])) {
        // Verify OTP
        $result = verify_otp_login($user->user_login, $user);
        // If OTP verification fails, return error
        if (is_wp_error($result)) {
            return $result;
        }
    }
    // If OTP login form is not submitted or OTP verification succeeds, continue with normal authentication
    return $user;
}, 10, 2);
