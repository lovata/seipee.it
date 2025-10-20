<?php namespace Responsiv\Campaign\Classes;

use Log;
use System\Classes\PluginManager;
use Exception;
use Closure;

/*
 * TagManager manages tags usable in the content editor.
 */
class TagManager
{
    const TAG_DEFAULTS = [
        'description' => '',
        'preview' => '',
        'value' => '',
    ];

    /**
     * @var array registeredTags
     */
    protected $registeredTags = [];

    /**
     * __construct TagManager
     */
    public function __construct()
    {
        $this->registerDefaultTags();
        $this->registerPluginTags();
    }

    /**
     * registerPluginTags
     */
    protected function registerPluginTags()
    {
        $pluginTags = PluginManager::instance()->getRegistrationMethodValues('registerMailCampaignTags');

        foreach ($pluginTags as $pluginSlug => $tags) {
            $this->registerTags($tags, $pluginSlug);
        }
    }

    /**
     * Returns the tags in the format for the parser and description display
     *
     * @return array
     */
    public function getTagsForParsing()
    {
        $tags = [];

        foreach ($this->registeredTags as $tagName => $tag) {
            $tags[$tagName] = $tag['description'];
        }

        return $tags;
    }

    /**
     * Return the registered tags as array
     *
     * @return array
     */
    public function getTags()
    {
        return $this->registeredTags;
    }

    /**
     * Registers an array of tags.
     *
     * @param array $tags
     * @param bool $pluginAlias The alias of the plugin that registers a tag.
     */
    public function registerTags(array $tags, $pluginAlias = '')
    {
        $tags = $this->sanitizeTagOptions($tags);
        foreach ($tags as $tagName => &$tagOptions) {

            if ($existingTag = $this->findTag($tagName)) {
                Log::warning('Tag ' . $tagName . ' already registered in plugin ' . $existingTag['_plugin'] . '.');
            }

            $tagOptions['_plugin'] = $pluginAlias;
            $this->registeredTags[$tagName] = $tagOptions;
        }
    }

    /**
     * Return the value of a tag.
     *
     * @param $tagName
     * @param $closureData
     * @return mixed
     */
    public function getValueForTag($tagName, $closureData)
    {
        if (!($tag = $this->findTag($tagName))) {
            return self::TAG_DEFAULTS['value'];
        }

        try {
            $value = $tag['value'] instanceof Closure ? $tag['value']($closureData) : $tag['value'];
        }
        catch (Exception $e) {
            Log::info('Error while accessing tag data: ' . $e->getMessage());
            return null;
        }

        return $value;
    }

    /**
     * Return the preview for a tag.
     *
     * @param $tagName
     * @param $closureData
     * @return mixed
     */
    public function getPreviewForTag($tagName, $closureData)
    {
        if (!($tag = $this->findTag($tagName))) {
            return self::TAG_DEFAULTS['preview'];
        }

        return $tag['preview'] instanceof Closure ? $tag['preview']($closureData) : $tag['preview'];
    }

    /**
     * Sets empty tag options to the default values.
     *
     * @param array $tags
     * @return array
     */
    protected function sanitizeTagOptions(array $tags)
    {
        foreach ($tags as $tagName => &$tagOptions) {
            foreach (self::TAG_DEFAULTS as $defaultKey => $defaultValue) {
                if (!array_key_exists($defaultKey, $tagOptions)) {
                    $tagOptions[$defaultKey] = $defaultValue;
                }
            }
        }

        return $tags;
    }

    /**
     * Registers the default tags.
     */
    protected function registerDefaultTags()
    {
        $this->registerTags([
            'first_name' => [
                'preview' => 'John',
                'value' => function ($tagData) {
                    return $tagData->subscriber->first_name;
                },
                'description' => 'The subscribers first name',
            ],
            'last_name' => [
                'preview' => 'Smith',
                'value' => function ($tagData) {
                    return $tagData->subscriber->last_name;
                },
                'description' => 'The subscribers last name',
            ],
            'email' => [
                'preview' => 'john.smith@company.com',
                'value' => function ($tagData) {
                    return $tagData->subscriber->email;
                },
                'description' => 'The subscribers email address',
            ],
            'unsubscribe_url' => [
                'preview' => 'javascript:',
                'value' => function ($tagData) {
                    return $tagData->message->getUnsubscribeUrl($tagData->subscriber);
                },
                'description' => 'The URL for unsubscribing from the list',
            ],
            'browser_url' => [
                'preview' => 'javascript:',
                'value' => function ($tagData) {
                    return $tagData->message->getBrowserUrl($tagData->subscriber);
                },
                'description' => 'The URL for the browser view',
            ],
            'tracking_pixel' => [
                'preview' => 'javascript:',
                'value' => function ($tagData) {
                    return $tagData->message->getTrackingPixelImage($tagData->subscriber);
                },
                'description' => 'The tracking pixel',
            ],
            'tracking_url' => [
                'preview' => 'javascript:',
                'value' => function ($tagData) {
                    return $tagData->message->getBrowserUrl($tagData->subscriber) . '.png';
                },
                'description' => 'The tracking URL',
            ],
        ], 'Responsiv.Campaign');
    }

    /**
     * Find a tag in our cache of registered tags
     *
     * @param $tag
     * @return bool|mixed
     */
    protected function findTag($tag)
    {
        return $this->registeredTags[$tag] ?? null;
    }
}
