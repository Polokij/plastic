<?php


use ONGR\ElasticsearchDSL\Aggregation\Metric\MinAggregation;
use Sleimanx2\Plastic\DSL\AggregationBuilder;
use Sleimanx2\Plastic\DSL\Aggregations\NestedAggregation;

class NestedAggregationTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function it_set_nested_aggregation()
    {

        $nestedAggs = new NestedAggregation('resellers', 'resellers');
        $minAggs = new MinAggregation('min_price', 'resellers.price');
        $nestedAggs->addAggregation($minAggs);

        $query = new \ONGR\ElasticsearchDSL\Search();
        $query->addAggregation($nestedAggs);

        $aggsArray = $query->toArray();

        $expectedArray = [
            "aggregations" => [
                "resellers" => [
                    "nested" => [
                        "path" => "resellers",
                    ],
                    "aggregations" => [
                        "min_price" => [
                            "min" => [
                                "field" => "resellers.price",
                            ],
                        ],
                    ],
                ],
            ],
        ];


        $this->assertEquals($expectedArray, $aggsArray, "Nested aggregation array is not as expected", 0.0, 10, true);
    }

    /**
     * @test
     */
    public function it_set_nested_aggregation_over_aggregationi_builder(){

        $aggsBuilder = $this->getBuilder();

        $aggsBuilder->nested('resellers', 'resellers', function(AggregationBuilder $builder){
            $builder->min('min_price', 'resellers.price');

            return $builder;
        });

        $aggsArray = $aggsBuilder->query->toArray();
        $expectedArray = [
            "aggregations" => [
                "resellers" => [
                    "nested" => [
                        "path" => "resellers",
                    ],
                    "aggregations" => [
                        "min_price" => [
                            "min" => [
                                "field" => "resellers.price",
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedArray, $aggsArray, "Nested aggregation array created over aggsBuilder is not as expected", 0.0, 10, true);
    }




    private function getBuilder()
    {
        $query = new \ONGR\ElasticsearchDSL\Search();

        return new AggregationBuilder($query);
    }

}
