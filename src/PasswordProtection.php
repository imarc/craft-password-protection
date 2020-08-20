<?php
/**
 * Password Protection plugin for Craft CMS 3.x
 *
 * Password protect any page on the CMS.
 *
 * @link      imarc.com
 * @copyright Copyright (c) 2020 Imarc
 */

namespace imarc\passwordprotection;

use imarc\passwordprotection\services\PasswordProtectionService as PasswordProtectionService;
use imarc\passwordprotection\variables\PasswordProtectionVariable;
use imarc\passwordprotection\models\Settings;
use imarc\passwordprotection\utilities\PasswordProtectionUtility as PasswordProtectionUtilityUtility;
use imarc\passwordprotection\SetPassword;
use imarc\passwordprotection\records\PasswordProtectionRecord;
use imarc\passwordprotection\assetbundles\passwordprotection\PasswordProtectionAsset;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\services\Utilities;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\web\View;
use craft\helpers\UrlHelper;
use craft\events\TemplateEvent;
use craft\base\Element;
use craft\elements\Entry;
use craft\events\RegisterElementActionsEvent;
use craft\events\RegisterTemplateRootsEvent;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author    Imarc
 * @package   PasswordProtection
 * @since     1.0.0
 *
 * @property  PasswordProtectionServiceService $passwordProtectionService
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class PasswordProtection extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * PasswordProtection::$plugin
     *
     * @var PasswordProtection
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * PasswordProtection::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE,
            function (TemplateEvent $event) {
                $url = UrlHelper::stripQueryString(Craft::$app->request->getUrl());
                $view = Craft::$app->getView();
                $request = Craft::$app->getRequest();
                $response = Craft::$app->getResponse();
                $session = Craft::$app->getSession();
                $currentUser = Craft::$app->getUser();

                //If the user is logged in allow them to see resource
                if ($currentUser->identity) {
                    return;
                }

                if (!isset($event->variables['entry'])) {
                    return;
                }
                
                $entryId = $event->variables['entry']->id;
                $passwordProtect = PasswordProtectionRecord::findByEntryId($entryId);
                //If password protection not enabled just return
                if (!$passwordProtect || $passwordProtect->passwordProtectionEnabled == false) {
                    return;
                }

                $params = $request->getBodyParams();

                if ($request->isPost && isset($params['protect_password'])) {
                    if ($params['protect_password'] == $passwordProtect->password) {
                        $this->setStoredPassword($url, $params['protect_password']);
                        $response->redirect($url)->send();
                        exit;
                    }

                    $session->setFlash(
                        "protect_error",
                        "We're sorry, but that password is invalid"
                    );

                    $response->redirect($url)->send();
                    exit;
                }

                
                if (!$this->isAuthorized($url, $passwordProtect->password)) {
                    $response = Craft::$app->getResponse();
                    $response->setStatusCode(403);
                    $view->registerAssetBundle(PasswordProtectionAsset::class);
                    $view->setTemplateMode($view::TEMPLATE_MODE_CP);
                    $response->data = $view->renderPageTemplate('password-protection/protect.twig');
                    $response->send();
                    exit;
                }
            }
        );

        // Register our utilities
        // Event::on(
        //     Utilities::class,
        //     Utilities::EVENT_REGISTER_UTILITY_TYPES,
        //     function (RegisterComponentTypesEvent $event) {
        //         $event->types[] = PasswordProtectionUtilityUtility::class;
        //     }
        // );

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('passwordProtection', PasswordProtectionVariable::class);
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_LOAD_PLUGINS,
            function () {
                $this->executeLogic();
            }
        );

/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'password-protection',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }


    /**
     * Run the main logic of the plugin.
     *
     * @return void
     */
    protected function executeLogic()
    {
        $request = Craft::$app->getRequest();

        //Saving the entry password
        if ($request->isCpRequest && $request->isActionRequest && in_array('entries', $request->getActionSegments())
        && in_array('save-entry', $request->getActionSegments())) {
            $service = new PasswordProtectionService();
            $service->updateEntryField($request->getBodyParams());
        }

        $view = Craft::$app->getView();
        $view->hook('cp.entries.edit.settings', [$this, 'renderEditSourceLink']);
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'password-protection/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }

    /**
     * @param array $context
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function renderEditSourceLink(array $context)
    {
        $entry = $context['entry'] ?? null;
        if ($entry) {
            return Craft::$app->getView()->renderTemplate('password-protection/password-field', [
                'entry' => $context,
                'settings' => $this->getSettings()
            ]);
        }
        return '';
    }

    /**
     * Set password for URL in a cookie
     */
    protected function setStoredPassword($url, $password)
    {
        // Create cookie object.
        $cookie = Craft::createObject([
            'class' => 'yii\web\Cookie',
            'name' => $this->makeCookieName($url),
            'httpOnly' => true,
            'value' => $this->makeCookieValue($password),
            'expire' => time() + 86400,
        ]);

        // Set cookie.
        Craft::$app->getResponse()->getCookies()->add($cookie);
    }

    /**
     * Get the stored password for the supplied URL
     */
    protected function getStoredPassword($url)
    {
        return Craft::$app->request->cookies->get($this->makeCookieName($url));
    }

    /**
     * Check if the password matches the stored password
     */
    protected function isAuthorized($url, $password)
    {
        $value = $this->getStoredPassword($url);

        return password_verify($password, $value);
    }

    /**
     * Hash a url for aa cookie name
     */
    protected function makeCookieName($url)
    {
        return 'protect_' . md5($url);
    }

    /**
     * Hash a password for a cookie value
     */
    protected function makeCookieValue($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}
