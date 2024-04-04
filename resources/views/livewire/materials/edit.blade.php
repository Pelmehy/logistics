<?php

use App\Models\Material;

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use Toast, WithFileUploads;

    public Material $material;

    #[Rule('required')]
    public string $name = '';

    #[Rule('sometimes')]
    public string $description = '';

    // Optional
    #[Rule('nullable|integer')]
    public ?int $quantity = null;


    #[Rule('nullable|image|max:1024')]
    public $photo;

    public function with(): array
    {
        return [];
    }

    public function mount(): void
    {
        $this->fill($this->material);
    }

    public function save(): void
    {
        $isWarning = false;

        // Validate
        $data = $this->validate();

        // Update
        $this->material->update($data);

        if ($this->photo) {
            if ($this->material->url) {
                $storageUrl = 'public/' . explode('storage/', $this->material->url)[1];

                Storage::exists($storageUrl)
                    ? Storage::delete($storageUrl)
                    : $isWarning = true;
            }

            $url = $this->photo->store('materials', 'public');
            $this->material->update(['url' => "/storage/$url"]);
        }

        $isWarning
            ? $this->warning('Material updated but old file is not found. ' . $storageUrl, redirectTo: '/materials/' . $this->material->id . '/edit')
            : $this->success('Material updated with success.', redirectTo: '/materials/' . $this->material->id . '/edit');
        // You can toast and redirect to any route
    }
}; ?>

<div>
    <x-header title="Create Material" separator />
    <div class="grid gap-5 lg:grid-cols-2">
        <div class="">
            <x-form wire:submit="save">
                <x-file label="Image" wire:model="photo" accept="image/png, image/jpeg" crop-after-change>
                    <img src="{{ $material->url ?? '/empty-user.jpg' }}" class="h-40 rounded-lg" />
                </x-file>

                <x-input label="Name" wire:model="name" />
                <x-textarea
                    label="Description"
                    wire:model="description"
                    placeholder="Add product description here ..."
                    hint="Max 1000 chars"
                    rows="5"
                    inline />

                <x-input label="Quantity" wire:model="quantity" type="number" />

                <x-slot:actions>
                    <x-button label="Cancel" link="/materials/create" />
                    <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
                </x-slot:actions>
            </x-form>
        </div>
        <div class="">
            <img src="" width="300" class="mx-auto" />
        </div>
    </div>
</div>
