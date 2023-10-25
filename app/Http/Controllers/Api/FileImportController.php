<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FileImportController extends Controller
{
    private const PERMISSION_ENTITY = 'file_import';

    // POST /file_imports
    public function store(Request $request) {

        $user = $request->user();
        if (!$user->hasPermission(self::PERMISSION_ENTITY, __FUNCTION__)) {
            abort(401, 'Unauthorized');
        }
        $fields = [
            //'file' => ['required','mimes:jpg,jpeg,png,csv,txt,xlx,xls,pdf','max:2048'],
            'file' => ['required','mimes:csv,txt','max:2048'],
            'file_import_resource' => ['required', Rule::in(['vehicle'])]
        ];
        $validated = $request->validate($fields);

        $file_name = time() . '_' . $validated['file']->getClientOriginalName();
        $file_path = $validated['file']->storeAs('uploads', $file_name, 'public');

        $name = time() . '_' . $validated['file']->getClientOriginalName();
        $path = '/storage/' . $file_path;

        switch ($validated['file_import_resource']) {
            case "vehicle":
                Vehicle::import($path);
                break;
            default:
                abort(500, "Invalid file import resource: ${$validated['file_import_resource']}");
        }

        return response()->json(['success' => 'File uploaded successfully.']);


    }
}
