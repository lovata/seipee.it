<?php namespace Inetis\DownloadManager\Components;

use ApplicationException;
use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Inetis\DownloadManager\Models\Category;
use Session;

class Password extends ComponentBase
{
    public $category = null;
    public $subCategories = null;
    public $categoryBasePath = null;

    public function componentDetails()
    {
        return [
            'name'        => 'inetis.downloadmanager::lang.password_form.name',
            'description' => 'inetis.downloadmanager::lang.password_form.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'page' => [
                'title'       => 'inetis.downloadmanager::lang.password_form.param.page.title',
                'description' => 'inetis.downloadmanager::lang.password_form.param.page.description',
                'default'     => null,
                'type'        => 'dropdown',
                'required'    => true,
            ],
        ];
    }

    public function getPageOptions()
    {
        return Page::all()
            ->filter(function ($item) {
                return array_has($item->settings, 'components.downloadManagerBrowser');
            })
            ->pluck('title', 'fileName')
            ->sortBy('title')
            ->toArray();
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws ApplicationException
     */
    public function onSubmitToken()
    {
        if (empty($token = input('token'))) {
            throw new ApplicationException(trans('inetis.downloadmanager::lang.password_form.errors.empty_token'));
        }

        if (!$category = Category::where('access_token', $token)->first()) {
            throw new ApplicationException(trans('inetis.downloadmanager::lang.password_form.errors.invalid_token'));
        }

        Session::put("downloadmanager-{$category->id}", $token);

        $pageName = $this->property('page');

        $url = $this->controller->pageUrl($pageName, [
            'path'  => $category->getPath(),
            'path?' => $category->getPath(),
        ]);

        return redirect($url);
    }
}
