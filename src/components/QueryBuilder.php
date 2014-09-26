<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 27.08.2014
 */

namespace opus\elastic\components;

use Elastica\Param;
use yii\elasticsearch\Query;

/**
 * Class QueryBuilder
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @package opus\elastic\components
 */
class QueryBuilder extends \yii\elasticsearch\QueryBuilder
{
    /**
     * Parses the condition specification and generates the corresponding SQL expression.
     *
     * @param string|array $condition the condition specification. Please refer to [[Query::where()]] on how to specify a condition.
     * @throws \yii\base\InvalidParamException if unknown operator is used in query
     * @throws \yii\base\NotSupportedException if string conditions are used in where
     * @return string the generated SQL expression
     */
    public function buildCondition($condition)
    {
        if ($condition instanceof Param) {
            return $condition->toArray();
        }
        return parent::buildCondition($condition);
    }

    /**
     * Generates query from a [[Query]] object.
     * @param Query $query the [[Query]] object from which the query will be generated
     * @return array the generated SQL statement (the first array element) and the corresponding
     * parameters to be bound to the SQL statement (the second array element).
     */
    public function build($query)
    {
        if ($query->query instanceof Param) {
            $query->query = $query->query->toArray();
        }
        return parent::build($query);

    }
}
