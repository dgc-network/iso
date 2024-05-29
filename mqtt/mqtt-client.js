jQuery(document).ready(function($) {
    function fetchMqttMessages() {
        $.ajax({
            url: mqttClient.ajax_url,
            method: 'POST',
            data: {
                action: 'get_mqtt_messages',
                nonce: mqttClient.nonce
            },
            success: function(response) {
                $('#mqtt-messages').html(response);
            }
        });
    }

    // Poll the server every 5 seconds
    setInterval(fetchMqttMessages, 5000);

    // Fetch messages immediately on load
    fetchMqttMessages();
});
