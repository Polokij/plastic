<?php

namespace Sleimanx2\Plastic\DSL;

use ONGR\ElasticsearchDSL\Aggregation\AbstractAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\DateRangeAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\GeoDistanceAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\GeoHashGridAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\HistogramAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\Ipv4RangeAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\MissingAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\RangeAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\AvgAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\CardinalityAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\GeoBoundsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\MaxAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\MinAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\PercentileRanksAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\PercentilesAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\StatsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\SumAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\ValueCountAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Pipeline\BucketScriptAggregation;
use ONGR\ElasticsearchDSL\BuilderInterface;
use ONGR\ElasticsearchDSL\Search as Query;

use Sleimanx2\Plastic\DSL\Aggregations\BucketSortAggregation;
use Sleimanx2\Plastic\DSL\Aggregations\DateHistogramAggregation;
use Sleimanx2\Plastic\DSL\Aggregations\NestedAggregation;
use Sleimanx2\Plastic\DSL\Aggregations\TermsAggregation;
use Sleimanx2\Plastic\DSL\Aggregations\WeightedAvgAggregation;

class AggregationBuilder extends AbstractAggregation
{

    /**
     * An instance of DSL query.
     *
     * @var Query
     */
    public $query;

    /**
     * Is a current aggregation top level.
     * In case topLevel = false - returning the value of 'aggregations' field
     *
     * @var bool
     */
    public $topLevel = true;

    /**
     * Builder constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query = null)
    {
        $this->query = $query;
    }

    /**
     * Add an average aggregate.
     *
     * @param             $alias
     * @param string|null $field
     * @param string|null $script
     */
    public function average($alias, $field = null, $script = null)
    {
        $aggregation = new AvgAggregation($alias, $field, $script);

        $this->append($aggregation);
    }

    /**
     * Add an cardinality aggregate.
     *
     * @param             $alias
     * @param string|null $field
     * @param string|null $script
     * @param int         $precision
     * @param bool        $rehash
     */
    public function cardinality($alias, $field = null, $script = null, $precision = null, $rehash = null)
    {
        $aggregation = new CardinalityAggregation($alias);

        $aggregation->setField($field);

        $aggregation->setScript($script);

        $aggregation->setPrecisionThreshold($precision);

        $aggregation->setRehash($rehash);

        $this->append($aggregation);
    }

    /**
     * Add a date range aggregate.
     *
     * @param       $alias
     * @param       $field
     * @param       $format
     * @param array $ranges
     *
     * @internal param null $from
     * @internal param null $to
     */
    public function dateRange($alias, $field, $format, array $ranges)
    {
        $aggregation = new DateRangeAggregation($alias, $field, $format, $ranges);

        $this->append($aggregation);
    }

    /**
     * Add a geo bounds aggregate.
     *
     * @param string      $alias
     * @param null|string $field
     * @param bool        $wrap_longitude
     */
    public function geoBounds($alias, $field, $wrap_longitude = true)
    {
        $aggregation = new GeoBoundsAggregation($alias, $field, $wrap_longitude);

        $this->append($aggregation);
    }

    /**
     * Add a geo bounds aggregate.
     *
     * @param string      $alias
     * @param null|string $field
     * @param string      $origin
     * @param array       $ranges
     */
    public function geoDistance($alias, $field, $origin, array $ranges)
    {
        $aggregation = new GeoDistanceAggregation($alias, $field, $origin, $ranges);

        $this->append($aggregation);
    }

    /**
     * Add a geo hash grid aggregate.
     *
     * @param string      $alias
     * @param null|string $field
     * @param float       $precision
     * @param null        $size
     * @param null        $shardSize
     */
    public function geoHashGrid($alias, $field, $precision, $size = null, $shardSize = null)
    {
        $aggregation = new GeoHashGridAggregation($alias, $field, $precision, $size, $shardSize);

        $this->append($aggregation);
    }

    /**
     * Add a histogram aggregate.
     *
     * @param        $alias
     * @param string $field
     * @param int    $interval
     * @param int    $minDocCount
     * @param string $orderMode
     * @param string $orderDirection
     * @param int    $extendedBoundsMin
     * @param int    $extendedBoundsMax
     * @param bool   $keyed
     */
    public function histogram(
        $alias,
        $field,
        $interval,
        $minDocCount = null,
        $orderMode = null,
        $orderDirection = 'asc',
        $extendedBoundsMin = null,
        $extendedBoundsMax = null,
        $keyed = null
    ) {
        $aggregation = new HistogramAggregation($alias, $field, $interval, $minDocCount, $orderMode, $orderDirection,
            $extendedBoundsMin, $extendedBoundsMax, $keyed);

        $this->append($aggregation);

        return $aggregation;
    }

