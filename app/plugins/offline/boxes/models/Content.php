<?php

namespace OFFLINE\Boxes\Models;

use Cms\Classes\Page as CmsPage;
use Illuminate\Database\Eloquent\Builder;
use Model;
use OFFLINE\Boxes\Classes\CMS\CmsPageParams;
use OFFLINE\Boxes\Classes\CMS\ThemeResolver;
use OFFLINE\Boxes\Classes\Scopes\ThemeScope;

/**
 * @property mixed|string $boxes
 * @property string $layout
 * @property string $slug
 * @property string $theme
 * @property int $content_id
 * @property string $content_type
 * @property boolean $is_pending_content
 * @property Model $content
 * @property array $custom_config
 * @mixin Builder
 */
class Content extends Model
{
    use \OFFLINE\Boxes\Classes\Traits\HasBoxes;
    use \OFFLINE\Boxes\Classes\Traits\HasMorphedTailorRelation;

    /**
     * @var string table associated with the model
     */
    public $table = 'offline_boxes_content';

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

    public $translatable = [
        'name',
        ['url', 'index' => true],
    ];

    public $fillable = [
        'layout',
        'slug',
        'is_pending_content',
        'custom_config',
        'holder_id',
        'holder_type',
    ];

    public $morphMany = [
        'boxes' => [Box::class, 'delete' => true, 'name' => 'holder', 'replicate' => true],
    ];

    public $morphTo = [
        // If a page is not a standalone page but just acts as content of a related
        // model, this relation is used. $this->content points to the related model.
        'content' => [],
    ];

    public $jsonable = [
        'custom_config',
    ];

    /**
     * Mark this model as replicated. This allows us to
     * run special actions on save.
     */
    private string $replicatedFrom = '';

    /**
     * Add event listeners.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Force a starting slash on translated URLs.
        $this->bindEvent('model.beforeReplicate', function () {
            $this->replicatedFrom = $this->slug;
            // Generate a new slug for this replicated instance.
            $this->slug = null;
        });
    }

    /**
     * Cleanup the data before saving.
     */
    public function beforeSave()
    {
        // Enforce the theme value.
        if (!$this->theme) {
            $this->theme = ThemeResolver::instance()?->getThemeCode();
        }

        if (!$this->slug) {
            $this->slug = str_random(12);
        }

        // If this model is marked as pending but now has received content,
        // reset the pending content flag.
        if ($this->is_pending_content && $this->content_type) {
            $this->is_pending_content = false;
        }
    }

    public function afterSave()
    {
        // Move nested boxes to their new parents.
        if ($this->replicatedFrom) {
            $originalContent = self::query()->where('slug', $this->replicatedFrom)->first();

            if ($originalContent) {
                $this->moveNestedBoxesToNewParents($originalContent, $this);
            }
        }
    }

    /**
     * Get all available layout options.
     */
    public function getLayoutOptions()
    {
        return (new CmsPage())->getLayoutOptions();
    }

    /**
     * Build a CMS page from this model instance.
     *
     * @return CmsPage
     */
    public function buildCmsPage(): CmsPage
    {
        $cmsPage = CmsPage::inTheme($this->theme);
        $cmsPage->title = $this->name;

        if (class_exists(\RainLab\Translate\Models\Locale::class)) {
            $cmsPage->url = $this->lang(\RainLab\Translate\Models\Locale::getDefault()->code)->url;
        } else {
            $cmsPage->url = $this->url;
        }

        $cmsPage->layout = $this->layout;

        $cmsPage->apiBag[CmsPageParams::BOXES_PAGE_ID] = $this->id;
        $cmsPage->apiBag[CmsPageParams::BOXES_MODEL_TYPE] = self::class;

        $cmsPage->fireEvent('model.afterFetch');

        if (class_exists(\RainLab\Translate\Models\Locale::class)) {
            $urls = collect(\RainLab\Translate\Models\Locale::listEnabled())
                ->mapWithKeys(fn ($name, $code) => [$code => $this->lang($code)->url])
                ->toArray();

            $cmsPage->attributes['viewBag'] = [
                'localeUrl' => $urls,
            ];
        }

        return $cmsPage;
    }

    /**
     * Dummy scope for compatibility with Page revisions.
     * @param mixed $query
     */
    public function scopeCurrent($query)
    {
    }

    protected static function booted()
    {
        static::addGlobalScope(new ThemeScope());
    }
}
