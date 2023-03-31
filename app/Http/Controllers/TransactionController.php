<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ManagerOnly;
use App\Http\Requests\GetTransactionReport;
use App\Models\Transaction;
use App\Traits\AuthAccessControl;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use HttpResponses;
    use AuthAccessControl;
    protected static string $NOT_FOUND = 'No transaction with this id';
    public function __construct()
    {
        $this->middleware(ManagerOnly::class)->except('index');
    }
    private function getTransaction(int $id): Transaction
    {
        return Transaction::where([
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
        $transactions = Transaction::with(
            'inventory:id,name',
            'warehouse:id,name',
            'committedBy:id,full_name'
        )
            ->where('owner_token', $this->userToken())
            ->filter(request(['limit', 'search', 'sort']))
            ->get();

        if (!$this->isManager()) {
            $transactions = $transactions->where('user_id', $this->userId());
        }

        return $this->success($transactions);
    }
    public function getTransactions()
    {
        return Transaction::with(
            'inventory:id,name',
            'warehouse:id,name',
            'committedBy:id,full_name'
        )
            ->where('owner_token', $this->userToken())
            ->filter(request(['limit', 'search', 'sort']))
            ->get();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $transaction = $this->getTransaction($id)->first();
        if (!$transaction) {
            return $this->failure(TransactionController::$NOT_FOUND);
        }

        return $this->success($transaction);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $attributes = $request->validate([
            'comment' => 'max:255',
            'payment_method' => 'in_array:bank,cash'
        ]);
        $transaction = $this->getTransaction($id)->first();
        if (!$transaction) {
            return $this->failure(TransactionController::$NOT_FOUND);
        }
        $transaction->update($attributes);

        return $this->success($transaction);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id)
    {
        $transaction = $this->getTransaction($id)->first();
        if (!$transaction) {
            return $this->failure(TransactionController::$NOT_FOUND);
        }
        $transaction->delete();
        return $this->success(null);
    }
    public function report(GetTransactionReport $request)
    {
        $attributes = $request->validated();
        //        TODO: correct it to only fetch user related posts
        $transaction = Transaction::with(
            'inventory:id,name,sell_price',
            'warehouse:id,name',
            'committedBy:id,full_name'
        )->where('transaction_type', '=', 'sold');
        switch ($attributes['report_type']) {
            case 'weekly':
                $transaction = $this->generate_weekly_report(
                    $attributes,
                    $transaction
                );
                break;
            case 'monthly':
                $transaction = $this->generate_monthly_report(
                    $attributes,
                    $transaction
                );
                break;
            case 'annually':
                $transaction = $this->generate_annual_report(
                    $attributes,
                    $transaction
                );
                break;
        }
        return $this->success($transaction);
    }

    private function generate_weekly_report($attributes, $transaction)
    {
        $targetDate = Carbon::create(
            year: $attributes['year'] ?? now()->year,
            month: $attributes['month'] ?? now()->month,
            day: 7 * $attributes['week']
        );
        return $transaction
            ->whereDate(
                'created_at',
                '>=',
                $targetDate->subDays(7)->toDateString()
            )
            ->whereDate(
                'created_at',
                '<=',
                $targetDate->addDays(7)->toDateString()
            )
            ->get()

            ->groupBy(function ($val) {
                return Carbon::parse($val->created_at)->format('D');
            })
            ->sortBy(function ($group, $key) {
                return $group->first()->created_at;
            });
    }

    private function generate_monthly_report($attributes, $transaction)
    {
        return $transaction
            ->whereYear('created_at', '=', $attributes['year'] ?? now()->year)
            ->whereMonth('created_at', '=', $attributes['month'])
            ->get()
            ->groupBy(function ($val) {
                return Carbon::parse($val->created_at)->format('d');
            })

            ->sortBy(function ($group, $key) {
                return $group->first()->created_at;
            });
    }

    private function generate_annual_report($attributes, $transaction)
    {
        return $transaction
            ->whereYear('created_at', '=', $attributes['year'])
            ->get()
            ->groupBy(function ($val) {
                return Carbon::parse($val->created_at)->format('M');
            })

            ->sortBy(function ($group, $key) {
                return $group->first()->created_at;
            });
    }
}
