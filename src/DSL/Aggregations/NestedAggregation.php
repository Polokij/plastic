<?php
/**
 * Created by PhpStorm.
 * User: vitaliy
 * Date: 24.08.18
 * Time: 16:40
 */

namespace Sleimanx2\Plastic\DSL\Aggregations;

use ONGR\ElasticsearchDSL\BuilderBag;

/**
 * Class NestedAggregation
 *
 * Overriding the method from \ONGR\ElasticsearchDSL library
 *
 * @package Sleimanx2\Plastic\DSL\Aggregations
 */
class NestedAggregation extends TermsAggregation
{

    /**
     * Inner aggregations container init.
     *
     * @param string $name
     * @param string $field
     */
    public function __construct($name, $field = null)
    {
        parent::__construct($name);
        $this->setField($field);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'nested';
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $data = array_filter(
            [
                'path' => $this->getField(),
            ]
        );

        return $data;
    }



}