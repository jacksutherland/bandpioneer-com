<?php

$vendorDir = dirname(__DIR__);
$rootDir = dirname(dirname(__DIR__));

return array (
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
    'version' => '3.1.0',
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
    'version' => '3.0.0',
    'description' => 'SendGrid mailer adapter for Craft CMS.',
    'developer' => 'PutYourLightsOn',
    'developerUrl' => 'https://putyourlightson.com/',
    'documentationUrl' => 'https://putyourlightson.com/plugins/sendgrid',
    'changelogUrl' => 'https://raw.githubusercontent.com/putyourlightson/craft-sendgrid/develop/CHANGELOG.md',
  ),
  'craftcms/ckeditor' => 
  array (
    'class' => 'craft\\ckeditor\\Plugin',
    'basePath' => $vendorDir . '/craftcms/ckeditor/src',
    'handle' => 'ckeditor',
    'aliases' => 
    array (
      '@craft/ckeditor' => $vendorDir . '/craftcms/ckeditor/src',
    ),
    'name' => 'CKEditor',
    'version' => '4.0.4',
    'description' => 'Edit rich text content in Craft CMS using CKEditor.',
    'developer' => 'Pixel & Tonic',
    'developerUrl' => 'https://pixelandtonic.com/',
    'developerEmail' => 'support@craftcms.com',
    'documentationUrl' => 'https://github.com/craftcms/ckeditor/blob/master/README.md',
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
    'version' => '3.0.0-beta.3',
    'description' => 'Add comments to your site.',
    'developer' => 'Verbb',
    'developerUrl' => 'https://verbb.io',
    'developerEmail' => 'support@verbb.io',
    'documentationUrl' => 'https://github.com/verbb/comments',
    'changelogUrl' => 'https://raw.githubusercontent.com/verbb/comments/craft-5/CHANGELOG.md',
  ),
);
