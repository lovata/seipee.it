<?php

use Inetis\DownloadManager\Models\Category;

Route::get('downloadmanager/download/{category_id}/{file_id}', function ($categoryId, $fileId) {

    $category = Category::findOrFail($categoryId);

    if (!$category->hasAccess()) {
        abort(403);
    }

    /** @var $file \System\Models\File */
    if (empty($file = $category->files->find($fileId))) {
        abort(403);
    }

    //disable execution time limit when downloading a big file.
    @set_time_limit(0);

    // Backward compatibility: getDisk() was introduced in OC 1.0.457
    $disk = method_exists($file, 'getDisk') ? $file->getDisk() : Storage::disk('local');
    $filesystem = $disk->getDriver();
    $path = $file->getDiskPath();

    // Backward compatibility: getMimetype() was renamed to mimeType() in Flysystem 2.0
    $mimeType = method_exists($filesystem, 'mimeType')
        ? $filesystem->mimeType($path)
        : $filesystem->getMimetype($path);

    $stream = $filesystem->readStream($path);

    if (ob_get_level()) {
        ob_end_clean();
    }

    return response()->stream(function () use ($stream) {
        fpassthru($stream);
    }, 200, [
        'Content-Type'        => $mimeType,
        'Content-disposition' => 'attachment; filename="' . $file->file_name . '"',
    ]);

})->middleware('web');
