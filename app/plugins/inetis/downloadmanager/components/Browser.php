<?php namespace Inetis\DownloadManager\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Inetis\DownloadManager\Models\Category;
use October\Rain\Database\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Browser extends ComponentBase
{
    public $category = null;
    public $subCategories = null;
    public $displayTitle = null;
    public $categoryBasePath = null;
    public $parentCategories = null;

    public function componentDetails()
    {
        return [
            'name'        => 'inetis.downloadmanager::lang.browser.name',
            'description' => 'inetis.downloadmanager::lang.browser.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'rootFolder' => [
                'title'       => 'inetis.downloadmanager::lang.browser.param.category.title',
                'description' => 'inetis.downloadmanager::lang.browser.param.category.description',
                'default'     => null,
                'type'        => 'dropdown',
            ],

            'path' => [
                'title'       => 'inetis.downloadmanager::lang.browser.param.path.title',
                'description' => 'inetis.downloadmanager::lang.browser.param.path.description',
                'default'     => '{{ :path }}',
                'type'        => 'string',
            ],

            'displaySubFolders' => [
                'title'       => 'inetis.downloadmanager::lang.browser.param.displaysubcategories.title',
                'description' => 'inetis.downloadmanager::lang.browser.param.displaysubcategories.description',
                'default'     => true,
                'type'        => 'checkbox',
            ],

            'displayTitle' => [
                'title'       => 'inetis.downloadmanager::lang.browser.param.displaytitle.title',
                'description' => 'inetis.downloadmanager::lang.browser.param.displaytitle.description',
                'default'     => true,
                'type'        => 'checkbox',
            ],

            'displayBreadcrumb' => [
                'title'       => 'inetis.downloadmanager::lang.browser.param.displaybreadcrumb.title',
                'description' => 'inetis.downloadmanager::lang.browser.param.displaybreadcrumb.description',
                'default'     => false,
                'type'        => 'checkbox',
            ],
        ];
    }

    public function getRootFolderOptions()
    {
        return (new Category)
            ->getAll()
            ->mapWithKeys(function ($item) {
                return [$item->id => str_repeat('–', $item->getLevel()) . ' ' . $item->name];
            })
            ->prepend(trans('inetis.downloadmanager::lang.category.no_parent'), 0)
            ->toArray();
    }

    public function onRun()
    {
        $this->category = $this->getCategory();
        $this->parentCategories = collect([]);
        $path = null;
        $this->displayTitle = $this->property('displayTitle');
        $rootCategoryId = $this->property('rootFolder');
        $displaySubCategories = $this->property('displaySubFolders', true);
        $displayBreadcrumb = $this->property('displayBreadcrumb', false);

        // No access to the current category
        if ($this->category && !$this->category->hasAccess()) {
            $this->category = null;

            return;
        }

        // Current category is not root
        if ($this->category) {
            // Get real path for the current category
            $this->categoryBasePath = $this->category->getBasePath($rootCategoryId);
            $path = $this->category->getPath($rootCategoryId);
        }

        // Redirect to the right Path if there is a path error
        if (!empty($current = $this->getPathParameter()) && $path != $current) {

            $url = $this->controller->pageUrl(null, [
                'path'  => $path,
                'path?' => $path,
            ]);

            return redirect($url, 302);
        }

        if ($displaySubCategories) {
            $this->subCategories = $this->category
                ? $this->category->getChildren()
                : Category::whereNull('parent_id')->get();

            $this->subCategories = $this->subCategories
                ->filter(function (Category $item) {
                    return $item->hasAccess(false) !== false;
                })
                ->each(function (Category $item) {
                    $item->setUrl(null, $this->controller, $this->categoryBasePath);
                });
        }

        // No breadcrumb if we are displaying the root category
        if ($this->category && $this->category->id != $rootCategoryId && $displayBreadcrumb) {
            $this->parentCategories = $this->getParentCategories($this->category)
                ->each(function (Category $item) {
                    $item->setUrl(null, $this->controller, $this->categoryBasePath);
                });
        }
    }

    /**
     * Get current category based on URL
     *
     * @return Category|null
     * @throws HttpException|ModelNotFoundException
     */
    protected function getCategory()
    {
        $categoryId = $this->getCategoryId();
        $rootCategory = $this->property('rootFolder', null);

        // No root and no sub category -> root
        if (empty($categoryId) && empty($rootCategory)) {
            return null;
        }

        // Custom root, no subcategory
        if (empty($categoryId)) {
            return Category::findOrFail($rootCategory);
        }

        // No root return sub
        if (empty($rootCategory)) {
            return Category::findOrFail($categoryId);
        }

        $category = Category::findOrFail($categoryId);

        // User try to access to a category that are not a child of the root
        if ($category->id != $rootCategory && !$category->isDescendantOf(Category::findOrFail($rootCategory))) {
            abort(403, 'You can\'t access a category that isn\'t a child of this page\'s root category');
        }

        return $category;
    }

    /**
     * Extract category ID from URL
     *
     * @return int|null
     */
    protected function getCategoryId()
    {
        $path = $this->getPathParameter();

        if (empty($path)) {
            return null;
        }

        $catSlug = ($pos = strrpos($path, '/')) === false
            ? $path
            : substr($path, $pos + 1);

        return (int)str_before($catSlug, '-');
    }

    /**
     * Given a category, fetch all parents of this category that can be displayed on this component instance.
     * If the component is configured with a root category, we must remove all categories above root.
     *
     * @param Category $childCategory
     *
     * @return Collection|Category[]
     */
    protected function getParentCategories(Category $childCategory)
    {
        $rootCategoryId = $this->property('rootFolder');
        $parentCategories = $childCategory->getParents();

        if ($rootCategoryId && $parentCategories->contains('id', $rootCategoryId)) {
            while ($parentCategories->first()->id != $rootCategoryId) {
                $parentCategories->shift();
            }
        }

        return $parentCategories;
    }

    /**
     * Get Path from URL
     *
     * @return string|null
     */
    protected function getPathParameter()
    {
        if (!empty($path = $this->property('path'))) {
            return $path;
        }

        $routerParam = $this->controller->getRouter()->getParameter('path?');

        if (!empty($routerParam) && $routerParam !== '*') {
            return $routerParam;
        }

        return null;
    }

}
