<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ManagerOnly;
use App\Http\Requests\SellInventoryRequest;
use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;
use App\Models\Inventory;
use App\Models\Transaction;
use App\Models\Variation;
use App\Models\WarehouseInfo;
use App\Traits\AuthAccessControl;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Str;

class InventoryController extends Controller
{
    use HttpResponses;
    use AuthAccessControl;
    protected static string $NOT_FOUND = 'No inventory with this id';
    public function __construct()
    {
        $this->middleware(ManagerOnly::class)->except([
            'index',
            'show',
            'lowOnStock'
        ]);
    }
    private function getInventory(int $id)
    {
        return Inventory::where([
            'id' => $id,
            'owner_token' => $this->userToken()
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return $this->success(
            Inventory::with('category:id,name', 'stores')
                ->withSum('stores as total_quantity', 'quantity')
                //                ->where('owner_token', $this->userToken())
                //                ->whereDate('created_at', '<=', Carbon::now()->subMonth())
                ->filter(request(['limit', 'search', 'sort']), [
                    'name',
                    'description'
                ])
                ->extract(request(['sell_price', 'purchase_price', 'quantity']))
                ->ByDate(request(['year']))
                ->nestedExtract('category', 'name')
                ->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInventoryRequest $request
     * @return JsonResponse
     */
    public function store(StoreInventoryRequest $request)
    {
        $attributes = $request->validated();
        $variations = $attributes['variation'];
        $warehouseId = $attributes['warehouse_id'];
        unset($attributes['warehouse_id']);
        unset($attributes['variation']);
        $inventory = Inventory::create([
            ...$attributes,
            'identifier' => Str::slug($attributes['identifier']),
            'created_at' => now(),
            'owner_token' => $this->userToken()
        ]);
        foreach ($variations as $variation) {
            Variation::create([
                ...$variation,
                'inventory_id' => $inventory->id
            ]);
        }
        WarehouseInfo::create([
            'quantity' => $attributes['quantity'],
            'warehouse_id' => $warehouseId,
            'inventory_id' => $inventory->id
        ]);
        return $this->success($inventory);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id)
    {
        $inventory = $this->getInventory($id)
            ->with('category:id,name', 'stores')
            ->first();
        if (!$inventory) {
            return $this->failure(InventoryController::$NOT_FOUND);
        }

        return $this->success($inventory);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateInventoryRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateInventoryRequest $request, int $id)
    {
        $attributes = $request->validated();
        $inventory = $this->getInventory($id)->first();
        if (!$inventory) {
            return $this->failure(InventoryController::$NOT_FOUND);
        }
        $inventory->update($attributes);

        return $this->success($inventory);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id)
    {
        $inventory = $this->getInventory($id)->first();
        if (!$inventory) {
            return $this->failure(InventoryController::$NOT_FOUND);
        }
        $inventory->delete();
        return $this->success(null);
    }
    public function sell(SellInventoryRequest $request, $id)
    {
        $attributes = $request->validated();
        $inventoryRecord = WarehouseInfo::firstWhere([
            'inventory_id' => $id,
            'warehouse_id' => $attributes['warehouse_id']
        ]);
        if (!$inventoryRecord) {
            return $this->failure(
                'No inventory found in this warehouse',
                Response::HTTP_BAD_REQUEST
            );
        }
        if ($attributes['quantity'] > $inventoryRecord->quantity) {
            return $this->failure(
                'Not enough inventories to sell from this warehouse!',
                Response::HTTP_BAD_REQUEST
            );
        }
        $inventoryRecord->decrement('quantity', $attributes['quantity']);
        $transaction = Transaction::create([
            ...$request->only(
                'quantity',
                'comment',
                'warehouse_id',
                'payment_method'
            ),
            'inventory_id' => $id,
            'transaction_type' => Transaction::SOLD,
            'created_at' => now(),
            'owner_token' => $this->userToken(),
            'user_id' => Auth()
                ->user()
                ->getAuthIdentifier()
        ]);
        return $this->success($transaction);
    }

    public function refund($id, $transactionId)
    {
        $transaction = Transaction::firstWhere([
            'id' => $transactionId,
            'owner_token' => $this->userToken(),
            'inventory_id' => $id,
            'transaction_type' => Transaction::SOLD
        ]);
        if (!$transaction) {
            return $this->failure(
                'Transaction with \'SOLD\' status does not exit for this item!',
                Response::HTTP_NOT_FOUND
            );
        }
        $inventoryRecord = WarehouseInfo::firstWhere([
            'inventory_id' => $transaction->inventory_id,
            'warehouse_id' => $transaction->warehouse_id
        ]);

        $inventoryRecord->increment('quantity', $transaction->quantity);
        $transaction->update(['transaction_type' => Transaction::REFUNDED]);

        return $this->success($inventoryRecord);
    }

    public function lowOnStock(Request $request)
    {
        $request->merge([
            'sort' => 'total_quantity ASC',
            'select' => ['id', 'name', 'brand']
        ]);
        return $this->success(
            Inventory::where('owner_token', $this->userToken())
                ->filter(request(['limit', 'sort', 'select']))
                ->withSum('stores as total_quantity', 'quantity')
                ->get()
                ->where('total_quantity', '<', 100)
        );
    }
}
