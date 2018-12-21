<?php


namespace App\Http\Controllers\Unsecure;


class MediaController
{
    public function upload()
    {
        $image = request()->file( 'file' );

        try {
            $path = $image->store( 'media', 's3' );

            return response()->json([
                'file' => $path,
                'location' => $path['url'], # duplicate for TinyMCE
            ], 201);
        } catch (\Exception $e) {
            return response()->json([], 404);
        }
    }
}
