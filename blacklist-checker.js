jQuery(document).ready(function($) {
    $('#blacklist-check-button').on('click', function(e) {
        e.preventDefault();

        var ipOrDomain = $('#ip-or-domain').val();

        $.ajax({
            url: blacklist_vars.ajaxurl,
            type: 'POST',
            data: {
                action: 'blacklist_check',
                security: blacklist_vars.nonce,
                ip_or_domain: ipOrDomain
            },
            beforeSend: function() {
                $('#blacklist-check-results').html('<p>' + blacklistTranslations.checking + '</p>');
            },
            success: function(response) {
                if (response.success) {
                    var results = response.data;
                    var html = '';

                    if (isIP(ipOrDomain)) {
                        html += '<h3>' + blacklistTranslations.ipCheckResult + ' ' + ipOrDomain + '</h3>';
                    } else {
                        html += '<h3>' + blacklistTranslations.domainCheckResult + ' ' + ipOrDomain + '</h3>';
                    }

                    html += '<ul>';
                    results.forEach(function(result) {
                        html += '<li><strong>' + result.service + '</strong>: ' + result.result + '</li>';
                    });
                    html += '</ul>';

                    $('#blacklist-check-results').html(html);
                } else {
                    $('#blacklist-check-results').html('<p>' + blacklistTranslations.error + ': ' + response.data + '</p>');
                }
            },
            error: function(xhr, status, error) {
                $('#blacklist-check-results').html('<p>' + blacklistTranslations.ajaxError + ': ' + error + '</p>');
            }
        });
    });

    // Function to check if input is IP address
    function isIP(input) {
        var ipRegex = /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/;
        return ipRegex.test(input);
    }
});
