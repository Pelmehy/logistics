<?php

use App\Models\Order;

use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

new class extends Component {
    use Toast, WithPagination;

    public bool $drawer = false;
//    public bool $drawer = true;

    public string $search = '';
    public string $statusType = '';
    public string $warningType = '';
    public bool $receiverSearch = false;
    public int|null $receiverID = null;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'created_at', 'label' => 'Insert Date', 'class' => 'w-1'],
            ['key' => 'due_date', 'label' => 'Due Date', 'class' => 'w-1'],
            ['key' => 'client_id', 'label' => 'Receiver', 'class' => 'w-1'],
            ['key' => 'total', 'label' => 'Total', 'class' => 'w-1'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-1'],
            ['key' => 'warnings', 'label' => 'Warnings', 'class' => 'w-1'],
        ];
    }

    public function orders(): LengthAwarePaginator
    {
        return Order::query()
            ->when($this->receiverSearch, fn(Builder $q) => $q->where('client_id', $this->receiverID))
            ->when($this->statusType, fn(Builder $q) => $q->where('status', $this->statusType))
            ->paginate(10);
    }

    public function testData()
    {
        return [
            (object)[
                'id' => 1,
                'insert_date' => 'Mar 29, 2024',
                'due_date' => 'Mar 30, 2025',
                'receiver' => 'Me',
                'total' => 100000,
                'status' => 'pending',
                'warnings' => 0,
            ],
            (object)[
                'id' => 1,
                'insert_date' => 'Mar 29, 2024',
                'due_date' => 'Mar 30, 2025',
                'receiver' => 'Me',
                'total' => 100000,
                'status' => 'pending',
                'warnings' => 0,
            ],
            (object)[
                'id' => 1,
                'insert_date' => 'Mar 29, 2024',
                'due_date' => 'Mar 30, 2025',
                'receiver' => 'Me',
                'total' => 100000,
                'status' => 'pending',
                'warnings' => 0,
            ],
        ];
    }

    public function filterCount(): int
    {
        $count = 0;
        if ($this->search) {
            $count += 1;
        }

        if ($this->statusType) {
            $count += 1;
        }

        if ($this->warningType) {
            $count += 1;
        }

        return $count;
    }

    public function with(): array
    {
        return [
            'headers' => $this->headers(),
            'orders' => $this->orders(),
            'filterCount' => $this->filterCount(),
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Orders" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass"/>
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel"
                      badge="{{ $filterCount ?: null }}"/>
            <x-button label="Add order" link="/materials/create" responsive icon="o-plus" class="btn-primary"/>
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-table
            :headers="$headers"
            :rows="$orders"
            :sort-by="$sortBy"
            with-pagination
            link="/orders/{id}/view"
        >
            @scope('cell_client_id', $order)
            {{$order->client->name}}
            @endscope

            @scope('cell_status', $order)
            <x-badge :value="$order->status" class="badge-primary"/>
            @endscope

            @scope('cell_warnings', $order)
            <x-badge :value="$order->warnings" class="badge-error"/>
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <x-input
            placeholder="Search..."
            wire:model.live.debounce="search"
            icon="o-magnifying-glass"
            @keydown.enter="$wire.drawer = false"/>

        <x-select
            placeholder="Status"
            class="mt-4"
            wire:model.live.debounce="statusType"
            :options="[
                ['id' => '', 'status' => 'None'],
                ['id' => 'placed', 'status' => 'placed'],
                ['id' => 'paid', 'status' => 'Paid'],
                ['id' => 'produced', 'status' => 'Produced'],
                ['id' => 'shipped', 'status' => 'Shipped'],
                ['id' => 'delivered', 'status' => 'Delivered'],
            ]"
            {{--            option-value="status"--}}
            option-label="status"
            placeholder-value="0"
            @keydown.enter="$wire.drawer = false"/>

        <x-select
            placeholder="Warnings"
            class="mt-4"
            wire:model.live.debounce="warningType"
            :options="[
                ['id' => 1, 'warning' => 'Overdue'],
                ['id' => 2, 'warning' => 'canceled'],
                ['id' => 3, 'warning' => 'unconfirmed'],
                ['id' => 4, 'warning' => 'none'],
            ]"
            option-value="id"
            option-label="warning"
            placeholder-value="0"
            @keydown.enter="$wire.drawer = false"/>

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner/>
            <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer = false"/>
        </x-slot:actions>
    </x-drawer>
</div>
