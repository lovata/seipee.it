<?php namespace Responsiv\Campaign\Models;

use Url;
use Cms;
use Site;
use Model;
use Event;
use Config;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use Cms\Classes\Controller as CmsController;
use October\Rain\Parse\Bracket as TextParser;
use Responsiv\Campaign\Classes\TagManager;
use Responsiv\Campaign\Helpers\RecipientGroup;
use Responsiv\Campaign\Components\Template as TemplateComponent;
use Exception;
use stdClass;

/**
 * Message Model
 */
class Message extends Model
{
    use \October\Rain\Database\Traits\Multisite;
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Parse\Syntax\SyntaxModelTrait;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'responsiv_campaign_messages';

    /**
     * @var array Date fields
     */
    public $dates = ['launch_at', 'processed_at'];

    /**
     * @var array propagatable fields
     */
    protected $propagatable = [];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required|between:2,128',
        'page' => 'required'
    ];

    /**
     * @var array belongsTo
     */
    public $belongsTo = [
        'status' => MessageStatus::class
    ];

    /**
     * @var array belongsToMany
     */
    public $belongsToMany = [
        'subscriber_lists' => [
            SubscriberList::class,
            'table' => 'responsiv_campaign_messages_lists',
            'otherKey' => 'list_id',
        ],
        'subscribers' => [
            Subscriber::class,
            'table' => 'responsiv_campaign_messages_subscribers',
            'pivot' => ['content_html', 'sent_at', 'read_at', 'stop_at'],
        ],
    ];

    /**
     * @var array attachMany
     */
    public $attachMany = [
        'attachments' => \System\Models\File::class
    ];

    /**
     * @var array guarded fields
     */
    protected $guarded = [];

    /**
     * @var array fillable fields
     */
    protected $fillable = [];

    /**
     * @var array jsonable list of attribute names which are json encoded and decoded from the database.
     */
    protected $jsonable = ['syntax_data', 'syntax_fields', 'groups'];

    /**
     * getAvailableTags returns all available tags for parsing and their descriptions.
     */
    public static function getAvailableTags()
    {
        return (new TagManager())->getTagsForParsing();
    }

    /**
     * getStatusCodeAttribute returns `status_code`
     */
    public function getStatusCodeAttribute()
    {
        return $this->status?->code;
    }

    /**
     * beforeReplicate
     */
    public function beforeReplicate()
    {
        $this->syncOriginalAttribute('syntax_data');

        $this->status_id = MessageStatus::getDraftStatus()->id;

        // Suppress the exception in case the theme page is not found
        // this will revert to using the existing content instead
        try {
            $this->rebuildContent();
        }
        catch (Exception) {
        }
    }

    /**
     * beforeCreate
     */
    public function beforeCreate()
    {
        if (empty($this->subject)) {
            $this->subject = $this->name;
        }

        if (empty($this->status_id)) {
            $this->status_id = MessageStatus::getDraftStatus()->id;
        }

        if (empty($this->content)) {
            $this->rebuildContent();
        }
    }

    /**
     * beforeSave
     */
    public function beforeSave()
    {
        if ($this->isDirty('syntax_data')) {
            $this->rebuildContent();
        }

        $this->content_html = $this->renderTemplate();
    }

    /**
     * afterDelete
     */
    public function afterDelete()
    {
        $this->subscriber_lists()->detach();
        $this->subscribers()->detach();
    }

    /**
     * rebuildContent and sync fields and data
     */
    public function rebuildContent()
    {
        $this->content = $this->getPageContent($this->page);

        $this->is_dynamic_template = $this->isPageDynamicTemplate($this->page);

        $this->makeSyntaxFields($this->content);

        return $this;
    }

    /**
     * getPageContent renders the CMS page via the CMS controller,
     * this is only ever really called via the backend context
     */
    protected function getPageContent($page, $subscriber = null)
    {
        $theme = Theme::getEditTheme();

        // Apply the appropriate site context from the backend
        $oldSite = Site::getActiveSite();
        $oldRelativeLinks = Config::get('system.relative_links', false);
        Site::applyActiveSite(Site::getEditSite());
        Config::set('system.relative_links', false);
        Url::forceRootUrl(Config::get('app.url'));

        // Preview subscriber
        TemplateComponent::setDynamicSubscriber($subscriber);

        // Render the page via the CMS
        Event::listen('cms.page.init', function ($controller, $pageObj) use ($page) {
            if ($pageObj->baseFilename !== $page) {
                return;
            }

            $data = $this->getSyntaxData() ?: [];
            $controller->vars = array_merge($controller->vars, $data);
        });

        $result = CmsController::render($page, ['code' => LARAVEL_START], $theme);

        // Restore old site
        Site::applyActiveSite($oldSite);
        Config::set('system.relative_links', $oldRelativeLinks);
        Url::forceRootUrl(null);

        return $result;
    }

    /**
     * renderTemplate
     */
    public function renderTemplate()
    {
        if (!$this->content) {
            return null;
        }

        $parser = $this->getSyntaxParser($this->content);

        $data = $this->getSyntaxData();

        $template = $parser->render($data);

        return $template;
    }

    /**
     * isPageDynamicTemplate
     */
    protected function isPageDynamicTemplate($pageFile): bool
    {
        $theme = Theme::getEditTheme();

        Page::clearCache($theme);

        $page = Page::load($theme, $pageFile);

        $properties = $page->getComponentProperties('campaignTemplate');

        $isDynamic = $properties['isDynamic'] ?? null;

        return (bool) $isDynamic;
    }

    /**
     * renderDynamicTemplate
     */
    public function renderDynamicTemplate($subscriber)
    {
        $content = $this->getPageContent($this->page, $subscriber);

        $parser = $this->getSyntaxParser($content);

        $data = $this->getSyntaxData();

        $template = $parser->render($data);

        return $template;
    }

    /**
     * rebuildStats for the campaign
     */
    public function rebuildStats()
    {
        $this->count_subscriber = $this->subscribers()->count();
        $this->count_sent = $this->subscribers()->whereNotNull('sent_at')->count();
        $this->count_read = $this->subscribers()->whereNotNull('read_at')->count();
        $this->count_stop = $this->subscribers()->whereNotNull('stop_at')->count();
        return $this;
    }

    /**
     * getExtendedStats
     */
    public function getExtendedStats()
    {
        return (object)[
            'open_rate' => $this->count_read && $this->count_sent ? round(($this->count_read / $this->count_sent) * 100) : 0,
            'stop_rate' => $this->count_stop && $this->count_sent ? round(($this->count_stop / $this->count_sent) * 100) : 0,
            'count_unread' => $this->count_sent - $this->count_read,
            'count_happy' => $this->count_sent - $this->count_stop,
        ];
    }

    /**
     * getStaggerCount determines how many messages to send each hour, if staggered option is enabled.
     */
    public function getStaggerCount()
    {
        if (!$this->is_staggered) {
            return -1;
        }

        // Stagger by time
        if ($this->stagger_type == 'time') {
            $spread = max(1, (int)$this->stagger_time);
            return ceil($this->count_subscriber / $spread);
        }

        // Stagger by count
        return max(1, (int) $this->stagger_count);
    }

    /**
     * canBeProcessed determines if a message can be processed. For staggered messages,
     * it can be processed again after 15 minutes. Otherwise, 1 hour.
     * @return bool
     */
    public function canBeProcessed()
    {
        if (!$this->processed_at) {
            return true;
        }

        $hourAgo = $this
            ->freshTimestamp()
            ->subMinutes($this->is_staggered ? 15 : 60);

        return $this->processed_at <= $hourAgo;
    }

    /**
     * markProcessed marks this message as being processed.
     */
    public function markProcessed()
    {
        self::where('id', $this->id)
            ->update(['processed_at' => $this->freshTimestamp()]);
    }

    /**
     * duplicateCampaign
     */
    public function duplicateCampaign()
    {
        $model = new self([
            'syntax_data' => $this->syntax_data,
            'syntax_fields' => $this->syntax_fields,
            'groups' => $this->groups,
            'page' => $this->page,
            'content' => $this->content,
            'name' => $this->name,
            'subject' => $this->subject,
            'is_staggered' => $this->is_staggered,
            'stagger_type' => $this->stagger_type,
            'stagger_time' => $this->stagger_time,
            'stagger_count' => $this->stagger_count,
            'is_repeating' => $this->is_repeating,
            'count_repeat' => $this->count_repeat,
            'repeat_frequency' => $this->repeat_frequency,
        ]);

        $model->subscriber_lists = $this->subscriber_lists->all();

        return $model;
    }

    //
    // Tag processing
    //

    /**
     * getIterativeNameAttribute
     */
    public function getIterativeNameAttribute()
    {
        if ($this->is_repeating) {
            return $this->name . ' (#' . $this->count_repeat . ')';
        }

        return $this->name;
    }

    /**
     * getGroupsOptions
     */
    public function getGroupsOptions()
    {
        return RecipientGroup::listRecipientGroups();
    }

    /**
     * getPageOptions returns a list of pages available in the theme.
     * @return array Returns an array of strings.
     */
    public function getPageOptions()
    {
        $result = [];

        $pages = $this->listPagesWithCampaignComponent();
        foreach ($pages as $baseName => $page) {
            $result[$baseName] = strlen($page->name) ? $page->name : $baseName;
        }

        if (!$result) {
            $result[null] = 'No pages found';
        }

        return $result;
    }

    //
    // Group management
    //

    /**
     * listPagesWithCampaignComponent returns a collection of page objects that use the
     * Campaign Component provided by this plugin.
     * @return array
     */
    public function listPagesWithCampaignComponent()
    {
        $result = [];
        $pages = Page::withComponent('campaignTemplate')->sortBy('baseFileName')->all();

        foreach ($pages as $page) {
            $baseName = $page->getBaseFileName();
            $result[$baseName] = $page;
        }

        return $result;
    }

    //
    // Page management
    //

    /**
     * getPageName
     */
    public function getPageName()
    {
        return ($page = $this->getPageObject()) ? $page->title : $this->page;
    }

    /**
     * getPageObject
     */
    public function getPageObject()
    {
        return Page::find($this->page);
    }

    /**
     * renderForPreview
     */
    public function renderForPreview()
    {
        $content = $this->content_html ?: $this->renderTemplate();
        $parser = new TextParser;

        $data = $this->buildTagData(new Subscriber, true);

        $result = $parser->parseString($content, $data);

        // Inject base target
        $result = str_replace(
            '</head>',
            '<base target="_blank" />' . PHP_EOL . '</head>',
            $result
        );

        return $result;
    }

    /**
     * buildTagData returns an array of tag data for this message and the subscriber.
     * Plugins may extend the available tags by registering them with the TagManager
     *
     * @param $subscriber
     * @param bool $forPreview
     * @return array
     */
    public function buildTagData($subscriber, $forPreview = false)
    {
        $data = [];
        $tagManager = new TagManager;
        $tags = $tagManager->getTags();

        // Prepare the tagData
        $tagData = new stdClass;
        $tagData->message = $this;
        $tagData->subscriber = $subscriber;

        Event::fire('responsiv.campaign.extendTagdata', [&$tagData]);

        // Iterate over the tags and resolve the closures
        foreach ($tags as $name => &$tag) {
            $data[$name] = $forPreview ? $tagManager->getPreviewForTag($name, $tagData) : $tagManager->getValueForTag($name, $tagData);
        }

        return $data;
    }

    /**
     * renderForSubscriber
     */
    public function renderForSubscriber($subscriber)
    {
        $contentHtml = $this->content_html;

        if ($this->is_dynamic_template) {
            $contentHtml = $subscriber->getDynamicTemplateHtml($this);
        }

        $parser = new TextParser;
        $data = $this->buildTagData($subscriber);
        $result = $parser->parseString($contentHtml, $data);

        // Extensibility
        Event::fire('responsiv.campaign.renderForSubscriber', [$this, &$result, $subscriber]);

        // Inject tracking pixel
        $result = str_replace(
            '</body>',
            $this->getTrackingPixelImage($subscriber) . PHP_EOL . '</body>',
            $result
        );

        return $result;
    }

    /**
     * getTrackingPixelImage
     */
    public function getTrackingPixelImage($subscriber)
    {
        $src = $this->getBrowserUrl($subscriber) . '.png';
        return '<img src="' . $src . '" alt="" />';
    }

    /**
     * getBrowserUrl
     */
    public function getBrowserUrl($subscriber)
    {
        return Cms::pageUrl($this->page, [
            'code' => $this->getUniqueCode($subscriber)
        ]);
    }

    /**
     * getUnsubscribeUrl
     */
    public function getUnsubscribeUrl($subscriber)
    {
        return $this->getBrowserUrl($subscriber) . '?unsubscribe=1';
    }

    /**
     * getUniqueCode
     */
    public function getUniqueCode($subscriber)
    {
        $value = $this->id . '!' . $subscriber->id;
        $hash = md5($value . '!' . $subscriber->email);
        return base64_encode($value . '!' . $hash);
    }

    //
    // Scopes
    //

    /**
     * scopeIsArchived
     */
    public function scopeIsArchived($query)
    {
        return $query->where('status_id', '!=', MessageStatus::getArchivedStatus()->id);
    }

    /**
     * isMultisiteEnabled allows for programmatic toggling
     * @return bool
     */
    public function isMultisiteEnabled()
    {
        return (bool) Config::get('multisite.features.responsiv_campaign_message', false);
    }
}
