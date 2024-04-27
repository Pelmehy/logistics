<?php

use App\Models\Order;
use App\Enums\Statuses;
use App\Models\Material;
use App\Models\Product;

use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Rule;

new class extends Component {
    use Toast, WithPagination;

    public Order $order;
    public $orderItems;
    public string $status;
    public bool $showDrawer = false;

    #[Rule('required|numeric')]
    public int $itemId;

    #[Rule('required|numeric')]
    public int $quantity;

    public function headers(): array
    {
        return [
            ['key' => 'url', 'label' => '', 'class' => 'w-14'],
            ['key' => 'name', 'label' => 'Name', 'class' => ''],
            ['key' => 'count', 'label' => 'Qty', 'class' => ''],
            ['key' => 'price', 'label' => 'Price', 'class' => ''],
            ['key' => 'total', 'label' => 'Total', 'class' => ''],

        ];
    }

    public function updateQuantity($itemId, int $count): void
    {
        $item = $this->order->getOrderItems()->where('id', $itemId)->first()->orderItems;
        $item->count = $count;
        $item->total = $count * $item->price;

        $item->save();

        $this->order->updateOrderTotal();
    }

    public function delete($itemId): void
    {
        $item = $this->order->getOrderItems()->where('id', $itemId)->first()->orderItems;
        $item->delete();
        $this->order->updateOrderTotal();

        $this->js('window.location.reload()');
    }

    public function save(): void
    {
        //syncWithoutDetaching
        $this->validate();

        if ($this->order->client_id) {
            $dataType = 'product';
            $item = Product::where('id', $this->itemId)->first();
            $price = $item->price;
        } else {
            $dataType = 'material';
            $item = Material::where('id', $this->itemId)->first();
            $price = $this->order
                ->manufacture
                ->materials()->where('id', $this->itemId)->first()
                ->pivot->price;
        }

        $data = [
            $dataType . '_id' => $this->itemId,
            'count' => $this->quantity,
            'price' => $price,
            'total' => $price * $this->quantity,
        ];

        $dataType .= 's';
        $this->order->$dataType()->syncWithoutDetaching([$data]);
        $this->showDrawer = false;
    }

    public function updated($property): void
    {
        if (!is_array($property) && $property != "") {
            $this->resetPage();
        }
    }

    public function changeStatus(bool $isNext = true): void
    {
        $statuses = Statuses::cases();
        $statusesCount = count($statuses);

        $i = 0;
        while ($i < $statusesCount) {
            if ($this->order->status === $statuses[$i]->name) {
                // validate status change action
                if ($isNext && $i === $statusesCount - 1) {
                    return;
                } elseif (!$isNext && $i === 0) {
                    return;
                }

                // update order status
                $this->order->status = $isNext
                    ? $statuses[$i + 1]->name
                    : $statuses[$i - 1]->name;

                $this->order->save();

                return;
            }

            $i++;
        }
    }

    public function getAvailableItems(): Collection
    {
        return $this->order->client_id
            ? Product::whereNotIn('id', $this->order->products()->pluck('id'))->get()
            : $this->order->manufacture
                ->materials()
                ->whereNotIn('id', $this->order->materials()
                    ->pluck('id'))->get();
    }

    public function with(): array
    {
        if ($this->order->client_id) {
            $this->orderItems = $this->order->products;
            $link = '/products/';
        } else {
            $this->orderItems = $this->order->materials;
            $link = '/materials/';
        }

        return [
            'order' => $this->order,
            'orderItems' => $this->orderItems,
            'headers' => $this->headers(),
            'itemsList' => $this->getAvailableItems()
        ];
    }
}; ?>

