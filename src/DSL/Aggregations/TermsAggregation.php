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

    private $sorts;

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
     * Creates BuilderBag new instance.
     *
     * @return BuilderBag
     */
    private function createBuilderBag()
    {
        return new BuilderBag();
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $data = array_filter(
            [
                'field' => $this->getField(),
                'script' => $this->getScript(),
//                'order' => $this->getSorts(),
            ]
        );

        return $data;
    }


    /**
     * @param array $sort
     */
    public function setSorts(array $sorts){

        $this->sorts = $sorts;

    }

    /**
     * @return mixed
     */
    public function getSorts(){

      return $this->sorts;

    }





}