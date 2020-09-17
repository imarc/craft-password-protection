# Password Protection plugin for Craft CMS 3.x

This is a Craft CMS plugin that allows for password protecting any page in your CMS intuitively and without any hassle.

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require imarc/craft-password-protection

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Password Protection.


## Configuring Password Protection
#### Default Password
When you set a default password that value will be used as a default for any page where Lock Screen is enabled and the password field is left empty. When this setting it left empty and no password is provided for a lock screen then the lock screen will automatically be disabled.

#### Password Cookie Duration
This setting defines how long the user will be remembered for. The value entered is in seconds, so for example to get one day you would do 86400. Default is 1 day.

#### Template
This allows for defining of a custom login page. Simply create a template in your project where you have a form with a field that is named `password_protect` and then define the route for that template in the settings for password protect. For a template located in `tempalates/password-protect/login.twig` the path entered would be `password-protect/login` or `/password-protect/login`. Here is an example of a simple template: 
```
<form method="POST">
    <h1>{{ 'Enter Password'|t }}</h1>
    <div>
        {{ csrfInput() }}
        
        {% set message = craft.app.session.getFlash('protect_error') %}
        {% if message|length > 0 %}
            <div class="alert">
                <p class="error">{{ message }}</p>
            </div>
        {% endif %}
        
        <input type="password" name="protect_password">
        <input type="submit" value="{{ 'Submit'|t}}">
    </div>
</form>
```

## Using Password Protection

To start using Password Protection on a page simply login to the admin panel, then go to Entries and click the entry that you would like to protect. Once on the edit page on the right side of the screen you will see a lightswitch labeled 'Enable Password Protect'. Just toggle it on/off and enter a passphrase if you want that page protected. When you're done don't forget to click save in the top right and then you are ready to go!

Now when users go to that page they will get prompted with a default password page, or a custom password page if defined.
![Screenshot](resources/img/protect-screen.png)

#### Note
Once a user enters a password correctly, a cookie will be stored and they won't have to re-enter the passphrase until the cookie expires. Also any user that is logged in will automatically be granted access to ALL password protected pages. It's on the road map to allow customization of which users will be prompted on certain pages.

## Password Protection Roadmap

Some things to do, and ideas for potential features:

* Allow customization of which logged in users will be prompted for a password on certain pages

Brought to you by [Imarc](https://www.imarc.com/)
