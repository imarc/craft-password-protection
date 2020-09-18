/**
 * Password Protection plugin for Craft CMS
 *
 * PageProtection Field JS
 *
 * @author    Imarc
 * @copyright Copyright (c) 2020 Imarc
 * @link      imarc.com
 * @package   PasswordProtection
 * @since     1.0.0
 */
$("#imarc-enabled-password-protect").click(function() {
    var display = $('input[name="imarc.passwordProtectionEnabled"]').val() ? 'block' : 'none';

    $('#imarc-password-field').css('display', display);
});
