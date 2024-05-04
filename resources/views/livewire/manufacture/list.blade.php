<?php

use App\Models\Material;
use App\Models\Manufacture;

use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

new class extends Component {
    use Toast, WithPagination;

    public bool $drawer = false;

    public string $search = '';
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-32'],
            ['key' => 'address', 'label' => 'Address', 'class' => 'w-64'],
            ['key' => 'materials', 'label' => 'Materials', 'sortable' => false, 'class' => 'w-56'],
        ];
    }

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    public function filterCount(): int
    {
        $count = 0;
        if ($this->search) {
            $count += 1;
        }

        return $count;
    }

    public function delete(Manufacture $manufacture): void
    {
        $manufacture->materials()->detach();
        $manufacture->delete();
        $this->warning("$manufacture->name deleted", 'Good bye!', position: 'toast-bottom');
    }

    public function manufactures(): LengthAwarePaginator
    {
        return Manufacture::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'manufactures' => $this->manufactures(),
            'filterCount' => $this->filterCount(),
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Manufactures" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel"
                      badge="{{ $filterCount ?: null }}"/>
            <x-button label="Create" link="/manufactures/create" responsive icon="o-plus" class="btn-primary"/>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table :headers="$headers" :rows="$manufactures" :sort-by="$sortBy" with-pagination link="/manufacture/{id}/edit">
            @scope('actions', $manufacture)
            <x-button icon="o-trash" wire:click="delete({{$manufacture['id']}})" wire:confirm="Are you sure?" spinner
                      class="btn-ghost btn-sm text-red-500"/>
            @endscope

            @scope('cell_materials', $manufacture)
            @foreach($manufacture->materials as $material)
                <x-badge :value="$material->name" class="badge-primary m-1" />
            @endforeach
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass"
                 @keydown.enter="$wire.drawer = false"/>

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner/>
            <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer = false"/>
        </x-slot:actions>
    </x-drawer>
</div>