    public function dateHistogram($alias, $field, $interval, \Closure $buildSubAggs  = null){

        $aggregation = new DateHistogramAggregation($alias, $field, $interval);

        if($buildSubAggs){
            $emptyQuery = $query = new \ONGR\ElasticsearchDSL\Search();

            $subAggregation = new AggregationBuilder($emptyQuery);

            $buildSubAggs($subAggregation);

            $subAggregations = $subAggregation->query->getAggregations();

            foreach ($subAggregations as $subAgg) {
                $aggregation->addAggregation($subAgg);
            }
        }
        $this->append($aggregation);

        return $aggregation;
    }

    /**
     * Add an ipv4 range aggregate.
     *
     * @param       $alias
     * @param null  $field
     * @param array $ranges
     */
    public function ipv4Range($alias, $field, array $ranges)
    {
        $aggregation = new Ipv4RangeAggregation($alias, $field, $ranges);

        $this->append($aggregation);

        return $aggregation;
    }

    /**
     * Add an max aggregate.
     *
     * @param             $alias
     * @param string|null $field
     * @param string|null $script
     */
    public function max($alias, $field = null, $script = null)
    {
        $aggregation = new MaxAggregation($alias, $field, $script);

        $this->append($aggregation);
    }

    /**
     * Add an min aggregate.
     *
     * @param             $alias
     * @param string|null $field
     * @param string|null $script
     */
    public function min($alias, $field = null, $script = null)
    {
        $aggregation = new MinAggregation($alias, $field, $script);

        $this->append($aggregation);
    }

    /**
     * Add an missing aggregate.
     *
     * @param string $alias
     * @param string $field
     */
    public function missing($alias, $field)
    {
        $aggregation = new MissingAggregation($alias, $field);

        $this->append($aggregation);
    }

    /**
     * Add an percentile aggregate.
     *
     * @param        $alias
     * @param string $field
     * @param        $percents
     * @param null   $script
     * @param null   $compression
     */
    public function percentile($alias, $field, $percents, $script = null, $compression = null)
    {
        $aggregation = new PercentilesAggregation($alias, $field, $percents, $script, $compression);

        $this->append($aggregation);
    }

    /**
     * Add an percentileRanks aggregate.
     *
     * @param        $alias
     * @param string $field
     * @param array  $values
     * @param null   $script
     * @param null   $compression
     */
    public function percentileRanks($alias, $field, array $values, $script = null, $compression = null)
    {
        $aggregation = new PercentileRanksAggregation($alias, $field, $values, $script, $compression);

        $this->append($aggregation);
    }

    /**
     * Add an stats aggregate.
     *
     * @param             $alias
     * @param string      $field
     * @param string|null $script
     */
    public function stats($alias, $field = null, $script = null)
    {
        $aggregation = new StatsAggregation($alias, $field, $script);

        $this->append($aggregation);
    }

    /**
     * Add an sum aggregate.
     *
     * @param             $alias
     * @param string      $field
     * @param string|null $script
     */
    public function sum($alias, $field = null, $script = null)
    {
        $aggregation = new SumAggregation($alias, $field, $script);

        $this->append($aggregation);

        return $aggregation;
    }

    /**
     * Add a value count aggregate.
     *
     * @param             $alias
     * @param string      $field
     * @param string|null $script
     */
    public function valueCount($alias, $field = null, $script = null)
    {
        $aggregation = new ValueCountAggregation($alias, $field, $script);

        $this->append($aggregation);
    }

    /**
     * Add a range aggregate.
     *
     * @param string $alias
     * @param string $field
     * @param array  $ranges
     * @param bool   $keyed
     */
    public function range($alias, $field, array $ranges, $keyed = false)
    {
        $aggregation = new RangeAggregation($alias, $field, $ranges, $keyed);

        $this->append($aggregation);
    }

