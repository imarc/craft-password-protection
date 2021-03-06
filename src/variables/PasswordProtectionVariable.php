<?php
/**
 * Password Protection plugin for Craft CMS 3.x
 *
 * Password protect any page on the CMS.
 *
 * @link      imarc.com
 * @copyright Copyright (c) 2020 Imarc
 */

namespace Imarc\Craft\PasswordProtection\variables;

use Imarc\Craft\PasswordProtection\PasswordProtection;
use Imarc\Craft\PasswordProtection\records\PasswordProtectionRecord;

use craft\helpers\DateTimeHelper;

/**
 * Password Protection Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.passwordProtection }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Imarc
 * @package   PasswordProtection
 * @since     1.0.0
 */
class PasswordProtectionVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.passwordProtection.exampleVariable }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.passwordProtection.exampleVariable(twigValue) }}
     *
     * @param null $optional
     * @return string
     */
    public function findPasswordProtectionByEntry($entry = null)
    {
        if ($entry) {
            return PasswordProtectionRecord::findByEntryId($entry->getId());
        }

        return $entry;
    }

    /**
     * Get the Password Cookie Duration and format it for human readability.
     *
     * @return void
     */
    public function getFormatedCookieDuration()
    {
        $settings = PasswordProtection::$plugin->getSettings();

        return DateTimeHelper::secondsToHumanTimeDuration($settings->getPasswordCookieDuration());
    }
}
