<?php

declare(strict_types=1);

namespace EngineWorks\PivotTests\Utils;

use EngineWorks\Pivot\QueryResult;
use EngineWorks\Pivot\Result;

class QueryResultAsArray
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(QueryResult $queryResult): array
    {
        $array = ['rows' => $this->resultAsArray($queryResult->getTotals(), 'rows')];
        if ($queryResult->hasDetails()) {
            $array['columns'] = $this->resultAsArray($queryResult->getDetails(), 'columns');
        }
        return $array;
    }

    /**
     * @return array<string, mixed>
     */
    private function resultAsArray(Result $result, string $childrenName): array
    {
        $array = [
            'group' => $result->caption,
            'values' => array_values($result->values ?? []),
        ];
        if ($result->hasChildren()) {
            $children = [];
            foreach ($result->getChildren() as $child) {
                $children[] = $this->resultAsArray($child, $childrenName);
            }
            $array[$childrenName] = $children;
        }
        return $array;
    }
}
