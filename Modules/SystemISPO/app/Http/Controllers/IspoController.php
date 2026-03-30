<?php

namespace Modules\SystemISPO\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\SystemISPO\App\Models\IspoDocument;
use Modules\SystemISPO\App\Models\IspoDocumentEntry;
use Modules\SystemISPO\App\Models\IspoEntryAttachment;
use Modules\SystemISPO\App\Models\IspoEntryHistory;
use Modules\SystemISPO\App\Models\IspoItem;
use Modules\ServiceAgreementSystem\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IspoController extends Controller
{
    public function index()
    {
        $documents = IspoDocument::with('site')->orderBy('year', 'desc')->get();
        $sites = Site::all();
        return view('systemispo::ispo.index', compact('documents', 'sites'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->moduleRole('ispo') !== 'ISPO Admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'site_id' => 'required',
            'year' => 'required|integer|min:2020|max:2030',
        ]);

        // Check if document already exists
        $exists = IspoDocument::where('site_id', $request->site_id)
            ->where('year', $request->year)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Document for this Site and Year already exists.');
        }

        $doc = IspoDocument::create([
            'site_id' => $request->site_id,
            'year' => $request->year,
            'status' => 'active',
            'start_date' => now(),
        ]);

        return redirect()->route('ispo.show', $doc->id);
    }

    public function show($id)
    {
        $document = IspoDocument::with('site')->findOrFail($id);
        
        // Fetch the full hierarchy: Principles -> Descendants
        $principles = IspoItem::where('type', 'principle')
            ->orderBy('order_index')
            ->with(['children' => function($q) {
                $q->orderBy('order_index')->with(['children' => function($q) {
                    $q->orderBy('order_index')->with(['children' => function($q) {
                        $q->orderBy('order_index')->with(['children' => function($q) {
                            $q->orderBy('order_index');
                        }]);
                    }]);
                }]);
            }])
            ->get();

        // Flatten the hierarchy for the table view
        $rows = [];
        foreach ($principles as $p) {
            foreach ($p->children as $c) {
                foreach ($c->children as $i) {
                    if ($i->children->isEmpty()) {
                        continue; 
                    }
                    
                    foreach ($i->children as $child) {
                        if ($child->type === 'parameter') {
                            foreach ($child->children as $v) {
                                $rows[] = [
                                    'principle' => $p,
                                    'criteria' => $c,
                                    'indicator' => $i,
                                    'parameter' => $child,
                                    'verifier' => $v
                                ];
                            }
                        } elseif ($child->type === 'verifier') {
                             $rows[] = [
                                'principle' => $p,
                                'criteria' => $c,
                                'indicator' => $i,
                                'parameter' => null,
                                'verifier' => $child
                            ];
                        }
                    }
                }
            }
        }

        // Fetch existing entries with attachments
        $entries = IspoDocumentEntry::where('ispo_document_id', $id)
            ->with('attachments')
            ->get()
            ->keyBy('ispo_item_id');

        return view('systemispo::ispo.show', compact('document', 'rows', 'entries'));
    }

    public function updateEntry(Request $request, $id)
    {
        $role = auth()->user()->moduleRole('ispo') === 'ISPO Admin' ? 'admin' : 'auditor';

        $request->validate([
            'item_id' => 'required',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
            'audit_status' => 'nullable|string',
            'audit_notes' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $document = IspoDocument::findOrFail($id);
        $entry = IspoDocumentEntry::firstOrNew([
            'ispo_document_id' => $document->id,
            'ispo_item_id' => $request->item_id
        ]);

        // Logic based on Role
        // Admin acts as HR (Data Entry) here, or we can allow both.
        // Assuming Admin can do both, but usually they fill the main data.
        if ($role === 'admin') {
            $entry->status = $request->status;
            $entry->notes = $request->notes;
        } elseif ($role === 'auditor') {
            $entry->audit_status = $request->audit_status;
            $entry->audit_notes = $request->audit_notes;
        }

        $entry->save();

        // Handle Attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ispo_attachments', 'public');
                IspoEntryAttachment::create([
                    'ispo_document_entry_id' => $entry->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                ]);
            }
        }
        
        $entry->load('attachments');

        // Create History Snapshot
        // We Snapshot the CURRENT state of the entry + attachments
        $attachmentSnapshot = $entry->attachments->map(function($att) {
            return [
                'id' => $att->id,
                'file_path' => $att->file_path,
                'file_name' => $att->file_name
            ];
        })->toArray();

        IspoEntryHistory::create([
            'ispo_document_entry_id' => $entry->id,
            'user_id' => auth()->id(),
            'role' => $role,
            'status' => $entry->status,
            'notes' => $entry->notes,
            'audit_status' => $entry->audit_status,
            'audit_notes' => $entry->audit_notes,
            'attachments_snapshot' => $attachmentSnapshot,
        ]);

        return response()->json([
            'success' => true,
            'entry' => $entry,
            'message' => 'Data saved successfully'
        ]);
    }

    public function destroyAttachment($attachmentId)
    {
        $attachment = IspoEntryAttachment::findOrFail($attachmentId);
        $attachment->delete();

        return response()->json(['success' => true, 'message' => 'Attachment removed from active list']);
    }

    public function getHistory($entryId)
    {
        $history = IspoEntryHistory::where('ispo_document_entry_id', $entryId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'history' => $history
        ]);
    }
    public function bulkUpdate(Request $request, $id)
    {
        $role = auth()->user()->moduleRole('ispo') === 'ISPO Admin' ? 'admin' : 'auditor';

        $request->validate([
            'items' => 'required|array',
            'items.*.status' => 'nullable|string',
            'items.*.notes' => 'nullable|string',
            'items.*.audit_status' => 'nullable|string',
            'items.*.audit_notes' => 'nullable|string',
        ]);

        $document = IspoDocument::findOrFail($id);
        $updatedCount = 0;

        foreach ($request->items as $itemId => $data) {
            // Check if there's any data to save for this item
            // For HR: status, notes, or files
            // For Auditor: audit_status, audit_notes
            $hasChange = false;
            
            if ($role === 'admin') {
                if (isset($data['status']) || isset($data['notes']) || $request->hasFile("items.$itemId.files")) {
                    $hasChange = true;
                }
            } elseif ($role === 'auditor') {
                if (isset($data['audit_status']) || isset($data['audit_notes'])) {
                    $hasChange = true;
                }
            }

            if (!$hasChange) continue;

            $entry = IspoDocumentEntry::firstOrNew([
                'ispo_document_id' => $document->id,
                'ispo_item_id' => $itemId
            ]);

            // Update Fields
            if ($role === 'admin') {
                if(isset($data['status'])) $entry->status = $data['status'];
                if(isset($data['notes'])) $entry->notes = $data['notes'];
            } elseif ($role === 'auditor') {
                if(isset($data['audit_status'])) $entry->audit_status = $data['audit_status'];
                if(isset($data['audit_notes'])) $entry->audit_notes = $data['audit_notes'];
            }

            $entry->save();

            // Handle Files
            if ($request->hasFile("items.$itemId.files")) {
                foreach ($request->file("items.$itemId.files") as $file) {
                    $path = $file->store('ispo_attachments', 'public'); // Explicit public disk
                    IspoEntryAttachment::create([
                        'ispo_document_entry_id' => $entry->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                    ]);
                }
            }

            // Create History (Use common logic if possible, or repeat here)
            // Reload attachments for snapshot
            $entry->load('attachments');
            
            $attachmentSnapshot = $entry->attachments->map(function($att) {
                return [
                    'id' => $att->id,
                    'file_path' => $att->file_path,
                    'file_name' => $att->file_name
                ];
            })->toArray();

            IspoEntryHistory::create([
                'ispo_document_entry_id' => $entry->id,
                'user_id' => auth()->id(),
                'role' => $role,
                'status' => $entry->status,
                'notes' => $entry->notes,
                'audit_status' => $entry->audit_status,
                'audit_notes' => $entry->audit_notes,
                'attachments_snapshot' => $attachmentSnapshot,
            ]);

            $updatedCount++;
        }

        return redirect()->route('ispo.show', $id)->with('success', "$updatedCount items updated successfully.");
    }
}
