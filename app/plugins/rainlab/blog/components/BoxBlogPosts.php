<?php namespace RainLab\Blog\Components;

use Lang;
use Redirect;
use BackendAuth;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use October\Rain\Database\Model;
use October\Rain\Database\Collection;
use RainLab\Blog\Models\Post as BlogPost;
use RainLab\Blog\Models\Category as BlogCategory;
use RainLab\Blog\Models\Settings as BlogSettings;
use RatMD\BlogHub\Models\Tag as Tags;

class BoxBlogPosts extends ComponentBase
{
    /**
     * A collection of posts to display
     *
     * @var Collection
     */
    public $posts;

    /**
     * Parameter to use for the page number
     *
     * @var string
     */
    public $pageParam;

    /**
     * If the post list should be filtered by a category, the model to use
     *
     * @var Model
     */
    public $category;

    /**
     * Message to display when there are no messages
     *
     * @var string
     */
    public $noPostsMessage;

    /**
     * Reference to the page name for linking to posts
     *
     * @var string
     */
    public $postPage;

    /**
     * Reference to the page name for linking to categories
     *
     * @var string
     */
    public $categoryPage;

    /**
     * If the post list should be ordered by another attribute
     *
     * @var string
     */
    public $sortOrder;

    public $pageNumber;

    public $categoryFilter;

    public $postsPerPage;

    public $exceptPost;

    public $exceptCategories;
    public $categoryId;
    public $tagId;
    public $tagPage;

    public function componentDetails()
    {
        return [
            'name'        => 'Visualizza POSTS Nel BOX',
            'description' => 'Visualizza POSTS Nel Box'
        ];
    }

    public function defineProperties()
    {
        return [
            /*'pageNumber' => [
                'title'       => 'rainlab.blog::lang.settings.posts_pagination',
                'description' => 'rainlab.blog::lang.settings.posts_pagination_description',
                'type'        => 'string',
                'default'     => '{{ :page }}',
            ],
            'categoryFilter' => [
                'title'       => 'rainlab.blog::lang.settings.posts_filter',
                'description' => 'rainlab.blog::lang.settings.posts_filter_description',
                'type'        => 'string',
                'default'     => '',
            ],
            'postsPerPage' => [
                'title'             => 'rainlab.blog::lang.settings.posts_per_page',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'rainlab.blog::lang.settings.posts_per_page_validation',
                'default'           => '10',
            ],
            */
            'noPostsMessage' => [
                'title'             => 'rainlab.blog::lang.settings.posts_no_posts',
                'description'       => 'rainlab.blog::lang.settings.posts_no_posts_description',
                'type'              => 'string',
                'default'           => Lang::get('rainlab.blog::lang.settings.posts_no_posts_default'),
                'showExternalParam' => false,
            ],
            /*'sortOrder' => [
                'title'       => 'rainlab.blog::lang.settings.posts_order',
                'description' => 'rainlab.blog::lang.settings.posts_order_description',
                'type'        => 'dropdown',
                'default'     => 'published_at desc',
            ],
            'categoryPage' => [
                'title'       => 'rainlab.blog::lang.settings.posts_category',
                'description' => 'rainlab.blog::lang.settings.posts_category_description',
                'type'        => 'dropdown',
                'default'     => 'blog/category',
                'group'       => 'rainlab.blog::lang.settings.group_links',
            ],
            'postPage' => [
                'title'       => 'rainlab.blog::lang.settings.posts_post',
                'description' => 'rainlab.blog::lang.settings.posts_post_description',
                'type'        => 'dropdown',
                'default'     => 'blog/post',
                'group'       => 'rainlab.blog::lang.settings.group_links',
            ],
            'exceptPost' => [
                'title'             => 'rainlab.blog::lang.settings.posts_except_post',
                'description'       => 'rainlab.blog::lang.settings.posts_except_post_description',
                'type'              => 'string',
                'validationPattern' => '^[a-z0-9\-_,\s]+$',
                'validationMessage' => 'rainlab.blog::lang.settings.posts_except_post_validation',
                'default'           => '',
                'group'             => 'rainlab.blog::lang.settings.group_exceptions',
            ],
            'exceptCategories' => [
                'title'             => 'rainlab.blog::lang.settings.posts_except_categories',
                'description'       => 'rainlab.blog::lang.settings.posts_except_categories_description',
                'type'              => 'string',
                'validationPattern' => '^[a-z0-9\-_,\s]+$',
                'validationMessage' => 'rainlab.blog::lang.settings.posts_except_categories_validation',
                'default'           => '',
                'group'             => 'rainlab.blog::lang.settings.group_exceptions',
            ],*/
        ];
    }

    

