<?php
/**
 * Copyright 2022 dgc.network
 *
 * dgc.network licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!class_exists('gemini_api')) {
    class gemini_api {

        private $gemini_api_key;
    
        public function __construct($gemini_api_key='') {
            add_action('admin_init', array( $this, 'gemini_register_settings' ) );
            $this->gemini_api_key = get_option('gemini_api_key');
            $current_user_id = get_current_user_id();
            $this->gemini_api_key = get_user_meta($current_user_id, 'gemini_api_key', true);
        }
    
        function gemini_register_settings() {
            // Register gemini section
            add_settings_section(
                'gemini-section-settings',
                'Gemini Settings',
                array( $this, 'gemini_section_settings_callback' ),
                'web-service-settings'
            );
        
            // Register fields for Open AI section
            add_settings_field(
                'gemini_api_key',
                'API KEY',
                array( $this, 'gemini_api_key_callback' ),
                'web-service-settings',
                'gemini-section-settings'
            );
            register_setting('web-service-settings', 'gemini_api_key');
        }
        
        function gemini_section_settings_callback() {
            echo '<p>Settings for Gemini.</p>';
        }
        
        function gemini_api_key_callback() {
            $value = get_option('gemini_api_key');
            echo '<input type="text" id="gemini_api_key" name="gemini_api_key" style="width:100%;" value="' . esc_attr($value) . '" />';
        }

        public function generate_content($userMessage) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $this->gemini_api_key;
            
            $data = array(
              "contents" => array(
                array(
                  "parts" => array(
                    array(
                      "text" => $userMessage,
                    )
                  )
                )
              )
            );
            
            $json_data = json_encode($data);
            
            $ch = curl_init($url);
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Set to true to return the response
            
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            } else {
                $decoded_response = json_decode($response, true);

                if (isset($decoded_response['candidates'][0]['content']['parts'][0]['text'])) {
                    $generated_text = $decoded_response['candidates'][0]['content']['parts'][0]['text'];
                    echo $this->convert_content_to_styled_html($generated_text);
                } else {
                    echo "Failed to generate text.";
                }
            }
            curl_close($ch);

        }
        
        function convert_content_to_styled_html($content) {
            // Define the inline CSS
            $css = "
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 20px;
                    background-color: #f9f9f9;
                }
                .content {
                    max-width: 800px;
                    margin: auto;
                    background: #fff;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                h2 {
                    color: #0056b3;
                    border-bottom: 2px solid #ddd;
                    padding-bottom: 5px;
                }
                ul {
                    margin: 10px 0 20px 20px;
                    padding-left: 20px;
                }
                ul li {
                    margin-bottom: 10px;
                }
                strong {
                    color: #000;
                }
                em {
                    color: #0056b3;
                }
            </style>
            ";
        
            // Replace markdown-like elements with HTML
            $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
            // Headings (## Heading ## or ## Heading)
            $content = preg_replace('/^\#\#\s*(.+?)\s*$/um', '<h2>$1</h2>', $content);
            $content = preg_replace('/\*\*([^\*]+)\*\*/', '<strong>$1</strong>', $content); // Bold
            $content = preg_replace('/\*([^\*]+)\*/', '<em>$1</em>', $content);           // Italic
            $content = preg_replace('/^\s*\*\s(.+)$/m', '<li>$1</li>', $content);         // List items
            $content = preg_replace_callback(
                '/(<li>.*?<\/li>)+/s',
                function ($matches) {
                    return '<ul>' . $matches[0] . '</ul>';
                },
                $content
            );
            $content = preg_replace('/\n{2,}/', '</p><p>', $content); // Paragraphs
            $content = '<p>' . $content . '</p>'; // Wrap in paragraph tags
            $content = str_replace("\n", '<br>', $content); // Line breaks
            $content = preg_replace('/<\/ul>\s*<ul>/', '', $content); // Clean nested lists
        
            // Combine the CSS and content in a div
            $styled_html = $css . "<div class='content ui-widget'>" . $content;
            $styled_html .= '<div style="padding:10px; border:solid; border-radius:1.5rem;"><input type="text" id="ask-gemini" placehold="問問 Gemini" class="text ui-widget-content ui-corner-all" /></div>';
            $styled_html .= "</div>";

            return $styled_html;
        }
    }
    $gemini_api = new gemini_api();
}