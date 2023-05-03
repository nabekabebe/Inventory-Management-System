<?php
namespace App\Traits;

use function PHPUnit\Framework\isEmpty;
trait ApiFilter
{
    public function scopeFilter(
        $query,
        array $filters,
        array $searchFields = []
    ) {
        $this->page($query, $filters['limit'] ?? 100);
        $query->when($filters['search'] ?? null, function ($query, $term) use (
            $searchFields
        ) {
            $count = 0;
            foreach ($searchFields as $column) {
                if ($count == 0) {
                    $query->where($column, 'like', $term . '%');
                } else {
                    $query->orWhere($column, 'like', $term . '%');
                }
                $count++;
            }
        });
        $query->when(
            $filters['select'] ?? [],
            fn($query, $fields) => $query->select($fields)
        );
        $query->when(
            $filters['sort'] ?? 'created_at DESC',
            fn($query, $order) => $query->OrderByRaw($order)
        );
    }

    private function page($query, int $amount)
    {
        $pagination = request()->only(['page', 'limit']);
        $page = $pagination['page'] ?? 1;
        $limit = $pagination['limit'] ?? $amount;
        $skipValue = ((int) $page - 1) * (int) $limit;
        $query->skip($skipValue)->take($limit);
    }

    public function scopeNestedExtract($query, string $name, string $attr)
    {
        $query->when(
            request([$name])[$name] ?? false,
            fn($query, $value) => $query->whereHas(
                $name,
                fn($query) => $query->where($attr, $value)
            )
        );
    }
    public function scopeExtract($query, array $filters, array $allowedFields)
    {
        foreach ($filters as $filter => $value) {
            if (
                !in_array($filter, $allowedFields) ||
                !str_contains($value, ',')
            ) {
                continue;
            }
            if ($filter == 'created_from' || $filter == 'created_until') {
                $filter = 'created_at';
            }
            [$op, $arg] = explode(',', $value);
            $query->where($filter, $op, $arg);
        }
    }
    public function scopeByDate($query, array $filters)
    {
        if (!isEmpty($filters)) {
            foreach ($filters as $filter => $value) {
                [$op, $arg] = explode(',', $value);
                $query->whereDate('created_at', $op, $arg);
            }
        }
    }
}
