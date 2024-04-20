<?php

use App\Models\Client;
use App\Models\Country;

use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

new class extends Component {
    use Toast;
    use WithPagination;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public int $email = 0;

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    // Delete action
    public function delete(Client $client): void
    {
        $client->delete();
        $this->warning("$client->name deleted", 'Good bye!', position: 'toast-bottom');
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-32'],
            ['key' => 'email', 'label' => 'E-mail'],
            ['key' => 'phone', 'label' => 'Phone'],
            ['key' => 'address', 'label' => 'Country', 'class' => 'hidden lg:table-cell'],
        ];
    }

    /**
     * For demo purpose, this is a static collection.
     *
     * On real projects you do it with Eloquent collections.
     * Please, refer to maryUI docs to see the eloquent examples.
     */
    public function clients(): LengthAwarePaginator
    {
        return Client::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->when($this->email, fn(Builder $q) => $q->where('email', $this->email))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(5); // No more `->get()`
    }

    public function updated($property): void
    {
        if (! is_array($property) && $property != "") {
            $this->resetPage();
        }
    }

    public function with(): array
    {
        return [
            'clients' => $this->clients(),
            'headers' => $this->headers(),
            'countries' => Country::all(),
            'filterCount' => $this->filterCount(),
        ];
    }

    public function filterCount(): int
    {
        $count = 0;
        if ($this->search) {
            $count += 1;
        }

        if ($this->email) {
            $count += 1;
        }

        return $count;
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Hello" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" badge="{{ $filterCount ?: null }}" />
            <x-button label="Create" link="/clients/create" responsive icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table :headers="$headers" :rows="$clients" :sort-by="$sortBy" with-pagination link="clients/{id}/edit">
            @scope('cell_phone', $client)
                +38{{$client->phone}}
            @endscope

            @scope('actions', $client)
            <x-button icon="o-trash" wire:click="delete({{ $client['id'] }})" wire:confirm="Are you sure?" spinner class="btn-ghost btn-sm text-red-500" />
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass" @keydown.enter="$wire.drawer = false" />
        <x-select placeholder="Country" class="mt-2" wire:model.live="email" :options="$countries" icon="o-flag" placeholder-value="0" />

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>
