<?php
/**
 * Created by PhpStorm.
 * User: vitaliy
 * Date: 11/30/18
 * Time: 8:13 AM
 */

namespace Sleimanx2\Plastic\DSL\Aggregations;


use ONGR\ElasticsearchDSL\Aggregation\AbstractAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\DateHistogramAggregation as DateHistogram;
use ONGR\ElasticsearchDSL\Aggregation\Metric\SumAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Pipeline\BucketScriptAggregation;


/**
 * Class DateHistogramAggregation
 *
 * @package Sleimanx2\Plastic\DSL\Aggregations
 */
class DateHistogramAggregation extends DateHistogram
{

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

        // Mapping the bucket to have a flat result similar
        $buckets = collect($termResults['buckets'])
            // iterate the buckets
            ->map(function($bucket) use ($aggregations){

                // iterating the bucket fields
                $bucketResult = $aggregations->mapWithKeys(function(AbstractAggregation $aggr) use ($bucket){
                    $fieldName = $aggr->getName();
                    if($aggr instanceof SumAggregation || $aggr instanceof BucketScriptAggregation){
                        return [$fieldName => $bucket[$fieldName]['value'] ?? 0];
                    }
                    return $aggr->flattenResult($bucket);
                });
                $bucketResult[$this->getName()] = $bucket['key'];
                return $bucketResult;
            });

        return $buckets;

    }

}