    /**
     * Add a terms aggregate.
     *
     * @param string      $alias
     * @param string|null $field
     * @param string|null $script
     *
     * @return \Sleimanx2\Plastic\DSL\Aggregations\TermsAggregation
     */
    public function terms($alias, $field = null, $script = null)
    {

        if ($script instanceof \Closure) {
            /** @var TermsAggregation $aggregation */
            $aggregation = new TermsAggregation($alias, $field);

            $emptyQuery = $query = new \ONGR\ElasticsearchDSL\Search();

            $subAggregation = new AggregationBuilder($emptyQuery);

            $script($subAggregation);

            $subAggregations = $subAggregation->query->getAggregations();
            foreach ($subAggregations as $subAgg) {
                $aggregation->addAggregation($subAgg);
            }

        } else {
            /** @var TermsAggregation $aggregation */
            $aggregation = new TermsAggregation($alias, $field, $script);
        }


        $this->append($aggregation);

        return $aggregation;
    }

    /**
     * Adding nested aggregation
     *
     * @param          $alias
     * @param          $field
     * @param \Closure $callback
     *
     * @return \Sleimanx2\Plastic\DSL\Aggregations\NestedAggregation|\Sleimanx2\Plastic\DSL\Aggregations\TermsAggregation
     */
    public function nested($alias, $field, \Closure $callback)
    {
        /** @var TermsAggregation $aggregation */
        $aggregation = new NestedAggregation($alias, $field);

        $emptyQuery = $query = new \ONGR\ElasticsearchDSL\Search();

        $subAggregation = new AggregationBuilder($emptyQuery);

        $callback($subAggregation);

        $subAggregations = $subAggregation->query->getAggregations();

        foreach ($subAggregations as $subAgg) {
            $aggregation->addAggregation($subAgg);
        }

        $this->append($aggregation);

        return $aggregation;

    }

    /**
     * Add the weighted_avg aggregation
     *
     * @param $alias
     * @param $field
     * @param $weight
     *
     * @return \Sleimanx2\Plastic\DSL\Aggregations\WeightedAvgAggregation
     */
    public function weightedAvg($alias, $field, $weight)
    {

        $aggregation = new WeightedAvgAggregation($alias, $field, $weight);

        $this->append($aggregation);

        return $aggregation;
    }

    /**
     * Add bucket_script aggregation
     *
     * @param       $alias
     * @param array $bucketsPath
     * @param       $script
     *
     * @return \ONGR\ElasticsearchDSL\Aggregation\Pipeline\BucketScriptAggregation
     */
    public function bucketScript($alias, array $bucketsPath, $script)
    {

        $aggregation = new BucketScriptAggregation($alias, $bucketsPath, $script);

        $this->append($aggregation);

        return $aggregation;
    }

    /**
     * Add bucket_sort aggregation
     *
     * @param          $alias
     * @param array    $sorts
     * @param int|null $size
     * @param int|null $from
     *
     * @return \Sleimanx2\Plastic\DSL\Aggregations\BucketSortAggregation
     */
    public function bucketSort($alias, array $sorts, int $size = null, int $from = null)
    {

        $aggregation = new BucketSortAggregation($alias, $sorts, $size, $from);

        $this->append($aggregation);

        return $aggregation;
    }

    /**
     * Return the DSL query.
     *
     * @return array
     */
    public function toDSL()
    {
        return $this->query->toArray();
    }

    /**
     * Append an aggregation to the aggregation query builder.
     *
     * @param AbstractAggregation $aggregation
     */
    public function append(AbstractAggregation $aggregation)
    {
        $this->query->addAggregation($aggregation);
    }

    /**
     * Implementation the method from BuilderInterface
     *
     * @return string
     */
    public function getType()
    {
        return 'aggregations';
    }

    /**
     * Implementation the method from BuilderInterface
     *
     * @return array|mixed
     */
    public function toArray()
    {
        $array = $this->toDSL();
        if ($this->topLevel) {
            return $array['aggregations'];
        }
        return $array['aggregations']['aggregations'] ?? $array['aggregations'] ?? $array;
    }

    /**
     * Method required for serialization of the aggregation
     *
     * @return string
     */
    public function getName()
    {
        return $this->getType();
    }

    public function supportsNesting()
    {
        return true;
    }

    public function getArray()
    {
        return [];
    }

    public function flattenResult($result)
    {
        $aggregations = collect($this->query->getAggregations());
        $aggregationResults = $aggregations->mapWithKeys(function ($aggregation, $key) use ($result) {
            $fieldName = $aggregation->getName();
            if ($aggregation instanceof SumAggregation || $aggregation instanceof BucketScriptAggregation) {
                return [$fieldName => $result[$fieldName]['value'] ?? 0];
            }

            return $aggregation->flattenResult($result);
        });
    }

}
