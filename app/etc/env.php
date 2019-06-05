<?php
return [
    'backend' => [
        'frontName' => 'admin_zg'
    ],
    'crypt' => [
        'key' => 'e1d3ab632c93cb9c561d15400fedc3ba'
    ],
    'db' => [
        'table_prefix' => 'zg_',
        'connection' => [
            'default' => [
                'host' => '10.64.1.120',
                'dbname' => 'ZG_Produ',
                'username' => 'ZG_Produ_User',
                'password' => 'Zg_Produ_Password',
                'active' => '1'
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'production',
    'session' => [
        'save' => 'files'
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => 'e9f_'
            ],
            'page_cache' => [
                'id_prefix' => 'e9f_'
            ]
        ]
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1,
        'vertex' => 1
    ],
    'install' => [
        'date' => 'Thu, 11 Apr 2019 11:02:36 +0000'
    ]
];
