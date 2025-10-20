<?php return [
    'plugin' => [
        'name'        => 'Download Manager',
        'description' => 'Display files to download on your website organized by folders with advanced access rights',
        'menu_title'  => 'Files',
    ],

    'category' => [
        'name'              => 'Name',
        'description'       => 'Description',
        'parent'            => 'Parent folder',
        'no_parent'         => 'Root folder',
        'files'             => 'Files',
        'menu_label'        => 'Folders',
        'reorder'           => 'Reorder',
        'user_groups_label' => 'User groups allowed to access to this folder and download files',
        'file_count'        => 'Number of files',
        'subcategory'       => 'Sub-folder',
        'subcategories'     => 'Sub-folders',

        'access_rights' => 'Access rights',

        'access_rights_public'        => 'Public',
        'access_rights_inherit'       => 'Inherited',
        'access_rights_rainlab_group' => 'User groups',
        'access_rights_token'         => 'Access token',

        'access_rights_public_desc'        => 'Public - everybody can access',
        'access_rights_inherit_desc'       => 'Inherit from parent folder',
        'access_rights_rainlab_group_desc' => 'Limited to selected frontend user groups',
        'access_rights_token_desc'         => 'Password',

        'access_token' => 'Password',

        'tab_details' => 'Description',
        'tab_files'   => 'Files',
        'tab_rights'  => 'Access rights',
        'tab_childs'  => 'Sub-folders',
    ],

    'browser' => [
        'name'        => 'Explorer',
        'description' => 'List files and folders recursively',

        'param' => [
            'category' => [
                'title'       => 'Root folder',
                'description' => 'You can define a custom root directory',
            ],

            'path' => [
                'title'       => 'Path',
                'description' => 'Relative path from root folder',
            ],

            'displaysubcategories' => [
                'title'       => 'Display sub-folders',
                'description' => 'If not enabled, only files in the current folder will be displayed',
            ],
            'displaytitle' => [
                'title'       => 'Display Category name',
                'description' => 'Show a title before the list of files ',
            ],
            'displaybreadcrumb' => [
                'title'       => 'Display breadcrumb',
                'description' => 'Show a breadcrumb to get back to the parent folders',
            ],
        ],
    ],

    'password_form' => [
        'name'          => 'Password form',
        'description'   => 'List files and folders recursively',
        'placeholder'   => 'Your password',
        'submit_button' => 'Find',

        'param' => [
            'page' => [
                'title'       => 'Browser page',
                'description' => "Page where the user will be redirected to display the requested folder",
            ],
        ],

        'errors' => [
            'empty_token'   => 'Password can\'t be empty',
            'invalid_token' => 'Invalid provided password',
        ],
    ],

    'permissions' => [
        'access' => 'Access',
    ],
];
