<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ManagerOnly;
use App\Http\Requests\StoreTransferRequest;
use App\Http\Requests\UpdateTransferRequest;
use App\Models\Transfer;
use App\Models\WarehouseInfo;
use App\Traits\AuthAccessControl;
use App\Traits\HttpResponses;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TransferController extends Controller
{
    use HttpResponses;
    use AuthAccessControl;
    protected static string $NOT_FOUND = 'No transfer with this id';
    public function __construct()
    {
        $this->middleware(ManagerOnly::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return $this->success(
            Transfer::withCount('inventories as total_inventories')
                ->with('from:id,name', 'to:id,name')
                ->where('owner_token', $this->userToken())
                ->filter(request(['limit', 'search', 'sort']))
                ->extract(request()->all(), [
                    'created_at',
                    'created_from',
                    'created_until'
                ])
                ->get(),
            withCount: true
        );
    }
    private function getTransfer(string $id)
    {
        return Transfer::where([
            'id' => $id,
            'owner_token' => $this->userToken()
        ]);
    }
    private function getWarehouseInfo(int $inventoryId, int $warehouseId)
    {
        return WarehouseInfo::firstWhere([
            'inventory_id' => $inventoryId,
            'warehouse_id' => $warehouseId
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTransferRequest $request
     * @return JsonResponse
     */
    public function store(StoreTransferRequest $request)
    {
        $attributes = $request->validated();
        $newTransfers = [];
        DB::beginTransaction();
        foreach ($attributes['inventories'] as $transfer) {
            $sourceWarehouse = $this->getWarehouseInfo(
                $transfer['id'],
                $attributes['source_id']
            );
            if (!$sourceWarehouse) {
                DB::rollBack();
                return $this->failure(
                    'Warehouse does not contain this inventory!',
                    Response::HTTP_BAD_REQUEST
                );
            }
            if ($sourceWarehouse->quantity < $transfer['quantity']) {
                DB::rollBack();
                return $this->failure(
                    'Not enough inventories to transfer!',
                    Response::HTTP_BAD_REQUEST
                );
            }
            $destinationWarehouse = $this->getWarehouseInfo(
                $transfer['id'],
                $attributes['destination_id']
            );
            if (!$destinationWarehouse) {
                $destinationWarehouse = WarehouseInfo::create([
                    'quantity' => 0,
                    'warehouse_id' => $attributes['destination_id'],
                    'inventory_id' => $transfer['id']
                ]);
            }
            $sourceWarehouse->decrement('quantity', $transfer['quantity']);
            $destinationWarehouse->increment('quantity', $transfer['quantity']);
            $newTransfers[] = Transfer::create([
                'source_id' => $attributes['source_id'],
                'destination_id' => $attributes['destination_id'],
                'inventory_id' => $transfer['id'],
                'quantity' => $transfer['quantity'],
                'owner_token' => $this->userToken()
            ]);
        }
        DB::commit();
        return $this->success($newTransfers, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $transfer = $this->getTransfer($id)
            ->with('inventories:id,name', 'from:id,name', 'to:id,name')
            ->first();
        if (!$transfer) {
            return $this->failure(TransferController::$NOT_FOUND);
        }

        return $this->success($transfer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTransferRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateTransferRequest $request, string $id)
    {
        $attributes = $request->validated();
        $amount = $attributes['quantity'];
        $transfer = $this->getTransfer($id)->first();
        if (!$transfer) {
            return $this->failure(TransferController::$NOT_FOUND);
        }
        $transfer->update($request->only('quantity'));
        $warehouseSource = $this->getWarehouseInfo(
            $transfer['id'],
            $attributes['source_id']
        );
        $warehouseDestination = $this->getWarehouseInfo(
            $transfer['id'],
            $attributes['destination_id']
        );
        if ($amount > $warehouseSource->quantity + $transfer->quanitity) {
            return $this->failure(
                'Not enough inventories to transfer!',
                Response::HTTP_BAD_REQUEST
            );
        }
        //rolling back the transfer then applying the new transfer amount
        $warehouseSource->increment('quantity', $transfer->quanitity - $amount);
        $warehouseDestination->decrement(
            'quantity',
            $transfer->quanitity + $amount
        );
        $transfer->update(['quantity' => $amount]);
        return $this->success($transfer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $transfer = $this->getTransfer($id)->first();
        if (!$transfer) {
            return $this->failure(TransferController::$NOT_FOUND);
        }
        $transfer->delete();
        return $this->success(null);
    }
}
