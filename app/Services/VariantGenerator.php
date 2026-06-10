<?php

namespace App\Services;

use App\Models\AttributeValue;

class VariantGenerator
{
    /**
     * Build the Cartesian product of selected attribute-value sets.
     *
     * @param  array<int, array<int, AttributeValue>>  $attributeValueGroups
     *         e.g. [ [Red, Blue], [128GB, 256GB] ]
     * @return array<int, array<int, AttributeValue>>
     *         e.g. [ [Red,128GB], [Red,256GB], [Blue,128GB], [Blue,256GB] ]
     */
    public function cartesian(array $attributeValueGroups): array
    {
        return array_reduce(
            $attributeValueGroups,
            function (array $carry, array $group): array {
                $result = [];
                foreach ($carry as $combo) {
                    foreach ($group as $value) {
                        $result[] = [...$combo, $value];
                    }
                }

                return $result;
            },
            [[]] // seed: one empty combination
        );
    }
}
