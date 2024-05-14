# Installation & Setup

## Installation
You can add the package to your project using Composer, or as a requirement in your `composer.json` file directly:

```shell
composer require verbb/auth
```

```json
"require": {
    "php": "^8.0.2",
    "craftcms/cms": "^4.0.0",
    "verbb/auth": "^1.0.0"
}
```

## Setup
To use the Auth module in your plugin, just call `Auth::getInstance()`.

```php
public function init(): void
{
    parent::init();

    \verbb\auth\Auth::getInstance()->getOAuth();
    \verbb\auth\Auth::getInstance()->getOAuth();

    // ...
}
```

### Migrations
Because the Auth plugin stores OAuth tokens in its own database table that's plugin-agnostic, you'll need to ensure that Auth's migration is run. In your plugin's `migrations\Install.php` file, add the following:

```php
class Install extends \craft\db\Migration
{
    public function safeUp(): bool
    {
        // Ensure that the Auth module kicks off setting up tables
        \verbb\auth\Auth::getInstance()->migrator->up();

        // Create any tables that your plugin requires
        $this->createTables();

        // ...
    }
}
```

This will ensure that the Auth database tables are created (if they don't already exist from another plugin requiring it), ready for you to add tokens to.

That completes the setup side of things!