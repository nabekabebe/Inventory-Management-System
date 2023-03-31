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
    private function getWarehouse(int $id)
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
            Warehouse::withSum('inventories as total_inventories', 'quantity')
                ->where('owner_token', $this->userToken())
                ->filter(request(['limit', 'search', 'sort']), ['name'])
                ->get()
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
        $category = Warehouse::create([
            ...$attributes,
            'owner_token' => $this->userToken()
        ]);
        return $this->success($category, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $warehouse = $this->getWarehouse($id)
            ->with('inventories')
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
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id)
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
            'sort' => 'total_inventories ASC',
            'select' => ['id', 'name']
        ]);
        return $this->success(
            Warehouse::where('owner_token', $this->userToken())
                ->filter(request(['limit', 'sort', 'select']))
                ->withSum('inventories as total_inventories', 'quantity')
                ->withMax('inventories as min_inventory', 'quantity')
                ->get()
                ->where('total_inventories', '<', 100)
        );
    }
}
