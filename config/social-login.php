<?php

return [
    '*' => [
        'enableLogin' => true,
        'enableCpLogin' => false,
        'cpLoginTemplate' => '',
        'enableRegistration' => true,
        'userGroups' => ['3A1e42cdc3-f360-43ac-b612-8de6f081ad47'],
        'populateProfile' => true,
        'providers' => [],
        	// 'facebook' => [
            //     'enabled' => true,
            //     'loginEnabled' => true,
            //     'cpLoginEnabled' => true,

            //     // Matching registration fields (provider-side, Craft-side)
            //     'matchUserSource' => 'email',
            //     'matchUserDestination' => 'email',

            //     // Field mapping
            //     'fieldMapping' => [
            //         'username' => 'email',
            //         'email' => 'email',
            //         'field:myFieldHandle' => 'description',
            //         'field:text' => 'response',
            //     ],

            //     // OAuth settings
            //     'clientId' => '••••••••••••••••••••••••••••',
            //     'clientSecret' => '••••••••••••••••••••••••••••',

            //     // Add in any additional OAuth scopes
            //     'scopes' => [
            //          'user_birthday',
            //      ],

            //      // Add in any additional OAuth authorization options, used when redirecting
            //      // to the provider to start the OAuth authorization process
            //      'authorizationOptions' => [
            //         'extra' => 'value',
            //      ],

            //      // Add in any additional provider-based fields to map from
            //      // (depends on the provider API with what's available)
            //      'customProfileFields' => [
            //          'birthday',
            //      ],
            // ],
    	// ]
    ]
];