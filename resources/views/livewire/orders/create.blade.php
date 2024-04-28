<?php

use App\Models\Product;
use App\Models\Material;
use App\Models\Client;
use App\Models\Order;
use App\Models\Manufacture;
use App\Enums\Statuses;

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Livewire\Attributes\Rule;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Validate;

new class extends Component {
    use Toast, WithFileUploads;

    /*
     TODO: add
        due date
        receiver
        materials/products list
     */
    // You could use Livewire "form object" instead.
    #[Rule('required')]
    public string $dueDate = '';

    #[Rule('required|numeric')]
    public ?int $receiverId = null;

    public ?int $manufactureId = 0;

    public array $items = [];

    public array $itemIds = [];

    #[Validate([
        'itemsCount.*' =>  'required|numeric',
    ])]
    public array $itemsCount = [];

    // We also need this to fill Countries combobox on upcoming form
    public function with(): array
    {
        if (!is_null($this->receiverId)) {
            $listItems = $this->receiverId === 0
                ? Material::all()
                : Product::all();
        }

        $itemsData = $this->getItemsData();

        return [
            'listItems' => $listItems ?? null,
            'clients' => Client::all(),
            'receiverId' => $this->receiverId,
            'manufactures' => $this->receiverId === 1 ? Manufacture::all() : [],
            'manufactureId' => $this->manufactureId,
            'products' => Product::all(),
            'materials' => $this->getManufactureMaterials(),
            'items' => $this->getOrderableItems(),
            'itemsData' => $itemsData,
            'total' => $this->countTotal($this->itemsCount, $itemsData)
        ];
    }

    private function getManufactureMaterials(): Collection|array
    {
        if (!$this->manufactureId) {
            return [];
        }

        return Manufacture::where('id', $this->manufactureId)->first()
            ->materials;
    }

    private function getOrderableItems(): Collection|null
    {
        if (!$this->manufactureId) {
            return null;
        }

        if ($this->receiverId === 1) {
            return Manufacture::where('id', $this->manufactureId)->first()->materials;

        }

        return Product::whereIn('id', $this->itemIds)->get();
    }

    private function getItemsData(): Collection|array
    {
        if (empty($this->itemIds)) {
            return [];
        }

        if ($this->receiverId === 1) {
            $manufacture = Manufacture::where('id', $this->manufactureId)->first();
            if ($manufacture) {
                return $manufacture->materials()->whereIn('id', $this->itemIds)->get();
            }
            return [];
        }

        return Product::whereIn('id', $this->itemIds)->get();
    }

    private function countTotal($countData, Collection|array $items): float
    {
        if (empty($items)) {
            return 0;
        }

        $total = 0;
        foreach ($countData as $id => $count) {
            $item = $items->where('id', $id)->first();
            if (!$item) {
                return 0;
            }
            $total += ($item->price * $count) ?: ($item->pivot->price * $count);
        }

        return $total;
    }

    public function save(): void
    {
        $this->validate();
        $itemsData = $this->getItemsData();

        $order = new Order();
        $order->due_date = $this->dueDate;
        $order->client_id = $this->receiverId;
        $order->status = Statuses::placed->name;
        $order->total = $this->countTotal($this->itemsCount, $itemsData);

        if ($this->receiverId === 1) {
            $order->manufacture_id = $this->manufactureId;
            $itemName = 'material';
        } else {
            $itemName = 'product';
        }

        $order->is_finalized = false;

        $syncData = [];
        foreach ($this->itemsCount as $id => $count) {
            $item = $itemsData->where('id', $id)->first();
            $price = ($item->price * $count) ?: ($item->pivot->price * $count);

            $syncData[] = [
                $itemName . '_id' => $id,
                'count' => $count,
                'price' => $price,
                'total' => $count * $price
            ];
        }

//        dd($syncData);
        $itemName .= 's';
        $order->save();
        $order->$itemName()->sync($syncData);

        $this->success('Order created with success.', redirectTo: '/orders/' . $order->id . '/view');

    }
}; ?>