<div>
    <x-header title="Order #{{$order->id}}" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Delete" link="/materials/create" responsive icon="o-trash" class="btn-error"/>
        </x-slot:actions>
    </x-header>
    <div class="grid lg:grid-cols-2 gap-8">
        <x-card title="Customer" shadow separator>
            <x-slot:menu>
                <x-button label="Change" icon="o-pencil"/>
            </x-slot:menu>
            @php
                if ($order->client_id) {
                    $customer = $order->client;
                    $orderItems = $order->products;
                } else {
                    $customer = (object) [
                        'id' => 0,
                        'name' => 'Me',
                        'email' => '0',
                        'phone' => '0',
                        'address' => '0'
                    ];
                    $orderItems = $order->materials;
                }
            @endphp
            <x-card title="{{$customer->name}}" class="!p-0">
                <x-slot:subtitle class="text-gray-500 flex flex-col gap-2 mt-2 pl-2">
                    <x-icon name="o-envelope" label="{{$customer->email}}"/>
                    <x-icon name="o-phone" label="{{$customer->phone}}"/>
                    <x-icon name="o-map-pin" label="{{$customer->address}}"/>
                </x-slot:subtitle>
            </x-card>
        </x-card>
        <x-card title="Summary" shadow separator>
            <x-slot:menu>
                <x-badge value="{{$order->status}}" class="bg-purple-500/20"/>
            </x-slot:menu>

            <div class="grid gap-2">
                <div class="flex gap-3 justify-between items-baseline px-10">
                    <div>Items</div>
                    <div class="border-b border-b-gray-400 border-dashed flex-1"></div>
                    <div class="font-black">({{count($orderItems)}})</div>
                </div>
                <div class="flex gap-3 justify-between items-baseline px-10">
                    <div>Total</div>
                    <div class="border-b border-b-gray-400 border-dashed flex-1"></div>
                    <div class="font-black">{{$order->total}} $</div>
                </div>
            </div>

            <div class="px-2 mt-5">
                @php
                    $pending = false;
                    $background = 'bg-primary';
                @endphp
                <ol class="items-center sm:flex">
                    @foreach(Statuses::cases() as $status)
                        <li class="relative mb-6 sm:mb-0 px-2">
                            <div class="flex items-center">
                                <div
                                    class="z-10 flex items-center justify-center w-7 h-7 {{$pending ? 'bg-base-300' : 'bg-primary'}} rounded-full ring-0 ring-white dark:bg-blue-900 sm:ring-8 dark:ring-gray-900 shrink-0">
                                    <x-icon name="{{$status->value}}" class="{{$pending ? '' : 'text-base-100'}}"/>
                                </div>
                                @if(!$loop->last)
                                    <div
                                        class="hidden sm:flex w-full {{$pending ? 'bg-gray-200' : 'bg-primary'}} h-0.5 dark:bg-gray-700"></div>
                                @endif
                            </div>
                            <div class="mt-3 sm:pe-8 min-w-24">
                                <div class="font-bold mb-1">{{$status->name}}</div>
                            </div>
                        </li>
                        @php
                            if (!$pending) {
                                $pending = $order->status == $status->name;
                            }
                        @endphp
                    @endforeach
                </ol>
            </div>

            <x-slot:actions>
                @if($order->status !== Statuses::placed->name)
                    <x-button label="Prev status" wire:click="changeStatus(false)" class=""/>
                @endif
                @if($order->status !== Statuses::delivered->name)
                    <x-button label="Next status" wire:click="changeStatus()" class=""/>
                @endif
            </x-slot:actions>
        </x-card>
    </div>

    <x-card title="Items" class="mt-5" shadow separator>
        <x-slot:menu>
            <x-button label="Add" icon="o-plus" wire:click="$toggle('showDrawer')"/>
        </x-slot:menu>

        <x-table
            :headers="$headers"
            :rows="$orderItems"
        >
            @scope('cell_url', $orderItem)
            <x-avatar :image="$orderItem->url ?: '/empty-product.png'" class="!w-14 !rounded-lg"/>
            @endscope

            @scope('cell_count', $orderItem)
            <x-input class="!max-w-24" type="number"
                     wire:change="updateQuantity({{$orderItem->id}}, $event.target.value)"
                     value="{{$orderItem->orderItems->count}}" placeholder="item count"/>
            @endscope

            @scope('cell_price', $orderItem)
            {{ $orderItem->orderItems->price }} $
            @endscope

            @scope('cell_total', $orderItem)
            {{ $orderItem->orderItems->total }} $
            @endscope

            @scope('actions', $orderItem)
            <x-button icon="o-trash" wire:click="delete({{ $orderItem->id }})" wire:confirm="Are you sure?" spinner
                      class="btn-ghost btn-sm text-red-500"/>
            @endscope
        </x-table>
    </x-card>

    <x-drawer wire:model="showDrawer" title="Add item" right separator with-close-button class="lg:w-1/3">
        <x-form wire:submit="save" class="grid grid-flow-row auto-rows-min gap-3">
            <x-choices-offline
                label="{{ $order->client_id ? 'Products' : 'Materials' }}"
                wire:model="itemId"
                :options="$itemsList"
                icon="o-magnifying-glass"
                height="max-h-96"
                hint="Search for product name"
                single
                searchable
            >
                @scope('item', $item)
                    <x-list-item :item="$item" no-hover>
                        <x-slot:avatar>
                            <x-avatar :image="$item->url ?: '/empty-product.png'" class="!w-14 !rounded-lg" />
                        </x-slot:avatar>
                        <x-slot:value>
                            {{ $item->name }}
                        </x-slot:value>
                        <x-slot:actions>
                            <x-badge :value="$item->price . '$'" />
                        </x-slot:actions>
                    </x-list-item>
                @endscope
            </x-choices-offline>

            <x-input label="Quantity" placeholder="0" wire:model="quantity" />

            <x-slot:actions>
                <x-button label="Cancel" class="btn-outline btn-error" />
                <x-button label="Add" class="btn-outline btn-success" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-drawer>
</div>
