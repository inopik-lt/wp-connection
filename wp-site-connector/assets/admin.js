jQuery(document).ready(function($) {
    // Regenerate API key
    $('#regenerate-api-key').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm(wpSiteConnector.i18n.confirmRegenerate)) {
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
                    alert(wpSiteConnector.i18n.regenerateSuccess);
                } else {
                    alert(wpSiteConnector.i18n.regenerateFailed);
                }
            },
            error: function() {
                alert(wpSiteConnector.i18n.ajaxError);
            }
        });
    });
    
    // Auto-save indication
    $('form').on('submit', function() {
        $(this).find('input[type="submit"]').val('Saving...');
    });
});
