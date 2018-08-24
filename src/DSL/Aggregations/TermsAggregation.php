<?php
/**
 * Created by PhpStorm.
 * User: vitaliy
 * Date: 24.08.18
 * Time: 16:40
 */

namespace Sleimanx2\Plastic\DSL\Aggregations;

use \ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation as Terms;

/**
 * Class TermsAggregation
 * Overriding the method from \ONGR\ElasticsearchDSL library
 *
 * @package Sleimanx2\Plastic\DSL\Aggregations
 */
class TermsAggregation extends Terms
{

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $array = $this->getArray();
        $result = [
            $this->getType() => is_array($array) ? $this->processArray($array) : $array,
        ];

        if ($this->supportsNesting()) {
            $nestedResult = $this->collectNestedAggregations();

            if (!empty($nestedResult)) {
                $result['aggregations'] = $nestedResult['aggregations'] ?? $nestedResult;
            }
        }

        return $result;
    }


}