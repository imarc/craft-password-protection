<?php
/**
 * Password Protection plugin for Craft CMS 3.x
 *
 * Password protect any page on the CMS.
 *
 * @link      imarc.com
 * @copyright Copyright (c) 2020 Imarc
 */

namespace imarc\passwordprotection\services;

use imarc\passwordprotection\PasswordProtection;
use imarc\passwordprotection\records\PasswordProtectionRecord;
use imarc\passwordprotection\models\PasswordProtectionModel;

use Craft;
use craft\base\Component;
use Exception;

/**
 * PasswordProtectionService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Imarc
 * @package   PasswordProtection
 * @since     1.0.0
 */
class PasswordProtectionService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function will handle the saving of passwordEnable and the password.
     *
     * @return mixed
     */
    public function updateEntryField($params) {
        $settings = PasswordProtection::$plugin->getSettings();

        $entryId = $params['entryId'] ?? null;
        $passwordProtectionEnabled = empty($params['imarc_passwordProtectionEnabled']) ? false : true;
        $password = $params['imarc_password'] ?? '';

        $data = [
            'entryId' => $entryId,
            'passwordProtectionEnabled' => $passwordProtectionEnabled,
            'password' => $password
        ];
        
        //Create/update the record
        PasswordProtectionRecord::updateRecord($data, PasswordProtectionRecord::findByEntryId($entryId));
    }
}