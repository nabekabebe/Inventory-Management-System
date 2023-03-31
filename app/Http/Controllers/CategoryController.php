<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ManagerOnly;
use App\Models\Category;
use App\Traits\AuthAccessControl;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    use HttpResponses;
    use AuthAccessControl;
    protected static string $NOT_FOUND = 'No category with this id';
    public function __construct()
    {
        $this->middleware(ManagerOnly::class)->except('index');
    }
    private function getCategory(int $id)
    {
        return Category::firstWhere([
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
            Category::where(['owner_token' => $this->userToken()])->get()
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
            'name' => 'required|string'
        ]);
        $category = Category::create([
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
    public function show(int $id)
    {
        $category = Category::firstWhere([
            'id' => $id,
            'owner_token' => $this->userToken()
        ]);
        if (!$category) {
            return $this->failure(CategoryController::$NOT_FOUND);
        }

        return $this->success($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $attributes = $request->validate([
            'name' => 'max:255'
        ]);
        $category = $this->getCategory($id);
        if (!$category) {
            return $this->failure(CategoryController::$NOT_FOUND);
        }
        $category->update($attributes);

        return $this->success($category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $category = Category::firstWhere([
            'id' => $id,
            'owner_token' => $this->userToken()
        ]);
        if (!$category) {
            return $this->failure(CategoryController::$NOT_FOUND);
        }
        $category->delete();
        return $this->success(null);
    }
}
