<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\CreateOrderData;
use App\DTO\Customer;
use App\DTO\OrderLine;
use Illuminate\Foundation\Http\FormRequest;

final class StoreOrderRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'delivery_address' => ['required', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.sku' => ['required', 'string', 'max:64', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }

    public function toDto(Customer $customer): CreateOrderData
    {
        /** @var array{delivery_address: string, items: array<int, array{sku: string, quantity: int|string}>} $data */
        $data = $this->validated();

        return new CreateOrderData(
            $customer,
            $data['delivery_address'],
            array_values(array_map(
                static fn (array $item): OrderLine => new OrderLine($item['sku'], (int) $item['quantity']),
                $data['items'],
            )),
        );
    }
}
