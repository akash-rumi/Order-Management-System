<?php
namespace App\Repositories;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository
{
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function findWithItems(int $id): ?Order
    {
        return Order::with(['items'])->find($id);
    }

    public function find(int $id): ?Order
    {
        return Order::find($id);
    }

    public function paginateForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::where('user_id', $userId)->with('items')->orderBy('created_at','desc')->paginate($perPage);
    }

    public function paginateAll(int $perPage = 15): LengthAwarePaginator
    {
        return Order::with('items')->orderBy('created_at','desc')->paginate($perPage);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);
        return $order->fresh();
    }
}
