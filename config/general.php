<?php
/**
 * General Configuration
 *
 * All of your system's general configuration settings go in here. You can see a
 * list of the available settings in vendor/craftcms/cms/src/config/GeneralConfig.php.
 *
 * @see \craft\config\GeneralConfig
 */

use craft\config\GeneralConfig;
use craft\helpers\App;

return GeneralConfig::create()
    // Set the default week start day for date pickers (0 = Sunday, 1 = Monday, etc.)
    ->defaultWeekStartDay(1)
    // Prevent generated URLs from including "index.php"
    ->omitScriptNameInUrls()
    // Enable Dev Mode (see https://craftcms.com/guides/what-dev-mode-does)
    ->devMode(App::env('DEV_MODE') ?? false)
    // Allow administrative changes
    ->allowAdminChanges(App::env('ALLOW_ADMIN_CHANGES') ?? false)
    // Disallow robots
    ->disallowRobots(App::env('DISALLOW_ROBOTS') ?? false)
    
    // Front End Authentication
    ->useEmailAsUsername(true)
    // The URI unauthenticated users will be redirected to if they go a url that has requireLogin
    ->loginPath('bands/login')
    //The URI Craft should redirect users to after setting their password from the front end.
    ->setPasswordSuccessPath('bands/login') 
    //The URI that users without access to the control panel should be redirected to after verifying a new email address.
    ->verifyEmailSuccessPath('bands/login') 
    //The URI that users without access to the control panel should be redirected to after activating their account.
    ->activateAccountSuccessPath('bands/login?rockstar=welcome')
    //The path users should be redirected to after logging in from the front-end site.
    ->postLoginRedirect('/')
    //The path that users should be redirected to after logging out from the front-end site.
    ->postLogoutRedirect('/')
    //The URI to the page where users can request to change their password.
    ->setPasswordRequestPath('reset')
;
