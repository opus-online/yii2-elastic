<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 3.09.2014
 */

namespace opus\elastic\search;


use yii\base\Widget;

/**
 * Widget renders all the AbstractHit objects
 * Class ResultListWidget
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @package opus\elastic\search
 */
class ResultListWidget extends Widget
{
    /**
     * @var AbstractResultWidget[]
     */
    public $items = [];

    /**
     * Hit view path hint
     * @var string
     */
    public $resultViewPath = null;

    /**
     * Custom view params
     * @var array
     */
    public $params = [];

    /**
     * Applies template hint to hit and renders it
     * @return null|string
     */
    public function run()
    {
        $listHtml = null;
        foreach ($this->items as $item) {
            $item->setViewPath($this->resultViewPath);
            $listHtml .= $item->render($item->getViewFilePath(), $this->params);
        }
        return $listHtml;
    }

} 
