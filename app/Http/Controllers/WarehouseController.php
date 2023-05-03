<?php

namespace App\Http\Controllers;
use App\Http\Middleware\ManagerOnly;
use App\Models\Warehouse;
use App\Traits\AuthAccessControl;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WarehouseController extends Controller
{
    use HttpResponses;
    use AuthAccessControl;
    protected static string $NOT_FOUND = 'No warehouse with this id';
    public function __construct()
    {
        $this->middleware(ManagerOnly::class)->except(['index', 'show']);
    }
    private function getWarehouse(string $id)
    {
        return Warehouse::where([
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
            Warehouse::where('owner_token', $this->userToken())
                ->withSum('records as inventory_count', 'quantity')
                ->withSum('records as total_sold', 'sell_count')
                ->extract(request()->all(), [
                    'created_at',
                    'created_from',
                    'created_until'
                ])
                ->filter(request(['limit', 'search', 'sort']), ['name'])
                ->get(),
            withCount: true
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $attributes = $request->validate([
            'name' => 'required|string',
            'address' => 'required|string'
        ]);
        $warehouse = Warehouse::create([
            ...$attributes,
            'owner_token' => $this->userToken()
        ]);
        return $this->success($warehouse, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function show(string $id)
    {
        $warehouse = $this->getWarehouse($id)
            ->with('records.inventory:id,name')
            ->withSum('records as total_sold', 'sell_count')
            ->withSum('records as total_refunded', 'refund_count')
            ->withSum('records as inventory_count', 'quantity')
            ->first();
        if (!$warehouse) {
            return $this->failure(WarehouseController::$NOT_FOUND);
        }

        return $this->success($warehouse);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  string  $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $attributes = $request->validate([
            'name' => 'max:255',
            'address' => 'string'
        ]);
        $warehouse = $this->getWarehouse($id)->first();
        if (!$warehouse) {
            return $this->failure(WarehouseController::$NOT_FOUND);
        }
        $warehouse->update($attributes);

        return $this->success($warehouse);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $warehouse = $this->getWarehouse($id)->first();
        if (!$warehouse) {
            return $this->failure(WarehouseController::$NOT_FOUND);
        }
        $warehouse->delete();
        return $this->success(null);
    }
    public function lowInWarehouse(Request $request)
    {
        $request->merge([
            'select' => ['id', 'name']
        ]);
        return $this->success(
            Warehouse::where('owner_token', $this->userToken())
                ->filter(request(['limit', 'select']))
                ->withSum('records as remaining', 'quantity')
                ->orderBy('remaining', 'asc')
                ->get(),
            withCount: true
        );
    }

    /**
     * Display a listing of the warehouses for inventory with given  id.
     * @param string $id
     * @return JsonResponse
     */
    public function getInventories(string $id)
    {
        return $this->success(
            $this->getWarehouse($id)
                ->select('id')
                ->with('inventory')
                ->get(),
            withCount: true
        );
    }
}
