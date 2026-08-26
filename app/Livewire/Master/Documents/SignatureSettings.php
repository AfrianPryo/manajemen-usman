<?php

namespace App\Livewire\Master\Documents;

use App\Models\AuditLog;
use App\Models\SignatureProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Pengaturan Tanda Tangan')]
class SignatureSettings extends Component
{
    use WithFileUploads;

    public ?int $editingId = null;
    public string $name = '';
    public string $position = '';
    public $signatureImage;
    public bool $is_default = false;

    public function render()
    {
        return view('livewire.master.documents.signature-settings', [
            'signatures' => SignatureProfile::where('user_id', Auth::id())->latest()->get(),
        ]);
    }

    public function edit(int $id): void
    {
        $profile = SignatureProfile::where('user_id', Auth::id())->findOrFail($id);
        $this->editingId = $profile->id;
        $this->name = $profile->name;
        $this->position = $profile->position;
        $this->is_default = $profile->is_default;
        $this->signatureImage = null;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:150',
            'position' => 'required|string|max:150',
            'signatureImage' => ($this->editingId ? 'nullable' : 'required') . '|image|max:1024',
        ]);

        $isUpdate = (bool) $this->editingId;
        $oldValues = $isUpdate
            ? SignatureProfile::where('user_id', Auth::id())->find($this->editingId)?->toArray()
            : null;

        $data = [
            'name' => $this->name,
            'position' => $this->position,
            'is_default' => $this->is_default,
        ];

        if ($this->signatureImage) {
            $data['signature_path'] = $this->signatureImage->store('signatures');
        }

        if ($this->is_default) {
            SignatureProfile::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        if ($this->editingId) {
            $profile = SignatureProfile::where('user_id', Auth::id())->findOrFail($this->editingId);
            $profile->update($data);

            AuditLog::record(
                'SIGNATURE_PROFILE_UPDATE',
                $profile->name,
                "Profil tanda tangan '{$profile->name}' diperbarui.",
                $oldValues,
                $profile->fresh()->toArray()
            );

            session()->flash('success', 'Profil tanda tangan diperbarui.');
        } else {
            $data['user_id'] = Auth::id();
            $profile = SignatureProfile::create($data);

            AuditLog::record(
                'SIGNATURE_PROFILE_CREATE',
                $profile->name,
                "Profil tanda tangan baru '{$profile->name}' ditambahkan.",
                null,
                $profile->toArray()
            );

            session()->flash('success', 'Profil tanda tangan ditambahkan.');
        }

        $this->reset(['editingId', 'name', 'position', 'signatureImage', 'is_default']);
    }

    public function delete(int $id): void
    {
        $profile = SignatureProfile::where('user_id', Auth::id())->findOrFail($id);
        $oldValues = $profile->toArray();

        if ($profile->signature_path) {
            Storage::delete($profile->signature_path);
        }

        $profile->delete();

        AuditLog::record(
            'SIGNATURE_PROFILE_DELETE',
            $profile->name,
            "Profil tanda tangan '{$profile->name}' dihapus.",
            $oldValues,
            null
        );

        session()->flash('success', 'Profil tanda tangan dihapus.');
    }
}