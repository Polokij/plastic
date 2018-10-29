<?php


use ONGR\ElasticsearchDSL\Aggregation\Metric\SumAggregation;
use Sleimanx2\Plastic\DSL\AggregationBuilder;
use Sleimanx2\Plastic\DSL\Aggregations\BucketSortAggregation;
use Sleimanx2\Plastic\DSL\Aggregations\TermsAggregation;

class BucketSortAggregationTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function it_set_bucket_sort_aggregation()
    {

        $termsAggs = new TermsAggregation('post_id', 'post_id');
        $termsAggs->addAggregation(new SumAggregation('views', 'views'));

        /** The array to specify the the buckets order */
        /** @var array $sort */
        $sort = [
            'views' => ['order' => 'desc'],
            '_key'  => ['order' => 'asc'],
        ];

        $bucketAggregation = new BucketSortAggregation('views_bucket_sort', $sort, 10, 1);

        $termsAggs->addAggregation($bucketAggregation);

        /** @var \Sleimanx2\Plastic\DSL\SearchBuilder $query */
        $query = new \ONGR\ElasticsearchDSL\Search();
        $query->addAggregation($termsAggs);

        $aggsArray = $query->toArray();

        $expectedArray = [
            "aggregations" => [
                "post_id" => [
                    "terms"        => [
                        "field" => "post_id",
                    ],
                    "aggregations" => [
                        "views"             => [
                            "sum" => [
                                "field" => "views",
                            ],
                        ],
                        "views_bucket_sort" => [
                            "bucket_sort" => [
                                "sort" => [
                                    'views' => ['order' => 'desc'],
                                    '_key'  => ['order' => 'asc'],
                                ],
                                "size" => 10,
                                "from" => 1
                            ],
                        ],
                    ],
                ],
            ],
        ];


        $this->assertEquals($expectedArray, $aggsArray, "Terms aggregation array is not as expected", 0.0, 10, true);
    }

}
