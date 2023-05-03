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
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Validator;

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
            'lowInStock'
        ]);
    }
    private function getInventory(string $id)
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
            Inventory::with('category:id,name')
                ->withSum('records as count', 'quantity')
                ->where('owner_token', $this->userToken())
                ->filter(request(['limit', 'search', 'sort']), [
                    'name',
                    'identifier'
                ])
                ->extract(request()->all(), [
                    'sell_price',
                    'purchase_price',
                    'created_at',
                    'created_from',
                    'created_until'
                ])
                ->ByDate(request(['year']))
                ->nestedExtract('category', 'name')
                ->get(),
            withCount: true
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
        $file_path = $request->file('image')->getRealPath();
        $imageUrl = '';
        try {
            $uploadedImage = Cloudinary::upload($file_path, [
                'folder' => 'my_folder',
                'upload_preset' => 'shrinkable'
            ]);
            $imageUrl = $uploadedImage->getSecurePath();
        } catch (Exception $e) {
            return $this->failure(
                'Unable to upload the image to cloud',
                Response::HTTP_BAD_GATEWAY
            );
        }
        $attributes = $request->validated();
        $variations = json_decode($request['variation']);
        $validator = Validator::make($variations, [
            'variation.*.quantity' => 'numeric',
            'variation.*.size' => 'string',
            'variation.*.color' => 'json'
        ]);
        if ($validator->invalid()) {
            return $this->failure(
                'Invalid inventory variation array provided',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $warehouseId = $attributes['warehouse_id'];
        unset($attributes['warehouse_id']);
        unset($attributes['variation']);
        $inventory = Inventory::create([
            ...$attributes,
            'identifier' => Str::slug(
                $attributes['name'] . ' ' . $attributes['barcode']
            ),
            'owner_token' => $this->userToken(),
            'image' => $imageUrl
        ]);
        $total_quantity = 0;
        foreach ($variations as $variation) {
            $total_quantity += $variation->quantity;
            Variation::create([
                'quantity' => $variation->quantity,
                'size' => $variation->size,
                'color' => $variation->color,
                'inventory_id' => $inventory->id
            ]);
        }
        WarehouseInfo::create([
            'quantity' => $total_quantity,
            'warehouse_id' => $warehouseId,
            'inventory_id' => $inventory->id
        ]);
        return $this->success($inventory);
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id)
    {
        $inventory = $this->getInventory($id)
            ->with('category:id,name', 'records.warehouse:id,name', 'variation')
            ->withSum('records as total_sold', 'sell_count')
            ->withSum('records as total_refunded', 'refund_count')
            ->withSum('records as count', 'quantity')
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
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateInventoryRequest $request, string $id)
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
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id)
    {
        $inventory = $this->getInventory($id)->first();
        if (!$inventory) {
            return $this->failure(InventoryController::$NOT_FOUND);
        }
        //        assert: This already deleted on cascade
        //        $variations = Variation::where(['inventory_id' => $inventory->id]);
        //        $variations->delete();
        $inventory->delete();
        assert(
            count(
                Variation::where(['inventory_id' => $inventory->id])->get()
            ) == 0
        );
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
                ResponseAlias::HTTP_BAD_REQUEST
            );
        }
        if ($attributes['quantity'] > $inventoryRecord->quantity) {
            return $this->failure(
                'Not enough inventories to sell from this warehouse!',
                ResponseAlias::HTTP_BAD_REQUEST
            );
        }
        $inventoryRecord->decrement('quantity', $attributes['quantity']);
        $inventoryRecord->increment('sell_count', $attributes['quantity']);

        $transaction = Transaction::create([
            ...$request->only(
                'quantity',
                'comment',
                'warehouse_id',
                'payment_method'
            ),
            'inventory_id' => $id,
            'transaction_type' => Transaction::SOLD,
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
        $inventoryRecord->increment('refund_count', $transaction->quantity);
        $inventoryRecord->decrement('sell_count', $transaction->quantity);
        $transaction->update(['transaction_type' => Transaction::REFUNDED]);

        return $this->success($inventoryRecord);
    }

    public function lowInStock(Request $request)
    {
        $request->merge([
            'select' => ['id', 'name', 'brand']
        ]);
        return $this->success(
            Inventory::where('owner_token', $this->userToken())
                ->filter(request(['limit', 'select']))
                ->withSum('records as remaining', 'quantity')
                ->orderBy('remaining', 'asc')
                ->get()
                ->where('remaining', '<=', $request->get('trigger') ?? 400),
            withCount: true
        );
    }

    /**
     * Display a listing of the warehouses for inventory with given  id.
     * @param string $id
     * @return JsonResponse
     */
    public function getWarehouses(string $id)
    {
        return $this->success(
            $this->getInventory($id)
                ->select('id')
                ->with('warehouse')
                ->get(),
            withCount: true
        );
    }
}
