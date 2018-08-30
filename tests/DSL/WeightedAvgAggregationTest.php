<?php


use Sleimanx2\Plastic\DSL\Aggregations\WeightedAvgAggregation;

class WeightedAvgAggregationTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function it_set_weigthed_avg_aggregation()
    {
        $weightedAvg = new WeightedAvgAggregation('view_avg_duration', 'view_avg_duration_metric', "views");
        $aggsArray = $weightedAvg->toArray();
        $expectedArray = [
            "view_avg_duration" => [
                "value"  => [
                    "field" => "view_avg_duration_metric",
                ],
                "weight" => [
                    "field" => "views",
                ],
            ],
        ];

        $this->assertEquals($expectedArray, $aggsArray, "Weight avg aggregation array is not as expected", 0.0, 10, true);
    }


}
