<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Livewire\Attributes\Rule;

use App\Models\Client;

new class extends Component {
    use Toast, WithFileUploads;

    // You could use Livewire "form object" instead.
    #[Rule('required')]
    public string $name = '';

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required|numeric:10')]
    public string $phone = '';

    #[Rule('required')]
    public string $address = '';
    // Optional

    // We also need this to fill Countries combobox on upcoming form
    public function with(): array
    {
        return [
        ];
    }

    public function save(): void
    {
        // Validate
        $data = $this->validate();

        // Update
        if ($this->_isClientExists($data['email'])) {
            $this->error('Client already exists.', redirectTo: '/users/create');
            return;
        }

        $client = new Client();
        $client->name = $data['name'];
        $client->email = $this->email;
        $client->phone = $this->phone;
        $client->address = $this->address;
        $client->save();

        // You can toast and redirect to any route
        $this->success('Client updated with success.', redirectTo: '/clients/' . $client->id . '/edit');
    }

    private function _isClientExists(string $email): bool
    {
        if (
            Client::query()->where('email', $email)->first()
        ) {
            return true;
        }

        return false;
    }
}; ?>

<div>
    <x-header title="Create Client" separator />
    <div class="grid gap-5 lg:grid-cols-2">
        <div class="">
            <x-form wire:submit="save">
                <x-input label="Name" wire:model="name" />
                <x-input label="Email" wire:model="email" />
                <x-input label="Phone" wire:model="phone" />
                <x-input label="Address" wire:model="address" />

                <x-slot:actions>
                    <x-button label="Cancel" link="/users" />
                    {{-- The important thing here is `type="submit"` --}}
                    {{-- The spinner property is nice! --}}
                    <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
                </x-slot:actions>
            </x-form>
        </div>
        <div class="">
            <img src="" width="300" class="mx-auto" />
        </div>
    </div>
</div>

