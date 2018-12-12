<?php
/**
 * Created by PhpStorm.
 * User: vitaliy
 * Date: 24.08.18
 * Time: 16:40
 */

namespace Sleimanx2\Plastic\DSL\Aggregations;

use ONGR\ElasticsearchDSL\Aggregation\AbstractAggregation;
use \ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation as Terms;
use ONGR\ElasticsearchDSL\Aggregation\Metric\SumAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Pipeline\BucketScriptAggregation;

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
     * {@inheritdoc}
     */
    public function getArray()
    {
        $data = [
            'field'  => $this->getField(),
        ];

        if($this->getScript()){
            $data['script'] = $this->getScript();
        }

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

    public function flattenResult($result){
        $termResults = $result[$this->getName()] ?? null;

        if( ! $termResults || ! isset($termResults['buckets'])){
            return [];
        }
        $aggregations = collect($this->getAggregations())
            ->filter(function($agg){
                // skip the bucket sort aggregation because it doesn't have the result value in the buckets
                return ! $agg instanceof BucketSortAggregation;
            });

        $resultBuckets = collect([]);
        // Mapping the bucket to have a flat result similar
        collect($termResults['buckets'])
            // iterate the buckets
            ->each(function($bucket) use ($aggregations, &$resultBuckets){

                $lastLevelAggregation = true;
                // iterating the bucket fields
                $bucketResult = $aggregations->mapWithKeys(function(AbstractAggregation $aggr) use ($bucket, &$resultBuckets, &$lastLevelAggregation){
                    $fieldName = $aggr->getName();
                    if($aggr instanceof SumAggregation || $aggr instanceof BucketScriptAggregation){
                        return [$fieldName => $bucket[$fieldName]['value'] ?? 0];
                    }
                    $aggrClass = get_class($aggr);
                    $flattenResults = $aggr->flattenResult($bucket);

                    if($aggr instanceof DateHistogramAggregation || $aggr instanceof TermsAggregation){
                        $lastLevelAggregation = false;
                        $flattenResults->transform(function($childBacket) use ($bucket, &$resultBuckets){
                            $childBacket[$this->getField()] = $bucket['key'];

                            $resultBuckets->push($childBacket);

                            return $childBacket;
                        });
                        return $flattenResults->toArray();
                    }
                    return $flattenResults;
                });
                $bucketResult[$this->getName()] = $bucket['key'];
                $lastLevelAggregation && $resultBuckets->push($bucketResult);
            });

        return $resultBuckets;

    }
}