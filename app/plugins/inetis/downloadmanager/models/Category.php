<?php namespace Inetis\DownloadManager\Models;

use Illuminate\Support\Collection;
use Model;
use RainLab\User\Facades\Auth;
use RainLab\User\Models\UserGroup as RainLabUserGroup;
use Session;
use System\Models\File;

/**
 * Model
 */
class Category extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\NestedTree;

    const ACCESS_RIGHT_INHERIT = 0;
    const ACCESS_RIGHT_PUBLIC = 1;
    const ACCESS_RIGHT_RAINLAB_GROUP = 2;
    const ACCESS_RIGHT_TOKEN = 3;

    /**
     * @var bool Disable timestamps
     */
    public $timestamps = false;

    /**
     * @var array Validation
     */
    public $rules = [
        'name'         => 'required',
        'userGroups'   => 'required_if:access_rights,2',
        'access_token' => 'required_if:access_rights,3|unique:inetis_downloadmanager_categories',
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'inetis_downloadmanager_categories';

    /**
     * @var array Relation Attach Many
     */
    public $attachMany = [
        'files' => [File::class, 'public' => false],
    ];

    public $belongsToMany = [];

    /**
     * @var array A temp cache for avoid to calc access rights multi time
     */
    protected static $accessRightsCache = [];

    /**
     * Category constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        if ($this->isRainLabUsersAvailable()) {
            $this->belongsToMany = [
                'userGroups' => [RainLabUserGroup::class, 'table' => 'inetis_downloadmanager_categories_user_groups'],
            ];
        }

        parent::__construct($attributes);
    }

    //
    // Events
    //

    public function beforeSave()
    {
        if (!$this->isRainLabUsersAvailable()) {
            unset($this->userGroups);
        }
    }

    public function filterFields($fields, $context = null)
    {
        if (!$this->isRainLabUsersAvailable()) {
            $fields->userGroups->hidden = true;
        }
    }

    //
    // Attributes
    //

    public function getFileCountAttribute()
    {
        return $this->files()->count();
    }

    public function getAccessRightNameAttribute()
    {
        $langString = str_replace('_desc', '',
            array_get($this->getAccessRightsOptions(), $this->access_rights, '')
        );

        return trans($langString);
    }

    //
    // Getters
    //

    public function getSlug()
    {
        return $this->id . '-' . str_slug($this->name);
    }

    public function getAccessRightsOptions()
    {
        $options = [
            static::ACCESS_RIGHT_PUBLIC => 'inetis.downloadmanager::lang.category.access_rights_public_desc',
            static::ACCESS_RIGHT_TOKEN  => 'inetis.downloadmanager::lang.category.access_rights_token_desc',
        ];

        if ($this->isRainLabUsersAvailable()) {
            $options[static::ACCESS_RIGHT_RAINLAB_GROUP] = 'inetis.downloadmanager::lang.category.access_rights_rainlab_group_desc';
        }

        if ($this->isChild() || input('_relation_field')) {
            $options[static::ACCESS_RIGHT_INHERIT] = 'inetis.downloadmanager::lang.category.access_rights_inherit_desc';
        }

        ksort($options);

        return $options;
    }

    /**
     * Return the category path
     *
     * e.g. For a category download
     *      home/documents/3-downloads
     *
     * @param int|null $rootId Define a custom root directory
     *
     * @return string
     */
    public function getPath($rootId = null)
    {
        $parentsPath = $this->getParentsSlugs($rootId)->toArray();

        return empty($parentsPath)
            ? $this->getSlug()
            : implode('/', $parentsPath) . '/' . $this->getSlug();
    }

    /**
     * Return the category base path
     *
     * Useful for childs listing, only need to append /#-child-slug for generate each
     * (instead to recall getPath any time)
     *
     * e.g. For a category download
     *      home/documents/downloads
     *
     * @param int|null $rootId Define a custom root directory
     *
     * @return string
     */
    public function getBasePath($rootId = null)
    {
        if ($this->id == $rootId) {
            return;
        }

        $path = $this->getParentsSlugs($rootId)->toArray();

        $path[] = str_slug($this->name);

        return implode('/', $path);
    }

    //
    // Setters
    //

    /**
     * Sets the "url" attribute with a URL to this object
     *
     * @param string                  $pageName
     * @param \Cms\Classes\Controller $controller
     * @param null                    $parentPath
     *
     * @return string
     */
    public function setUrl($pageName, $controller, $parentPath = null)
    {
        $path = empty($parentPath) ? $this->getSlug() : $parentPath . '/' . $this->getSlug();

        return $this->url = $controller->pageUrl($pageName, [
            'path'  => $path,
            'path?' => $path,
        ]);
    }

    //
    // Checkers
    //

    public function accessRightsInherit()
    {
        return $this->access_rights == static::ACCESS_RIGHT_INHERIT;
    }

    public function accessRightsPublic()
    {
        return $this->access_rights == static::ACCESS_RIGHT_PUBLIC;
    }

    public function accessRightsRainLabGroup()
    {
        return $this->access_rights == static::ACCESS_RIGHT_RAINLAB_GROUP;
    }

    public function accessRightsToken()
    {
        return $this->access_rights == static::ACCESS_RIGHT_TOKEN;
    }

    /**
     * @param bool $recursive
     *
     * @return bool|null
     */
    public function hasAccess($recursive = true)
    {
        if (isset(static::$accessRightsCache[$this->id])) {
            return static::$accessRightsCache[$this->id];
        }

        if ($this->accessRightsPublic()) {
            return static::$accessRightsCache[$this->id] = true;
        }

        if ($this->accessRightsRainLabGroup()) {
            return static::$accessRightsCache[$this->id] = $this->userGroups
                ->pluck('id')
                ->intersect($this->getCurrentUserGroups())
                ->isNotEmpty();
        }

        if ($this->accessRightsToken()) {
            return static::$accessRightsCache[$this->id] = (
                !empty($this->access_token) && Session::get("downloadmanager-{$this->id}") == $this->access_token
            );
        }

        // Avoid a loop
        if (!$recursive) {
            return null;
        }

        foreach ($this->getParents()->reverse() as $category) {
            if (!is_null($access = $category->hasAccess(false))) {
                return $access;
            }
        }

        // By default no access
        return false;
    }

    //
    // Protected
    //

    /**
     * @param $rootId
     *
     * @return Collection
     */
    protected function getParentsSlugs($rootId)
    {
        $parentsPath = $this->parents()->pluck('name', 'id');

        // If a parent ID are set, get relative path from him
        if ($rootId) {
            $key = $parentsPath->keys()->search($rootId);
            $parentsPath = $parentsPath->slice($key + 1);
        }

        return $parentsPath->transform(function ($item) {
            return str_slug($item);
        });
    }

    /**
     * Return list of groups ID of the current visitor
     *
     * @return array
     */
    protected function getCurrentUserGroups()
    {
        if (!class_exists('Auth') || !Auth::check()) {
            return collect();
        }

        return Auth::getUser()->groups->pluck('id');
    }

    protected function isRainLabUsersAvailable()
    {
        return class_exists(RainLabUserGroup::class);
    }
}
