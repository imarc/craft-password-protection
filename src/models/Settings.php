<?php
/**
 * Password Protection plugin for Craft CMS 3.x
 *
 * Password protect any page on the CMS.
 *
 * @link      imarc.com
 * @copyright Copyright (c) 2020 Imarc
 */

namespace Imarc\Craft\PasswordProtection\Models;

use Craft;
use craft\base\Model;

/**
 * PasswordProtection Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Imarc
 * @package   PasswordProtection
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some field model attribute
     *
     * @var string
     */
    public $defaultPassword = NULL;
    public $passwordCookieDuration = 86400;
    public $template = NULL;

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['defaultPassword', 'string'],
            ['defaultPassword', 'default', 'value' => NULL],
            ['passwordCookieDuration', 'integer'],
            ['passwordCookieDuration', 'required'],
            ['passwordCookieDuration', 'default', 'value' => 86400],
            ['template', 'string'],
            ['template', 'default', 'value' => NULL],
        ];
    }

    public function getDefaultPassword() {
        return $this->defaultPassword;
    }

    public function getPasswordCookieDuration() {
        return $this->passwordCookieDuration;
    }

    public function getTemplate() {
        return $this->template;
    }
}
