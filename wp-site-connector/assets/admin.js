jQuery(document).ready(function($) {
    // Regenerate API key
    $('#regenerate-api-key').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to regenerate the API key? The old key will stop working.')) {
            return;
        }
        
        $.ajax({
            url: wpSiteConnector.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wp_site_connector_regenerate_key',
                nonce: wpSiteConnector.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('input[name="wp_site_connector_api_key"]').val(response.data.api_key);
                    alert('API key regenerated successfully!');
                } else {
                    alert('Failed to regenerate API key.');
                }
            },
            error: function() {
                alert('An error occurred.');
            }
        });
    });
    
    // Auto-save indication
    $('form').on('submit', function() {
        $(this).find('input[type="submit"]').val('Saving...');
    });
});
