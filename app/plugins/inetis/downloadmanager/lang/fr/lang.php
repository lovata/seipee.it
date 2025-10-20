<?php return [
    'plugin' => [
        'name'        => 'Gestionnaire de téléchargement',
        'description' => 'Affiche des fichiers à télécharger sur votre site, organisés par repertoires avec des droits d\'accès avancé',
        'menu_title'  => 'Fichiers',
    ],

    'category' => [
        'name'              => 'Nom',
        'description'       => 'Description',
        'parent'            => 'Repertoire parent',
        'no_parent'         => 'Repertoire racine',
        'files'             => 'Fichiers',
        'menu_label'        => 'Repertoires',
        'reorder'           => 'Réorganiser',
        'user_groups_label' => "Groupe d'utilisateurs autorisé à accéder à ce repertoire et à en télécharger les fichiers",
        'file_count'        => 'Nombre de fichiers',
        'subcategory'       => 'Sous-repertoire',
        'subcategories'     => 'Sous-repertoires',

        'access_rights' => "Droits d'accès",

        'access_rights_public'        => 'Public',
        'access_rights_inherit'       => 'Hérité',
        'access_rights_rainlab_group' => 'Groupe d\'utilisateur',
        'access_rights_token'         => 'Mot de passe',

        'access_rights_public_desc'        => "Public - tout le monde peux y accéder",
        'access_rights_inherit_desc'       => 'Hérité du repertoire parent',
        'access_rights_rainlab_group_desc' => "Limité aux groupes d'utilisateurs frontaux sélectionnés",
        'access_rights_token_desc'         => "Mot de passe",

        'access_token' => "Mot de passe",

        'tab_details' => 'Description',
        'tab_files'   => 'Fichiers',
        'tab_rights'  => 'Droits d\'accès',
        'tab_childs'  => 'Sous-repertoires',
    ],

    'browser' => [
        'name'        => 'Explorateur',
        'description' => 'Lister les fichiers et les repertoires récursivement',

        'param' => [
            'category' => [
                'title'       => 'Repertoire racine',
                'description' => 'Permet de définir un repertoire racine',
            ],

            'path' => [
                'title'       => 'Chemin',
                'description' => 'Chemin relatif depuis le repertoire racine',
            ],

            'displaysubcategories' => [
                'title'       => 'Afficher les sous-repertoires',
                'description' => 'Si cette option n\'est pas activée, seuls les fichiers du repertoire en cours seront affichés',
            ],
            'displaytitle'         => [
                'title'       => 'Afficher le nom de la catégorie',
                'description' => 'Affiche un titre avant la liste des fichiers',
            ],
            'displaybreadcrumb' => [
                'title'       => 'Afficher un fil d\'Ariane',
                'description' => 'Affiche un fil d\'Ariane permettant de remonter aux répertoires parents',
            ],
        ],
    ],

    'password_form' => [
        'name'          => "Formulaire de mot de passe",
        'description'   => 'Affiche un formulaire permettant de saisir un mot de passe et rediriger le visiteur dans le bon repertoire',
        'placeholder'   => "Votre mot de passe",
        'submit_button' => 'Rechercher',

        'param' => [
            'page' => [
                'title'       => 'Page contenant l\'explorateur',
                'description' => "Page vers laquelle l'utilisateur sera redirigé pour afficher le repertoire demandé",
            ],
        ],

        'errors' => [
            'empty_token'   => "Le mot de passe ne peux pas être vide",
            'invalid_token' => "Mot de passe invalide",
        ],
    ],

    'permissions' => [
        'access' => 'Accès',
    ],
];
