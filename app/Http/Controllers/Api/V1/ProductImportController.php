<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProductImport;
use App\Jobs\ProcessProductImportJob;
use App\Http\Resources\Api\V1\ProductImportResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ProductImportController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $path = $file->store('imports');

        $import = ProductImport::create([
            'uploader_id' => $request->user()->id,
            'file_path' => $path,
            'status' => 'pending',
            'rows_total' => null,
            'rows_processed' => 0,
            'error_log' => null,
        ]);

        // dispatch job
        ProcessProductImportJob::dispatch($import->id);

        return response()->json(['message' => 'Import queued', 'import_id' => $import->id], 202);
    }

    /**
     * Show import status.
     *
     * Only uploader or admin may view.
     */
    public function status(ProductImport $import, Request $request)
    {
        $user = $request->user();

        // Authorization: allow admin or the uploader
        if (! $user->hasRole('admin') && $import->uploader_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $import->loadMissing('uploader:id,name,email');
        return new ProductImportResource($import);
    }
}
