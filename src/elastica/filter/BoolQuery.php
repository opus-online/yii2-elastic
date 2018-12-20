<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 14.11.2014
 */

namespace opus\elastic\elastica\filter;

use Elastica\Exception\InvalidException;
use Elastica\Filter\AbstractFilter;

/**
 * Class Bool
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @package opus\elastic\elastica\filter
 */
class BoolQuery extends \Elastica\Query\BoolQuery
{
    /**
     * Adds general filter based on type
     *
     * @param  string                               $type Filter type
     * @param  array|\Elastica\Filter\AbstractFilter $args Filter data
     * @throws \Elastica\Exception\InvalidException
     * @return \Elastica\Filter\Bool           Current object
     */
    protected function _addFilter($type, $args)
    {
        if ($args instanceof AbstractFilter) {
            $args = $args->toArray();
        }
        else if (!is_array($args)) {
            throw new InvalidException('Invalid parameter. Has to be array or instance of Elastica\Filter');
        }
        else{
            $parsedArgs = array();
            foreach($args as $filter){
                if($filter instanceof AbstractFilter){
                    $parsedArgs[] = $filter->toArray();
                } else {
                    $parsedArgs[] = $filter;
                }
            }
            $args = $parsedArgs;
        }

        $varName = '_' . $type;
        $this->{$varName}[] = $args;
        return $this;
    }
}