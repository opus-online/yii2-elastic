<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 26.09.2014
 */

namespace opus\elastic\search;

use Elastica\Param;

/**
 * Interface QueryHandlerInterface
 *
 * @package opus\elastic\search
 */
interface QueryHandlerInterface
{
    /**
     * Adds handle and returns query and filter
     * @param $config
     * @return Param{]
     */
    public function handle($config);

    /**
     * Adds special query case to elasticsearch request
     * @return mixed
     */
    public function addHandle();
} 