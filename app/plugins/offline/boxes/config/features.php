<?php

return [
    // Deprecated: This value is now ignored, use backend setting to enable/disable this feature.
    'revisions' => (bool)env('BOXES_REVISIONS_ENABLED', true),

    'multisite' => (bool)env('BOXES_MULTISITE_ENABLED', true),
    'references' => (bool)env('BOXES_REFERENCES_ENABLED', true),
    'isProVersion' => true,
    'placeholderPreviews' => (bool)env('BOXES_PLACEHOLDER_PREVIEWS_ENABLED', true),
];
