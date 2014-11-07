<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 30.10.2014
 */

namespace opus\elastic\search;

/**
 * Interface ResultsFormatterInterface
 *
 * @package opus\shop\elastic\search\result
 */
interface ResultsFormatterInterface
{
    /**
     * @return AbstractResultWidget[]
     */
    public function format();

    /**
     * @return int
     */
    public function getCounter();
}