<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 5.09.2014
 */

namespace opus\elastic\components;

/**
 * Class ActiveRecord
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @package opus\elastic\components
 */
class ActiveRecord extends \yii\elasticsearch\ActiveRecord
{
    /**
     * @return string the name of the index this record is stored in.
     */
    public static function index()
    {
        return \Yii::$app->get('elasticsearch')->index;
    }
}
