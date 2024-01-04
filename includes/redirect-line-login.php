<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include Line SDK
require 'vendor/autoload.php';

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

// Line API credentials
$channelId = '1657474604';
$channelSecret = '574d1d9ed505ae6a4cb99604da1bdd9f';
$redirectUri = 'https://iso-2beaa9.ingress-haven.ewp.live/';

// Initialize Line SDK
$httpClient = new CurlHTTPClient($channelSecret);
$lineBot = new LINEBot($httpClient, ['channelSecret' => $channelSecret]);

// Redirect users to Line Login page
function redirect_to_line_login() {
    global $lineBot, $channelId, $redirectUri;
    
    $state = bin2hex(random_bytes(16)); // Generate a random state
    $loginUrl = $lineBot->getLoginUrl($redirectUri, $state);
    
    // Redirect to Line Login
    wp_redirect($loginUrl);
    exit;
}

// Handle Line Login Callback
function handle_line_login_callback() {
    global $lineBot, $channelSecret, $redirectUri;
    
    $response = $lineBot->getOAuthToken($_GET['code']);
    $lineUserId = $response['userId'];

    // Use $lineUserId to authenticate or create a WordPress user
    // ...

    // Redirect or perform any other actions
    wp_redirect(home_url('/'));
    exit;
}

// Register custom actions
add_action('init', 'handle_line_login_callback');
add_action('wp_enqueue_scripts', 'enqueue_line_login_script');


// Assume you have Line API credentials
$channelId = 'YOUR_CHANNEL_ID';
$channelSecret = 'YOUR_CHANNEL_SECRET';
$accessToken = 'USER_ACCESS_TOKEN'; // User-specific access token

// Function to update Line user profile
function update_line_user_profile($lineUserId, $userData) {
    global $channelId, $channelSecret, $accessToken;

    // Make requests to the Line API to update user profile
    // You may need to use the Line SDK or make HTTP requests

    // Example using Line SDK
    $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channelSecret);
    $lineBot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channelSecret]);

    $response = $lineBot->linkToken($lineUserId, $accessToken);
    // Use the response to handle success or failure
}

// Hook into user registration or profile update in WordPress
function sync_user_to_line($user_id) {
    // Get user data from WordPress
    $user = get_user_by('ID', $user_id);
    
    // Map WordPress data to Line data
    $lineUserId = $user->get('line_user_id'); // Assume you have a custom field for Line User ID
    $lineUserData = array(
        'displayName' => $user->get('display_name'),
        'email' => $user->get('user_email'),
        // Map other fields as needed
    );

    // Update Line user profile
    update_line_user_profile($lineUserId, $lineUserData);
}

// Hook into user registration or profile update
add_action('user_register', 'sync_user_to_line');
add_action('profile_update', 'sync_user_to_line');
