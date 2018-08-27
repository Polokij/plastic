<?php


class AggregationTermsSubAggregationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_sets_a_terms_aggregation()
    {
        $builder = $this->getBuilder();
        $terms  = $builder->terms('campaign', 'campaign_id');
        $expectedArray = ['aggregations' => ['campaign' => ['terms' => ['field' => 'campaign_id']]]];
        $this->assertEquals($expectedArray, $builder->toDSL());

        /** Testing the building of hierarchy of aggregations   */
        /** @var \Sleimanx2\Plastic\DSL\AggregationBuilder $nestedAggregation */
        $nestedAggregation = $this->getBuilder();
        $nestedAggregation->sum('views', 'views');
        $nestedAggregation->sum('likes', 'likes');
        $nestedAggregation->topLevel = false;

        $terms->addAggregation($nestedAggregation);

        /** Expected array */
        $expectedArray['aggregations']['campaign'] = [
            'terms' => [
                'field' => 'campaign_id'
            ],
            "aggregations" => [
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
        ];
        ini_set('xdebug.var_display_max_depth', 10);

        $this->assertEquals($expectedArray, $builder->toDSL(), "\$canonicalize = true", 0.0, 20, true);

    }

    private function getBuilder()
    {
        $query = new \ONGR\ElasticsearchDSL\Search();

        return new Sleimanx2\Plastic\DSL\AggregationBuilder($query);
    }
}
