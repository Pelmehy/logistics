<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Livewire\Attributes\Rule;

use App\Models\User;
use App\Models\Language;

new class extends Component {
    use Toast, WithFileUploads;

    public User $user;

    // You could use Livewire "form object" instead.
    #[Rule('required')]
    public string $name = '';

    #[Rule('required|email')]
    public string $email = '';

    // Optional
    #[Rule('nullable|image|max:1024')]
    public $photo;

    // We also need this to fill Countries combobox on upcoming form
    public function with(): array
    {
        return [
        ];
    }

    public function mount(): void
    {
        $this->fill($this->user);
    }

    public function save(): void
    {
        // Validate
        $data = $this->validate();

        // Update
        $this->user->update($data);

        if ($this->photo) {
            $url = $this->photo->store('users', 'public');
            $this->user->update(['avatar' => "/storage/$url"]);
        }

        // You can toast and redirect to any route
        $this->success('User updated with success.', redirectTo: '/users/' . $this->user->id . '/edit');
    }
}; ?>

<div>
    <x-header title="Оновити {{ $user->name }}" separator />
    <div class="grid gap-5 lg:grid-cols-2">
        <div class="">
            <x-form wire:submit="save">
                <x-file label="Аватар" wire:model="photo" accept="image/png, image/jpeg" crop-after-change>
                    <img src="{{ $user->avatar ?? '/empty-user.jpg' }}" class="h-40 rounded-lg" />
                </x-file>

                <x-input label="Ім'я" wire:model="name" />
                <x-input label="Email" wire:model="email" />

                <x-slot:actions>
                    <x-button label="Відміна" link="/users" />
                    {{-- The important thing here is `type="submit"` --}}
                    {{-- The spinner property is nice! --}}
                    <x-button label="Зберегти" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
                </x-slot:actions>
            </x-form>
        </div>
        <div class="">
            <img src="" width="300" class="mx-auto" />
        </div>
    </div>
</div>

