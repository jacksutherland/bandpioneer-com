<?php

$vendorDir = dirname(__DIR__);
$rootDir = dirname(dirname(__DIR__));

return array (
  'putyourlightson/craft-sendgrid' => 
  array (
    'class' => 'putyourlightson\\sendgrid\\Sendgrid',
    'basePath' => $vendorDir . '/putyourlightson/craft-sendgrid/src',
    'handle' => 'sendgrid',
    'aliases' => 
    array (
      '@putyourlightson/sendgrid' => $vendorDir . '/putyourlightson/craft-sendgrid/src',
    ),
    'name' => 'SendGrid',
    'version' => '2.0.1',
    'description' => 'SendGrid mailer adapter for Craft CMS.',
    'developer' => 'PutYourLightsOn',
    'developerUrl' => 'https://putyourlightson.com/',
    'documentationUrl' => 'https://putyourlightson.com/plugins/sendgrid',
    'changelogUrl' => 'https://raw.githubusercontent.com/putyourlightson/craft-sendgrid/v2/CHANGELOG.md',
  ),
  'craftcms/contact-form' => 
  array (
    'class' => 'craft\\contactform\\Plugin',
    'basePath' => $vendorDir . '/craftcms/contact-form/src',
    'handle' => 'contact-form',
    'aliases' => 
    array (
      '@craft/contactform' => $vendorDir . '/craftcms/contact-form/src',
    ),
    'name' => 'Contact Form',
    'version' => '3.0.0',
    'description' => 'Add a simple contact form to your Craft CMS site',
    'developer' => 'Pixel & Tonic',
    'developerUrl' => 'https://pixelandtonic.com/',
    'developerEmail' => 'support@craftcms.com',
    'documentationUrl' => 'https://github.com/craftcms/contact-form/blob/v2/README.md',
    'components' => 
    array (
      'mailer' => 'craft\\contactform\\Mailer',
    ),
  ),
  'bandpioneer/rockstar' => 
  array (
    'class' => 'bandpioneer\\rockstar\\Rockstar',
    'basePath' => 'bandpioneer/rockstar/src',
    'handle' => 'rockstar',
    'aliases' => 
    array (
      '@bandpioneer/rockstar' => $vendorDir . '/bandpioneer/rockstar/src',
    ),
    'name' => 'Band Pioneer',
    'version' => '1.0.0',
    'description' => 'Band Pioneer plugin for base website design properties.',
    'developer' => 'Band Pioneer',
    'developerUrl' => 'https://bandpioneer.com',
    'documentationUrl' => 'https://bandpioneer.com',
    'changelogUrl' => 'https://bandpioneer.com',
  ),
  'verbb/comments' => 
  array (
    'class' => 'verbb\\comments\\Comments',
    'basePath' => $vendorDir . '/verbb/comments/src',
    'handle' => 'comments',
    'aliases' => 
    array (
      '@verbb/comments' => $vendorDir . '/verbb/comments/src',
    ),
    'name' => 'Comments',
    'version' => '2.0.6',
    'description' => 'Add comments to your site.',
    'developer' => 'Verbb',
    'developerUrl' => 'https://verbb.io',
    'developerEmail' => 'support@verbb.io',
    'documentationUrl' => 'https://github.com/verbb/comments',
    'changelogUrl' => 'https://raw.githubusercontent.com/verbb/comments/craft-4/CHANGELOG.md',
  ),
  'craftcms/redactor' => 
  array (
    'class' => 'craft\\redactor\\Plugin',
    'basePath' => $vendorDir . '/craftcms/redactor/src',
    'handle' => 'redactor',
    'aliases' => 
    array (
      '@craft/redactor' => $vendorDir . '/craftcms/redactor/src',
    ),
    'name' => 'Redactor',
    'version' => '3.0.3',
    'description' => 'Edit rich text content in Craft CMS using Redactor by Imperavi.',
    'developer' => 'Pixel & Tonic',
    'developerUrl' => 'https://pixelandtonic.com/',
    'developerEmail' => 'support@craftcms.com',
    'documentationUrl' => 'https://github.com/craftcms/redactor/blob/v2/README.md',
  ),
);
