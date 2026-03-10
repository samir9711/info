<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

trait HasDynamicFiltering
{
    /** Master flag: allow dynamic filtering/sorting or not. */
    protected bool $enableDynamicFilters = true;

    /**
     * Whitelist of columns allowed for dynamic filtering/sorting.
     * Example items: 'price', 'brand_id', 'created_at', 'studentGroups.course_id'
     */
    protected array $dynamicFilterColumns = [];

    /**
     * Which columns are allowed for sorting (?sort=price,-created_at).
     * If empty → we'll reuse $dynamicFilterColumns for sorting.
     */
    protected array $sortableColumns = [];

    protected array $allowedOps = [
        'eq', 'neq',
        'like', 'nlike',
        'in', 'nin',
        'gt','gte','lt','lte',
        'between',
        'bool',
        'null',
    ];

    /**
     * Entry points used by BaseAuthModel::scopeWithFilters()
     */
    protected function applyDynamicFilters($query): void
    {
        $request = request();
        $params  = $request->query();

        // numeric min/max shortcuts
        $this->applyMinMaxShortcuts($query, $params);

        // pass #1: scalar params
        foreach ($params as $rawKey => $value) {
            if ($rawKey === 'search' || $rawKey === 'status' || $rawKey === 'sort') {
                continue;
            }
            if (is_array($value)) {
                continue; // arrays handled in pass #2
            }

            // CASE A: plain column on main table
            if (strpos($rawKey, '.') === false && strpos($rawKey, '_') === false) {
                [$column, $op] = $this->splitColumnAndOp($rawKey, $value);

                if (!$this->isColumnAllowed($column)) {
                    continue;
                }

                $this->applyColumnOp($query, $column, $op, $value);
                continue;
            }

            // CASE B: dotted relation.column
            if (strpos($rawKey, '.') !== false) {
                [$relation, $columnAndOp] = explode('.', $rawKey, 2);
                [$column, $op] = $this->splitColumnAndOp($columnAndOp, $value);

                if (!$this->isColumnAllowed("$relation.$column")) {
                    continue;
                }

                $this->applyRelationFilter($query, $relation, $column, $op, $value);
                continue;
            }

            // CASE C: PHP underscore mutation (relation_column)
            if (strpos($rawKey, '_') !== false) {
                $firstUnderscorePos = strpos($rawKey, '_');
                $relation     = substr($rawKey, 0, $firstUnderscorePos);
                $columnAndOp  = substr($rawKey, $firstUnderscorePos + 1);

                [$column, $op] = $this->splitColumnAndOp($columnAndOp, $value);

                if (!$this->isColumnAllowed("$relation.$column")) {
                    continue;
                }

                $this->applyRelationFilter($query, $relation, $column, $op, $value);
                continue;
            }
        }

        // pass #2: array-style nested relation filters (e.g. ?details[attribute_id]=7)
        foreach ($params as $relation => $subArray) {
            if (!is_array($subArray)) continue;

            foreach ($subArray as $columnAndOp => $value) {
                [$column, $op] = $this->splitColumnAndOp($columnAndOp, $value);

                if (!$this->isColumnAllowed("$relation.$column")) {
                    continue;
                }

                $this->applyRelationFilter($query, $relation, $column, $op, $value);
            }
        }
    }

    protected function applyDynamicSorting($query): void
    {
        $request = request();
        $sortRaw = $request->query('sort');

        if (!$sortRaw) return;

        $columns = array_map('trim', explode(',', (string)$sortRaw));

        foreach ($columns as $col) {
            $dir = 'asc';

            if (str_starts_with($col, '-')) {
                $dir = 'desc';
                $col = ltrim($col, '-');
            }

            if (!$this->isSortable($col)) continue;

            // no relation sorting by default (to avoid auto joins)
            if (strpos($col, '.') !== false) continue;

            $query->orderBy($col, $dir);
        }
    }

    protected function splitColumnAndOp(string $columnAndOp, $value): array
    {
        if (preg_match('/^(?<col>.+)_(?<op>[a-z]+)$/i', $columnAndOp, $m)) {
            $col = $m['col'];
            $op  = strtolower($m['op']);
            if (!in_array($op, $this->allowedOps, true)) {
                $op = 'eq';
            }
            return [$col, $op];
        }

        if (is_string($value) && strlen($value) && $value[0] === '!') {
            if (strpos($value, ',') !== false) {
                return [$columnAndOp, 'nin'];
            }
            return [$columnAndOp, 'neq'];
        }

        return [$columnAndOp, 'eq'];
    }

