<?php

use App\Models\Order;
use App\Enums\Statuses;

use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;

new class extends Component {
    use Toast, WithPagination;

    public Order $order;
    public string $status;

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

    public function with(): array
    {
        if ($this->order->client_id) {
            $orderItems = $this->order->products;
            $link = '/products/';
        } else {
            $orderItems = $this->order->materials;
            $link = '/materials/';
        }

        return [
            'order' => $this->order,
            'orderItems' => $orderItems,
            'headers' => $this->headers(),
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
                                <div class="z-10 flex items-center justify-center w-7 h-7 {{$pending ? 'bg-base-300' : 'bg-primary'}} rounded-full ring-0 ring-white dark:bg-blue-900 sm:ring-8 dark:ring-gray-900 shrink-0">
                                    <x-icon name="{{$status->value}}" class="{{$pending ? '' : 'text-base-100'}}" />
                                </div>
                                @if(!$loop->last)
                                    <div class="hidden sm:flex w-full {{$pending ? 'bg-gray-200' : 'bg-primary'}} h-0.5 dark:bg-gray-700"></div>
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
                <x-button label="Change status" class=""/>
            </x-slot:actions>
        </x-card>
    </div>

    <x-card title="Items" class="mt-5" shadow separator>
        <x-slot:menu>
            <x-button label="Add" icon="o-plus"/>
        </x-slot:menu>

        <x-table
            :headers="$headers"
            :rows="$orderItems"
        >
            @scope('cell_url', $orderItem)
                <x-avatar :image="$orderItem->url ?: '/empty-product.png'" class="!w-14 !rounded-lg"/>
            @endscope

            @scope('cell_count', $orderItem)
                <x-input class="!max-w-16" type="number" wire:model="" value="{{$orderItem->orderItems->count}}" placeholder="item count" />
            @endscope

            @scope('cell_price', $orderItem)
            {{ $orderItem->orderItems->price }} $
            @endscope

            @scope('cell_total', $orderItem)
                {{ $orderItem->orderItems->total }} $
            @endscope
        </x-table>
    </x-card>
</div>
