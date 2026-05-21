<?php

use App\Models\Trip;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use function Livewire\Volt\{state, computed, on, uses};

uses([WithFileUploads::class]);

state([
    'trip',
    'document_file' => null,
]);

on(['trip-updated' => '$refresh']);

$documents = computed(function () {
    return $this->trip->documents()->with('uploader')->get();
});

$uploadDocument = function () {
    // Only organizer/co_planner can upload
    if (!$this->trip->canEditItinerary(auth()->user())) {
        abort(403);
    }

    $this->validate([
        'document_file' => 'required|file|max:10240', // Max 10MB
    ]);

    $originalName = $this->document_file->getClientOriginalName();
    $extension = $this->document_file->getClientOriginalExtension();
    $size = $this->document_file->getSize();

    $path = $this->document_file->store('documents', 'local');

    Document::create([
        'trip_id' => $this->trip->id,
        'uploaded_by' => auth()->id(),
        'name' => $originalName,
        'file_path' => $path,
        'file_size' => $size,
        'file_type' => $extension,
    ]);

    $this->reset('document_file');
    $this->dispatch('trip-updated');
};

$downloadDocument = function ($docId) {
    // Scope to current trip to prevent IDOR
    $document = $this->trip->documents()->findOrFail($docId);

    return Storage::disk('local')->download($document->file_path, $document->name);
};

$deleteDocument = function ($docId) {
    // Scope to current trip to prevent IDOR
    $document = $this->trip->documents()->findOrFail($docId);
    if (auth()->id() !== $document->uploaded_by && !$this->trip->canManageMembers(auth()->user())) {
        abort(403);
    }

    Storage::disk('local')->delete($document->file_path);
    $document->delete();
    $this->dispatch('trip-updated');
};

?>

<div class="space-y-6" x-init="if(window.Motion) { window.Motion.animate($el, { opacity: [0, 1], y: [10, 0] }, { duration: 0.3 }) }">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Upload Area -->
        <div class="md:col-span-1">
            <div class="bg-bg-primary border border-border-light rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold text-sm text-text-main mb-4 flex items-center space-x-1.5">
                    <i class="ph ph-upload-simple"></i>
                    <span>Upload ticket / PDF</span>
                </h3>
                
                <form wire:submit.prevent="uploadDocument" class="space-y-4">
                    <div class="border-2 border-dashed border-border-card rounded-xl p-6 text-center hover:border-text-muted transition relative bg-bg-secondary/20">
                        <input type="file" wire:model="document_file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <i class="ph ph-file-arrow-up text-3xl text-text-muted block mx-auto"></i>
                        <p class="mt-2 text-xs font-bold text-text-main">
                            @if ($document_file)
                                Selected: {{ $document_file->getClientOriginalName() }}
                            @else
                                Choose PDF or Image
                            @endif
                        </p>
                        <p class="text-[10px] text-text-muted mt-1">Maximum file size: 10MB</p>
                    </div>
                    @error('document_file') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror

                    <button type="submit" 
                            class="w-full px-4 py-2.5 bg-brand-neutral hover:bg-brand-hover text-bg-primary font-bold text-xs rounded-xl transition cursor-pointer"
                            @if(!$document_file) disabled @endif>
                        Upload Document
                    </button>
                </form>
            </div>
        </div>

        <!-- Documents lists -->
        <div class="md:col-span-2">
            <div class="bg-bg-primary border border-border-light rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold text-sm text-text-main border-b border-border-light pb-2 mb-4 flex items-center space-x-1.5">
                    <i class="ph ph-folder-open"></i>
                    <span>Shared Documents</span>
                </h3>
                @if ($this->documents->isEmpty())
                    <p class="text-xs text-text-muted py-6">No files uploaded yet. Add tickets, booking confirmations, or PDFs.</p>
                @else
                    <div class="divide-y divide-border-light">
                        @foreach ($this->documents as $doc)
                            <div class="flex items-center justify-between py-3">
                                <div class="flex items-center space-x-3.5">
                                    <span class="p-2.5 bg-bg-secondary border border-border-light rounded-xl text-brand-neutral flex items-center justify-center shrink-0">
                                        <i class="ph ph-file-text text-base block"></i>
                                    </span>
                                    <div>
                                        <h4 class="font-bold text-xs text-text-main">{{ $doc->name }}</h4>
                                        <p class="text-[10px] text-text-muted">
                                            {{ round($doc->file_size / 1024, 1) }} KB &bull; Uploaded by: {{ $doc->uploader->name }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 shrink-0">
                                    <button wire:click="downloadDocument({{ $doc->id }})" 
                                            class="text-[11px] font-bold px-3 py-1.5 border border-border-card rounded-lg hover:bg-bg-secondary transition cursor-pointer">
                                        Download
                                    </button>
                                    
                                    @if (auth()->id() === $doc->uploaded_by || $trip->canManageMembers(auth()->user()))
                                        <button wire:click="deleteDocument({{ $doc->id }})" 
                                                wire:confirm="Permanently delete this file?"
                                                class="text-text-muted hover:text-red-600 transition p-1.5 bg-bg-secondary hover:bg-red-50 rounded-lg cursor-pointer">
                                            <i class="ph ph-trash text-sm block"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
