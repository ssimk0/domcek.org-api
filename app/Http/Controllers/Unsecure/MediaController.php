<?php


namespace App\Http\Controllers\Unsecure;


use Illuminate\Support\Facades\Storage;

class MediaController
{
    public function upload()
    {
        $image = request()->file( 'file' );
        try {
            $path = $image->store( 'media', 's3' );
            $path = Storage::cloud()->url($path);

            return response()->json([
                'url' => $path,
                'success' => true,
                'location' => $path, # duplicate for TinyMCE
            ], 201);
        } catch (\Exception $e) {
            return response()->json([], 404);
        }
    }
}
