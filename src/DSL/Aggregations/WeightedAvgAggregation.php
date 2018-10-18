<?php
/**
 * Created by PhpStorm.
 * User: vitaliy
 * Date: 24.08.18
 * Time: 16:40
 */

namespace Sleimanx2\Plastic\DSL\Aggregations;

use ONGR\ElasticsearchDSL\Aggregation\AbstractAggregation;

/**
 * Class WeightedAvgAggregation
 * Overriding the method from \ONGR\ElasticsearchDSL library
 *
 * @package Sleimanx2\Plastic\DSL\Aggregations
 */
class WeightedAvgAggregation extends AbstractAggregation
{

    private $field;
    private $weight;

    public function __construct($name, $field, $weight)
    {
        parent::__construct($name);
        $this->field = $field;
        $this->weight = $weight;
    }


    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $data = array_filter(
            [
                "value" => [
                    "field" => $this->field
                ],
                "weight" =>[
                    "field" => $this->weight
                ],
            ]
        );

        return $data;
    }


    public function getType(){

        return "weighted_avg";

    }


    public function supportsNesting()
    {
        return false;
    }


}