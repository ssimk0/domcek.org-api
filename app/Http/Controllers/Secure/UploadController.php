<?php


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    const SMALL = "resized/small";
	const LARGE = "reduced";

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        $files = $this->storeFile($request);
        $largeFilePath = self::LARGE."/".$files["filename"];
        $smallFilePath = self::LARGE."/".$files["filename"];

        Storage::put($largeFilePath, $files["file"]->__toString(), 'public');
        if (Arr::has($files, "thumb")) {
            Storage::put($smallFilePath, $files["thumb"]->__toString(), 'public');
        }

        return response()->json([
            "success" => true,
            "location" => $largeFilePath,
            "url" => $largeFilePath,
            "url_small" => $smallFilePath
        ]);
    }

    protected function storeFile($request)
    {
        // Get file from request
        $file = $request->file('file');
        $is_image = false;

        // Get filename with extension
        $filename = Str::random(15);

        // Get the original image extension
        $extension = $file->getClientOriginalExtension();

        if (in_array($extension, ["jpg", "jpeg", "png", "svg", "gif"])) {
            $is_image = true;
        }

        // Create unique file name
        $fileNameToStore = $filename . '_' . time() . '.' . $extension;

        $files = [
            "file" => $file
        ];

        if ($is_image) {
            $files = $this->resizeImage($file, $fileNameToStore);
        }

        // Refer image to method resizeImage
        return array_merge($files, ["filename" => $fileNameToStore]);
    }

    protected function resizeImage($file)
    {
        $img = Image::make($file);
        $width = $img->getWidth();
        // Resize image
        $large = $img->resize($width > 1920 ? 1920 : $width, null, function ($constraint) {
            $constraint->aspectRatio();
        })->orientate()->encode('jpg', 75);

        $thumb = Image::make($file)->resize(300, null, function ($constraint) {
            $constraint->aspectRatio();
        })->orientate()->encode('jpg', 75);

        return ["file" => $large, "thumb" => $thumb];
    }
}