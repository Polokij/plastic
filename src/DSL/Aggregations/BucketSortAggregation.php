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
use ONGR\ElasticsearchDSL\BuilderBag;
use ONGR\ElasticsearchDSL\BuilderInterface;

/**
 * Class TermsAggregation
 * Overriding the method from \ONGR\ElasticsearchDSL library
 *
 * @package Sleimanx2\Plastic\DSL\Aggregations
 */
class BucketSortAggregation extends AbstractAggregation
{

    private $sorts;
    private $from = null;
    private $size = null;

    function __construct($name, array $sorts, int $size = null, int $from = null)
    {
        $this->sorts = $sorts;
        $this->size = $size;
        $this->from = $from;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $data = [
            'sort' => $this->getSorts(),
        ];

        $this->size !== null && $data['size'] = $this->size;
        $this->from !== null && $data['from'] = $this->from;

        return $data;
    }

    public function getType(){

        return "bucket_sort";
    }

    public function supportsNesting()
    {
        return false;
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

    /**
     * @return int|null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int|null $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }


    /**
     * @return null
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param null $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }



}