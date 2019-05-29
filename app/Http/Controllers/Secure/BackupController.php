<?php


namespace App\Http\Controllers\Secure;


use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class BackupController extends Controller
{
    public function upload()
    {
        $file = request()->file('file');
        try {
            $fileName = Str::random(10) . '.' . $file->getClientOriginalName();
            $file->storeAs('backup', $fileName, ['disk' => 'local']);
            return response()->json([ 'success' => true ], 201);
        } catch (\Exception $e) {
            $this->logWarning("Problem with upload image end withn error " . $e);
            return response()->json([], 404);
        }
    }
}