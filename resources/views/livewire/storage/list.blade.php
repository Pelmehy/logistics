<?php

use App\Models\Material;
use App\Models\Product;
use App\Models\Storage;

use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

new class extends Component {
    use Toast, WithPagination;

    public bool $drawer = false;
    public array $expanded = [1];

    public string $search = '';
    public array $sortBy = ['column' => 'address', 'direction' => 'asc'];

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'address', 'label' => 'Address', 'class' => 'w-32'],
            ['key' => 'height', 'label' => 'Height', 'class' => 'w-4'],
            ['key' => 'square', 'label' => 'Square', 'class' => 'w-4'],
            ['key' => 'capacity', 'label' => 'Capacity', 'class' => 'w-32', 'sortable' => false],
        ];
    }

    public function headersItems(): array
    {
        return [
            ['key' => 'url', 'label' => 'img', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-16'],
            ['key' => 'type', 'label' => 'Type', 'class' => 'w-16'],
            ['key' => 'capacity', 'label' => 'Quantity', 'class' => 'w-8'],
        ];
    }

    public function storageItems(Storage $storage)
    {
        $items = $storage->products()->select('id, name, url')->where('id', '>', 0);
        return $storage->materials()->select('id, name, url')->union($items);
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

    public function delete(Storage $storage): void
    {
        $storage->materials()->detach();
        $storage->storage()->detach();
        $storage->delete();
        $this->warning("$storage->name deleted", 'Good bye!', position: 'toast-bottom');
    }

    public function storage(): LengthAwarePaginator
    {
        return Storage::query()
            ->when($this->search, fn(Builder $q) => $q->where('address', 'like', "%$this->search%"))
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'storages' => $this->storage(),
            'filterCount' => $this->filterCount(),
            'headersItems' => $this->headersItems(),
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Hello" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel"
                      badge="{{ $filterCount ?: null }}"/>
            <x-button label="Create" link="/storage/create" responsive icon="o-plus" class="btn-primary"/>
        </x-slot:actions>
    </x-header>

    <div class="columns-2 mb-2">
        <x-card class="p-6" shadow separator>
            <div id="chart1"></div>
        </x-card>
        <x-card class="p-6" shadow separator>
            <div id="chart2"></div>
        </x-card>
    </div>


    <script>
        $(document).ready(function () {
            var options = {
                chart: {
                    type: 'pie',
                    height: 300,
                },
                series: [44, 55, 41, 17, 15],
                chartOptions: {
                    labels: ['Apple', 'Mango', 'Orange', 'Watermelon']
                },
            }

            var chart1 = new ApexCharts(document.querySelector("#chart1"), options);
            var chart2 = new ApexCharts(document.querySelector("#chart2"), options);

            chart1.render();
            chart2.render();
        })
    </script>

    <!-- TABLE  -->
    <x-card>
        <x-table
        :headers="$headers"
        :rows="$storages"
        :sort-by="$sortBy"
        wire:model="expanded"
        expandable
        with-pagination
        link="/storage/{id}/edit">
            @scope('cell_capacity', $storage)
                @php
                    $capacity = $storage->height * $storage->square;
                    $loadMaterials = $storage->materials()->sum('storage_quantity');
                    $loadProducts = $storage->products()->sum('storage_quantity');

                    $load = $loadMaterials + $loadProducts;
                @endphp
                <div class="grid grid-flow-col auto-cols-auto">
                    <div class="mr-2 min-w-8">
                        <x-progress value="{{$loadProducts}}" max="{{$capacity}}" class="progress-warning h-3" />
                    </div>
                    <div class="min-w-8">
                        {{$loadProducts}} / {{$capacity}}
                    </div>
                </div>
            @endscope

            @scope('actions', $storage)
            <x-button
                icon="o-trash"
                wire:click="delete({{$storage['id']}})"
                wire:confirm="Are you sure?"
                spinner
                class="btn-ghost btn-sm text-red-500"/>
            @endscope

            @scope('expansion', $storage, $headersItems)
                <x-table
                :headers="$headersItems"
                :rows="$storage->items()->get()"
                >
                    @scope('cell_url', $item)
                        <x-avatar :image="$item->url ?: '/empty-product.png'" class="!w-8 !rounded-lg"/>
                    @endscope

                    @scope('cell_capacity', $item, $storage)
                        @php
                            $capacity = $storage->height * $storage->square;
                        @endphp
                        <div class="grid grid-flow-col auto-cols-auto">
                            <div class="mr-2">
                                <x-progress value="{{$item->storage_quantity}}" max="{{$capacity}}" class="progress-warning h-3" />
                            </div>
                            <div class="">
                                {{$item->storage_quantity}}
                            </div>
                        </div>
                    @endscope

                    @scope('actions', $storage)
                        <x-button
                            label="Make order"
                            link="#TODO_add_link_to_order_page"
                            class="btn btn-outline btn-success btn-sm"
                        />
                    @endscope

                </x-table>
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
