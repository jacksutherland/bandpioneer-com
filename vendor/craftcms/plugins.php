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
    'version' => '3.0.1',
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
    'version' => '3.0.4',
    'description' => 'Edit rich text content in Craft CMS using Redactor by Imperavi.',
    'developer' => 'Pixel & Tonic',
    'developerUrl' => 'https://pixelandtonic.com/',
    'developerEmail' => 'support@craftcms.com',
    'documentationUrl' => 'https://github.com/craftcms/redactor/blob/v2/README.md',
  ),
  'ether/sidebarentrytypes' => 
  array (
    'class' => 'ether\\sidebarentrytypes\\SidebarEntryTypes',
    'basePath' => $vendorDir . '/ether/sidebarentrytypes/src',
    'handle' => 'sidebarentrytypes',
    'aliases' => 
    array (
      '@ether/sidebarentrytypes' => $vendorDir . '/ether/sidebarentrytypes/src',
    ),
    'name' => 'Sidebar Entry Types',
    'version' => '2.0.1',
    'description' => 'Easily switch between entry types in entries section',
    'developer' => 'Ether Creative',
    'developerUrl' => 'https://ethercreative.co.uk',
    'documentationUrl' => 'https://github.com/ethercreative/sidebar-entrytypes',
    'changelogUrl' => 'https://github.com/ethercreative/sidebar-entrytypes/blob/master/CHANGELOG.md',
    'hasCpSettings' => false,
    'hasCpSection' => false,
  ),
  'ether/logs' => 
  array (
    'class' => 'ether\\logs\\Logs',
    'basePath' => $vendorDir . '/ether/logs/src',
    'handle' => 'logs',
    'aliases' => 
    array (
      '@ether/logs' => $vendorDir . '/ether/logs/src',
    ),
    'name' => 'Logs',
    'version' => '4.0.0',
    'schemaVersion' => '3.0.0',
    'description' => 'Access logs from the CP',
    'developer' => 'Ether Creative',
    'developerUrl' => 'https://ethercreative.co.uk',
  ),
  'nystudio107/craft-instantanalytics-ga4' => 
  array (
    'class' => 'nystudio107\\instantanalyticsGa4\\InstantAnalytics',
    'basePath' => $vendorDir . '/nystudio107/craft-instantanalytics-ga4/src',
    'handle' => 'instant-analytics-ga4',
    'aliases' => 
    array (
      '@nystudio107/instantanalyticsGa4' => $vendorDir . '/nystudio107/craft-instantanalytics-ga4/src',
    ),
    'name' => 'Instant Analytics GA4',
    'version' => '4.0.0',
    'description' => 'Instant Analytics brings full Google GA4 server-side analytics support to your Twig templates and automatic Craft Commerce integration',
    'developer' => 'nystudio107',
    'developerUrl' => 'https://nystudio107.com',
    'documentationUrl' => 'https://nystudio107.com/docs/instant-analytics-ga4/',
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
    'version' => '2.0.8',
    'description' => 'Add comments to your site.',
    'developer' => 'Verbb',
    'developerUrl' => 'https://verbb.io',
    'developerEmail' => 'support@verbb.io',
    'documentationUrl' => 'https://github.com/verbb/comments',
    'changelogUrl' => 'https://raw.githubusercontent.com/verbb/comments/craft-4/CHANGELOG.md',
  ),
);
