<?php

use App\Models\Product;
use App\Models\Material;

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use Toast, WithFileUploads;

    // You could use Livewire "form object" instead.
    #[Rule('required')]
    public string $name = '';

    #[Rule('sometimes')]
    public string $description = '';

    #[Rule('required')]
    public array $product_materials = [];

    #[Rule('nullable|integer')]
    public ?int $quantity = null;

    #[Rule('nullable|image|max:1024')]
    public $photo;

    // We also need this to fill Countries combobox on upcoming form
    public function with(): array
    {
        return [
            'materials' => Material::all(),
        ];
    }

    public function save(): void
    {
        // Validate
        $data = $this->validate();

        if ($this->_isProductExists($data['name'])) {
            $this->error('Product already exists.', redirectTo: '/products/create');
            return;
        }

        $product = new Product();
        $product->name = $data['name'];
        $product->description = $data['description'];
        $product->quantity = $data['quantity'] ?: 0;
        $product->save();

        $product->materials()->sync($this->product_materials);

        if ($this->photo) {
            if ($product->url) {
                $urlArr = explode('storage/', $product->url);
                $path = count($urlArr) > 1
                    ? $urlArr[1]
                    : '';
                $storageUrl = 'public/' . $path;

                Storage::exists($storageUrl)
                    ? Storage::delete($storageUrl)
                    : $isWarning = true;
            }

            $url = $this->photo->store('product', 'public');
            $product->update(['url' => "/storage/$url"]);
        }

        // You can toast and redirect to any route
        $this->success('Product updated with success.', redirectTo: '/products/' . $product->id . '/edit');
    }

    private function _isProductExists(string $name): bool
    {
        return (bool)Product::query()->where('name', $name)->first();
    }
}; ?>

<div>
    <x-header title="Create Product" separator/>
    <div class="grid gap-5 lg:grid-cols-2">
        <div class="">
            <x-form wire:submit="save">
                <x-file label="Image" wire:model="photo" accept="image/png, image/jpeg" crop-after-change>
                    <img src="/empty-product.png" class="h-40 rounded-lg"/>
                </x-file>

                <x-input label="Name" wire:model="name"/>
                <x-textarea
                    label="Description"
                    wire:model="description"
                    placeholder="Add product description here ..."
                    hint="Max 1000 chars"
                    rows="5"
                    inline/>

                <x-input
                    label="Quantity"
                    wire:model="quantity"
                    type="number"
                />

                <x-choices-offline
                    label="Materials"
                    wire:model="product_materials"
                    :options="$materials"
                    searchable/>

                <x-slot:actions>
                    <x-button label="Cancel" link="/users"/>
                    {{-- The important thing here is `type="submit"` --}}
                    {{-- The spinner property is nice! --}}
                    <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
                </x-slot:actions>
            </x-form>
        </div>
        <div class="">
            <img src="" width="300" class="mx-auto"/>
        </div>
    </div>
</div>
