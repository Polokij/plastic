<?php
/**
 * Created by PhpStorm.
 * User: vitaliy
 * Date: 24.08.18
 * Time: 16:40
 */

namespace Sleimanx2\Plastic\DSL\Aggregations;

use \ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation as Terms;
use ONGR\ElasticsearchDSL\BuilderBag;
use ONGR\ElasticsearchDSL\BuilderInterface;

/**
 * Class TermsAggregation
 * Overriding the method from \ONGR\ElasticsearchDSL library
 *
 * @package Sleimanx2\Plastic\DSL\Aggregations
 */
class TermsAggregation extends Terms
{

    /**
     * @var string
     */
//    private $field;

    /**
     * @var BuilderBag
     */
//    private $aggregations;

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

    /**
     * Adds a sub-aggregation.
     *
     * @param \ONGR\ElasticsearchDSL\BuilderInterface|\Sleimanx2\Plastic\DSL\Aggregations\AbstractAggregation $abstractAggregation
     *
     * @return $this
     */
//    public function addSubAggregation(BuilderInterface $abstractAggregation)
//    {
//        if (!$this->aggregations) {
//            $this->aggregations = $this->createBuilderBag();
//        }
//
//        $this->aggregations->add($abstractAggregation);
//
//        return $this;
//    }

    /**
     * Creates BuilderBag new instance.
     *
     * @return BuilderBag
     */
    private function createBuilderBag()
    {
        return new BuilderBag();
    }


}