    public function getSortOrderOptions()
    { 
        $options = BlogPost::$allowedSortingOptions;

        foreach ($options as $key => $value) {
            $options[$key] = Lang::get($value);
        }

        return $options;
    }

    public function onRun()
    {

        if ($this->methodExists('getBoxesPage')) {
            $boxesPage = $this->getBoxesPage();
           
        }

        // Access the Boxes Box where the component is rendered on.
        if ($this->methodExists('getBoxesBox')) {
            // required: tag o category
            $categoryId='';
            $tagId='';
            $box = $this->getBoxesBox();
            if($box){
                if($box->blog_post_number){
                    $this->postsPerPage=$box->blog_post_number;
                    //dd($this->postsPerPage);
                }
                if($box->blog_post_category_id){
                    
                    $this->categoryId=$box->blog_post_category_id;
                    
                }
                if($box->blog_post_tag_id){
                    $this->tagId=$box->blog_post_tag_id;
                }
                if($box->blog_sort_order){
                    $this->sortOrder=$box->blog_sort_order;
                }
                $this->tagPage=$this->page["tagPage"]=$box->blog_post_tag_page_url;
                $this->postPage=$this->page["postPage"]=$box->blog_post_page_url;
                $this->categoryPage=$this->page["categoryPage"]=$box->blog_post_category_page_url;
            }
           
        }
        $this->prepareVars();

        $this->category = $this->page['category'] = $this->loadCategory();
        
        
        $this->posts = $this->page['posts'] = $this->listPosts();
       
        
        /*
         * If the page number is not valid, redirect
         */
        /*if ($pageNumberParam = $this->paramName('pageNumber')) {
            $currentPage = $this->property('pageNumber');

            if ($currentPage > ($lastPage = $this->posts->lastPage()) && $currentPage > 1) {
                return Redirect::to($this->currentPageUrl([$pageNumberParam => $lastPage]));
            }
        }*/
    }

    protected function prepareVars()
    {
        $this->pageParam = 'page';
        $this->noPostsMessage = $this->page['noPostsMessage'] = $this->property('noPostsMessage');

        /*
         * Page links
         */
       
        
    }

    protected function listPosts()
    {
       
        $category = $this->category ? $this->category->id : null;
        $categorySlug = $this->category ? $this->category->slug : null;
        

        /*
         * List all the posts, eager load their categories
         */
        $isPublished = !$this->checkEditor();

        $postsQuery = BlogPost::with(['categories', 'featured_images', 'ratmd_bloghub_tags']);

        if ($this->tagId && !empty($this->tagId)) {
        // Se un tag è selezionato, mostra solo i post con quel tag
            $tagId = $this->tagId;
        $postsQuery->whereHas('ratmd_bloghub_tags', function ($q) use ($tagId) {
            $q->where('slug', $tagId);
        });
        }
        /*
     * Controllo sull'ordinamento per evitare valori non validi
     */
        $allowedSortingOptions = BlogPost::$allowedSortingOptions;
        $sortOrder = in_array($this->sortOrder, array_keys($allowedSortingOptions))
        ? $this->sortOrder
        : 'published_at desc'; // Default: ultimi post pubblicati

        

        $posts=$postsQuery->listFrontEnd([
            'page'             => '1',
            'sort'             => $sortOrder,
            'perPage'          => $this->postsPerPage,
            'category'         => $category,
            'published'        => $isPublished,
            
        ]);
        
        
        return $posts;
       
    }

    protected function loadCategory()
    {
        if (!$slug = $this->categoryId) {
            return null;
        }

        $category = new BlogCategory;

        $category = $category->isClassExtendedWith('RainLab.Translate.Behaviors.TranslatableModel')
            ? $category->transWhere('slug', $slug)
            : $category->where('slug', $slug);

        $category = $category->first();

        return $category ?: null;
    }

    protected function checkEditor()
    {
        $backendUser = BackendAuth::getUser();

        return $backendUser &&
            $backendUser->hasAccess('rainlab.blog.access_posts') &&
            BlogSettings::get('show_all_posts', true);
    }



}