    protected function isColumnAllowed(string $col): bool
    {
        if (empty($this->dynamicFilterColumns)) return false;
        return in_array($col, $this->dynamicFilterColumns, true);
    }

    protected function isSortable(string $col): bool
    {
        $list = !empty($this->sortableColumns)
            ? $this->sortableColumns
            : $this->dynamicFilterColumns;

        return in_array($col, $list, true);
    }

    protected function applyRelationFilter($query, string $relation, string $column, string $op, $value): void
    {
        // حاول الحصول على اسم جدول النموذج المرتبط لتأهيل الأعمدة
        try {
            $relationObj = $this->{$relation}(); // Relation instance
            $relatedTable = $relationObj->getRelated()->getTable();
        } catch (\Throwable $e) {
            $relatedTable = $relation; // fallback نادر
        }

        $qualified = strpos($column, '.') !== false ? $column : "{$relatedTable}.{$column}";

        $query->whereHas($relation, function ($relQ) use ($qualified, $op, $value) {
            $this->applyColumnOp($relQ, $qualified, $op, $value);
        });
    }

    protected function applyColumnOp($query, string $column, string $op, $value): void
    {
        $csvToArray = function ($v) {
            if (is_array($v)) return $v;
            $v = (string)$v;
            if ($v === '') return [];
            if ($v[0] === '!') $v = substr($v, 1);
            return array_map('trim', explode(',', $v));
        };

        $valueStr = is_string($value) ? $value : '';
        $negated  = (is_string($value) && strlen($value) && $value[0] === '!');

        switch ($op) {
            case 'like':
                if ($valueStr === '' && !is_numeric($value)) break;
                if ($negated) {
                    $needle = substr($valueStr, 1);
                    $query->where($column, 'NOT LIKE', '%'.$needle.'%');
                } else {
                    $query->where($column, 'LIKE', '%'.$value.'%');
                }
                break;

            case 'nlike':
                if ($valueStr === '' && !is_numeric($value)) break;
                $query->where($column, 'NOT LIKE', '%'.$value.'%');
                break;

            case 'in': {
                $vals = $csvToArray($value);
                if (!empty($vals)) {
                    $query->whereIn($column, $vals);
                }
                break;
            }

            case 'nin': {
                $vals = $csvToArray($value);
                if (!empty($vals)) {
                    $query->whereNotIn($column, $vals);
                }
                break;
            }

            case 'gt':
                $query->where($column, '>', $value);
                break;

            case 'gte':
                $query->where($column, '>=', $value);
                break;

            case 'lt':
                $query->where($column, '<', $value);
                break;

            case 'lte':
                $query->where($column, '<=', $value);
                break;

            case 'between': {
                $vals = is_array($value) ? $value : $csvToArray($value);
                if (count($vals) >= 2) {
                    $query->whereBetween($column, [$vals[0], $vals[1]]);
                }
                break;
            }

            case 'bool': {
                $bool = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
                if ($bool !== null) {
                    $query->where($column, $bool);
                }
                break;
            }

            case 'null': {
                $flag = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
                if ($flag === true)  $query->whereNull($column);
                if ($flag === false) $query->whereNotNull($column);
                break;
            }

            case 'neq':
                if ($negated) $value = substr($valueStr, 1);
                $query->where($column, '!=', $value);
                break;

            case 'eq':
            default:
                if ($negated) {
                    $val = substr($valueStr, 1);
                    if (strpos($val, ',') !== false) {
                        $vals = array_map('trim', explode(',', $val));
                        $query->whereNotIn($column, $vals);
                    } else {
                        $query->where($column, '!=', $val);
                    }
                } else {
                    if ($value !== '' && $value !== null) {
                        $query->where($column, $value);
                    }
                }
                break;
        }
    }

    protected function applyMinMaxShortcuts($query, array $params): void
    {
        foreach ($this->dynamicFilterColumns as $col) {
            if (strpos($col, '.') !== false) continue;

            $minKey = "{$col}_min";
            $maxKey = "{$col}_max";

            $hasMin = array_key_exists($minKey, $params) && $params[$minKey] !== '';
            $hasMax = array_key_exists($maxKey, $params) && $params[$maxKey] !== '';

            if (!$hasMin && !$hasMax) continue;

            if ($hasMin && $hasMax) {
                $query->whereBetween($col, [$params[$minKey], $params[$maxKey]]);
            } elseif ($hasMin) {
                $query->where($col, '>=', $params[$minKey]);
            } else {
                $query->where($col, '<=', $params[$maxKey]);
            }
        }
    }
}