<div>
    <x-header title="Create Product" separator/>
    <div class="grid gap-5 lg:grid-cols-2">
        <div class="">
            <x-form wire:submit="save">
                <x-datetime label="Due date" wire:model="dueDate" icon="o-calendar" type="datetime-local"/>

                <x-choices
                    label="Receiver"
                    wire:model.live="receiverId"
                    wire:change="resetItems()"
                    :options="$clients"
                    single
                />

                @if($receiverId === 1)
                    <x-choices-offline
                        label="Manufacture"
                        wire:model.live="manufactureId"
                        :options="$manufactures"
                        height="max-h-96"
                        hint="Search for product name"
                        searchable
                        single
                    >
                        @scope('item', $item)
                        <x-list-item :item="$item" sub-value="address" no-hover>
                        </x-list-item>
                        @endscope
                    </x-choices-offline>

                    @if($manufactureId)
                        <x-choices-offline
                            label="Materials"
                            wire:model.live="itemIds"
                            :options="$materials"
                            height="max-h-96"
                            hint="Search for product name"
                            searchable
                        >
                            @scope('item', $item)
                            <x-list-item :item="$item" no-hover>
                                <x-slot:avatar>
                                    <x-avatar :image="$item->url ?: '/empty-product.png'" class="!w-14 !rounded-lg"/>
                                </x-slot:avatar>
                                <x-slot:value>
                                    {{ $item->name }}
                                </x-slot:value>
                                <x-slot:actions>
                                    <x-badge :value="$item->pivot->price . '$'"/>
                                </x-slot:actions>
                            </x-list-item>
                            @endscope

                            @scope('selection', $item)
                            {{ $item->name }} ({{ $item->pivot->price }}$)
                            @endscope
                        </x-choices-offline>
                    @endif
                @endif

                @if($receiverId > 1)
                    <x-choices-offline
                        label="Products"
                        wire:model.live="itemIds"
                        :options="$products"
                        height="max-h-96"
                        hint="Search for product name"
                        searchable
                    >
                        @scope('item', $item)
                        <x-list-item :item="$item" no-hover>
                            <x-slot:avatar>
                                <x-avatar :image="$item->url ?: '/empty-product.png'" class="!w-14 !rounded-lg"/>
                            </x-slot:avatar>
                            <x-slot:value>
                                {{ $item->name }}
                            </x-slot:value>
                            <x-slot:actions>
                                <x-badge :value="$item->price . '$'"/>
                            </x-slot:actions>
                        </x-list-item>
                        @endscope

                        @scope('selection', $item)
                        {{ $item->name }} ({{ $item->price }}$)
                        @endscope
                    </x-choices-offline>
                @endif

                @if($itemsData)
                    @foreach($itemsData as $item)
                        <x-list-item :item="$item" no-hover>
                            <x-slot:avatar>
                                <x-avatar :image="$item->url ?: '/empty-product.png'" class="!w-14 !rounded-lg" />
                            </x-slot:avatar>
                            <x-slot:value>
                                {{ $item->name }}
                            </x-slot:value>
                            <x-slot:actions>
                                <x-input
                                    placeholder="count"
                                    wire:model.live="itemsCount.{{$item->id}}"
{{--                                    wire:change="updateLoad({{$item->id}}, $event.target.value, false)"--}}
                                    type="number"
                                />
                            </x-slot:actions>
                        </x-list-item>
                    @endforeach
                @endif

                <x-slot:actions>
                    <x-button label="Cancel" link="/users"/>
                    {{-- The important thing here is `type="submit"` --}}
                    {{-- The spinner property is nice! --}}
                    <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary"/>
                </x-slot:actions>
            </x-form>
        </div>
        <div class="">
            <div class="grid gap-2">
                <div class="flex gap-3 justify-between items-baseline px-10">
                    <div>Items</div>
                    <div class="border-b border-b-gray-400 border-dashed flex-1"></div>
                    <div class="font-black">({{count($itemsData)}})</div>
                </div>
                <div class="flex gap-3 justify-between items-baseline px-10">
                    <div>Total</div>
                    <div class="border-b border-b-gray-400 border-dashed flex-1"></div>
                    <div class="font-black">{{$total}} $</div>
                </div>
            </div>
        </div>
    </div>
</div>
