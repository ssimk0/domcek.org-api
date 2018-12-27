<?php


namespace App\Http\Controllers\Unsecure;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
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
            $this->logWarning("Problem with upload image end withn error " . $e);
            return response()->json([], 404);
        }
    }
}
