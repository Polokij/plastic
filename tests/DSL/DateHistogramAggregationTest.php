<?php


use Sleimanx2\Plastic\DSL\AggregationBuilder;

class DateHistogramAggregationTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function it_sets_a_terms_sub_aggregation()
    {
        $builder = $this->getBuilder();


        $dateHistogramAggregation = $builder->dateHistogram('date', 'date_from', 'day');

        $expectedArray = [
            'aggregations' => [
                'date' => [
                    'date_histogram' => [
                        'field'    => 'date_from',
                        'interval' => 'day',
                    ],
                ],
            ],
        ];
        $this->assertEquals($expectedArray, $builder->toDSL());
    }

    /**
     * @test
     */
    public function it_sets_a_date_histogram_callback_sub_aggregation()
    {
        $builder = $this->getBuilder();

        /** @var \Sleimanx2\Plastic\DSL\Aggregations\DateHistogramAggregation $dateHistogram */
        $dateHistogram = $builder->dateHistogram('date', 'date_from', 'month',
            function (AggregationBuilder $builder) {
                $terms = $builder->terms('campaign', 'campaign_id', function (AggregationBuilder $builder) {
                    $builder->sum('views', 'views');
                    $builder->sum('likes', 'likes');
                    return $builder;
                });
                return $builder;
            });

        $dateHistogram->setParameters(['format' => 'yyyy-MM-dd']);

        $expectedArray = [
            'aggregations' => [
                'date' => [
                    'date_histogram' => [
                        'field'    => 'date_from',
                        'interval' => 'month',
                        'format'   => 'yyyy-MM-dd',
                    ],
                    'aggregations'   => [ // nested terms aggregations
                        'campaign' => [
                            'terms'        => [
                                'field' => 'campaign_id',
                            ],
                            "aggregations" => [ // nested metrics aggregations
                                "views" => [
                                    "sum" => [
                                        "field" => "views",
                                    ],
                                ],
                                "likes" => [
                                    "sum" => [
                                        "field" => "likes",
                                    ],
                                ],
                            ],
                        ],

                    ],
                ],
            ],
        ];


        ini_set('xdebug.var_display_max_depth', 10);
        $this->assertEquals($expectedArray, $builder->toDSL(), "\$canonicalize = true", 0.0, 20, true);

    }


    public function getBuilder()
    {
        $query = new \ONGR\ElasticsearchDSL\Search();

        return new Sleimanx2\Plastic\DSL\AggregationBuilder($query);
    }
}
