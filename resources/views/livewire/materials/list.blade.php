<?php

use App\Models\Material;

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
            ['key' => 'зображ.', 'label' => 'Зображ.', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Ім\'я.', 'class' => 'w-64'],
            ['key' => 'quantity', 'label' => 'Кількість', 'class' => 'w-8'],
            ['key' => 'description', 'label' => 'Опис', 'sortable' => false],
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

    public function delete(Material $material): void
    {
        $material->products()->detach();
        $material->delete();
        $this->warning("$material->name deleted", 'Good bye!', position: 'toast-bottom');
    }

    public function materials(): LengthAwarePaginator
    {
        return Material::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'materials' => $this->materials(),
            'filterCount' => $this->filterCount(),
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Матеріали" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Пошук..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Фільтри" @click="$wire.drawer = true" responsive icon="o-funnel" badge="{{ $filterCount ?: null }}" />
            <x-button label="Створити" link="/materials/create" responsive icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table :headers="$headers" :rows="$materials" :sort-by="$sortBy" with-pagination link="/materials/{id}/edit">
            @scope('actions', $material)
            <x-button icon="o-trash" wire:click="delete({{$material['id']}})" wire:confirm="Ви впевнені?" spinner class="btn-ghost btn-sm text-red-500" />
            @endscope

            @scope('cell_url', $material)
            <x-avatar :image="$material->url ?: '/empty-product.png'" class="!w-8 !rounded-lg" />
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Фільтри" right separator with-close-button class="lg:w-1/3">
        <x-input placeholder="Пошук..." wire:model.live.debounce="search" icon="o-magnifying-glass" @keydown.enter="$wire.drawer = false" />

        <x-slot:actions>
            <x-button label="Скинути" icon="o-x-mark" wire:click="clear" spinner />
            <x-button label="Застосувати" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>
