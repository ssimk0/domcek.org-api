<?php

namespace App\Http\Controllers\Secure;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackupController extends Controller
{
    public function upload(Request $request)
    {
        $data = $request->validate([
            'participants' => 'nullable|array',
            'wrong-payments' => 'nullable|array',
        ]);

        try {
            $fileName = 'backup/'.Str::random(10).'.json';
            Storage::disk('local')->put($fileName, json_encode($data));

            return response()->json(['success' => true], 201);
        } catch (\Exception $e) {
            $this->logWarning('Problem with upload image end withn error '.$e);

            return response()->json([], 404);
        }
    }
}
