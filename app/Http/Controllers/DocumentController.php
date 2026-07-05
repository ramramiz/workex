<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;
use App\Models\Intern;
use App\Models\Employee;

class DocumentController extends Controller
{
    /**
     * Store a newly uploaded document.
     */
    public function store(Request $request)
    {
        $request->validate([
            'document' => 'required|file|max:10240', // Limit to 10MB
            'title' => 'nullable|string|max:255',
            'documentable_type' => 'required|string|in:intern,employee',
            'documentable_id' => 'required|integer',
        ]);

        $type = $request->input('documentable_type');
        $id = $request->input('documentable_id');
        $modelClass = null;

        if ($type === 'intern') {
            $modelClass = Intern::class;
            $exists = Intern::where('id', $id)->exists();
        } else {
            $modelClass = Employee::class;
            $exists = Employee::where('id', $id)->exists();
        }

        if (!$exists) {
            return back()->withErrors(['documentable_id' => 'The selected record does not exist.']);
        }

        $file = $request->file('document');
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $title = $request->input('title') ?: pathinfo($fileName, PATHINFO_FILENAME);

        $path = $file->store('documents', 'public');

        Document::create([
            'uploaded_by' => auth()->id(),
            'documentable_type' => $modelClass,
            'documentable_id' => $id,
            'file_name' => $fileName,
            'file_path' => $path,
            'file_size' => $fileSize,
            'title' => $title,
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    /**
     * Download the specified document.
     */
    public function download(Document $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found on disk.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * View the specified document inline in the browser.
     */
    public function view(Document $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found on disk.');
        }

        $filePath = Storage::disk('public')->path($document->file_path);
        $mimeType = Storage::disk('public')->mimeType($document->file_path);

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $document->file_name . '"'
        ]);
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy(Document $document)
    {
        // Delete file from disk
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Delete DB record
        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }
}
