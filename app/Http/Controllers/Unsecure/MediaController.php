<?php


namespace App\Http\Controllers\Unsecure;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function upload()
    {
        $image = request()->file('file');
        try {
            $fileName = Str::random(40) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('media', $fileName, ['disk' => 's3']);
            $path = Storage::cloud()->url($path);
            $path = str_replace('/media/', '/reduced/', $path);
            sleep(5);
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
