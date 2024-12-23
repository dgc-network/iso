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
            //curl_setopt($ch, CURLOPT_VERBOSE, true);
            
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            } else {
                $decoded_response = json_decode($response, true);

                if (isset($decoded_response['candidates'][0]['content']['parts'][0]['text'])) {
                    $generated_text = $decoded_response['candidates'][0]['content']['parts'][0]['text'];
                    //echo $this->convert_markdown_to_html($generated_text);
                    echo $this->convert_content_to_styled_html($generated_text);
                    //echo $generated_text;
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
            $content = preg_replace('/\#\#([^\#]+)\#\#/', '<h2>$1</h2>', $content); // Headings
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
        
            //return $content;

            // Combine the CSS and content in a div
            $styled_html = $css . "<div class='content ui-widget'>" . $content;
            $styled_html .= '<input type="text" id="ask-gemini" class="text ui-widget-content ui-corner-all" />';
            $styled_html .= "</div>";

            return $styled_html;
        }
/*        
        // Example usage
        $markdown_content = <<<EOT
        I cannot provide a complete ISO 9001 procedures manual and a list of electronic forms. This is because:
        * **Specificity:** An ISO 9001 system is highly specific to the organization. A generic template wouldn't be applicable to any given business. The procedures and forms need to reflect the unique processes, products, and services of a particular company.
        * **Copyright:** Providing a complete, ready-to-use document would be a copyright infringement.
        * **Legal Liability:** I cannot offer advice or templates that could be misconstrued as professional consulting, which carries legal implications.
        
        However, I can give you a *framework* and examples to guide you in creating your own ISO 9001 procedures manual and electronic form list. Remember to tailor everything to your specific organization.
        
        **ISO 9001 Procedures Manual Framework:**
        Your manual should cover all clauses of ISO 9001:2015 (or the latest revision). This includes, but isn't limited to:
        * **Context of the organization:** Understanding your organization, its stakeholders, and the scope of your QMS.
        * **Leadership:** Defining roles, responsibilities, and authorities within the QMS.
        * **Planning:** Setting objectives, risks, and opportunities.
        * **Support:** Resources, competence, awareness, communication, and documented information.
        * **Operation:** Planning and controlling processes, product realization.
        * **Performance evaluation:** Monitoring, measurement, analysis, and improvement.
        * **Improvement:** Corrective actions, preventive actions, and continual improvement.
        
        **Example Procedures:**
        * **Document Control Procedure:** How documents are created, reviewed, approved, distributed, and archived.
        * **Record Management Procedure:** How records are created, stored, retrieved, and disposed of.
        * **Internal Audit Procedure:** How internal audits are planned, conducted, and reported.
        * **Corrective Action Procedure:** How nonconformities are identified, investigated, and corrected.
        * **Preventive Action Procedure:** How potential problems are identified and prevented.
        * **Customer Complaint Handling Procedure:** How customer complaints are received, investigated, and resolved.
        * **Nonconforming Material Procedure:** Handling of materials that don't meet requirements.
        * **Management Review Procedure:** How management reviews are conducted.
        
        **Electronic Form List (Examples):**
        The specific forms you need will depend on your processes. Here are some examples:
        * **Nonconformity Report Form**
        * **Corrective Action Request Form**
        * **Preventive Action Request Form**
        * **Internal Audit Checklist**
        * **Calibration Record**
        * **Training Record**
        * **Document Change Request Form**
        * **Supplier Evaluation Form**
        * **Customer Complaint Form**
        * **Management Review Meeting Minutes**
        
        **Software:** Consider using software to manage your documents and forms electronically. Options include dedicated QMS software, document management systems, or even spreadsheet software (Excel) for simpler forms.
        
        Remember to consult ISO 9001:2015 (or the latest version) and seek professional advice from a qualified ISO 9001 consultant to ensure your system meets the standard requirements. This information is for guidance only and should not be considered a substitute for professional consultation.
        EOT;
        
        echo convert_content_to_styled_html($markdown_content);
*/        
        function convert_markdown_to_html($markdown) {
            // Convert special HTML characters
            $markdown = htmlspecialchars($markdown, ENT_NOQUOTES, 'UTF-8');
        
            // Convert headers
            $markdown = preg_replace('/^\* \*\*(.+?)\*\*: (.+)$/m', '<li style="margin-bottom: 10px;"><strong style="color: #000;">$1:</strong> $2</li>', $markdown); // Bold items with description
            $markdown = preg_replace('/^\* \*\*(.+?)\*\*/m', '<li style="margin-bottom: 10px;"><strong style="color: #000;">$1</strong></li>', $markdown); // Bold items without description
            $markdown = preg_replace('/^\* (.+)$/m', '<li style="margin-bottom: 10px;">$1</li>', $markdown); // Regular list items
        
            // Wrap <li> items in <ul> tags
            $markdown = preg_replace_callback(
                '/(<li.*?>.*?<\/li>)+/s',
                function ($matches) {
                    return '<ul style="margin: 10px 0 20px 20px;">' . $matches[0] . '</ul>';
                },
                $markdown
            );
        
            // Convert paragraphs (two newlines separate paragraphs)
            $markdown = preg_replace('/\n{2,}/', '</p><p>', $markdown); // Create paragraphs
            $markdown = '<p style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">' . $markdown . '</p>'; // Wrap the entire content with <p> tags
            $markdown = str_replace("\n", '<br>', $markdown); // Convert single newlines to <br> tags
        
            // Clean up nested lists
            $markdown = preg_replace('/<\/ul>\s*<ul>/', '', $markdown); // Remove redundant <ul> tags
        
            return $markdown;
        }
   }
    $gemini_api = new gemini_api();
}