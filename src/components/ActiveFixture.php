<?php
/**
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @date 11.11.2014
 */

namespace opus\elastic\components;


use yii\base\InvalidParamException;
use yii\elasticsearch\ActiveRecord;
use yii\elasticsearch\Connection;
use yii\test\BaseActiveFixture;

/**
 * Class ActiveFixture
 *
 * @author Mihkel Viilveer <mihkel@opus.ee>
 * @package opus\elastic\components
 */
class ActiveFixture extends BaseActiveFixture
{
    /**
     * @var Connection|string the DB connection object or the application component ID of the DB connection.
     * After the DbFixture object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     */
    public $db = 'elasticsearch';

    /**
     * @var string
     */
    public $index;

    /**
     * @var string
     */
    public $type;

    public function init()
    {
        parent::init();

        /** @var ActiveRecord $model */
        $model = $this->modelClass;
        $this->index || $this->index = $model::index();
        $this->type || $this->type = $model::type();

        if (empty($this->type) || empty($this->index)) {
            throw new InvalidParamException('"index" and "type" must be specified');
        }

    }

    /**
     * Loads the fixture.
     *
     * The default implementation will first clean up the table by calling [[resetTable()]].
     * It will then populate the table with the data returned by [[getData()]].
     *
     * If you override this method, you should consider calling the parent implementation
     * so that the data returned by [[getData()]] can be populated into the table.
     */
    public function load()
    {
        $this->resetType();
        $this->data = [];
        foreach ($this->getData() as $alias => $row) {
            $this->db->createCommand()->insert($this->index, $this->type, $row);
            $this->data[$alias] = $row;
        }
    }


    /**
     * Returns the fixture data.
     *
     * This method is called by [[loadData()]] to get the needed fixture data.
     *
     * The default implementation will try to return the fixture data by including the external file specified by [[dataFile]].
     * The file should return an array of data rows (column name => column value), each corresponding to a row in the table.
     *
     * If the data file does not exist, an empty array will be returned.
     *
     * @return array the data rows to be inserted into the collection.
     */
    protected function getData()
    {
        if ($this->dataFile === false) {
            return [];
        }
        if ($this->dataFile !== null) {
            $dataFile = \Yii::getAlias($this->dataFile);
        } else {
            $class = new \ReflectionClass($this);
            $dataFile = dirname($class->getFileName()) . '/data/' . $this->type . '.php';
        }
        return is_file($dataFile) ? require($dataFile) : [];
    }

    /**
     * @return mixed
     */
    private function resetType()
    {
        return $this->db->createCommand(
            [
                'queryParts' =>
                    [
                        'query' => ['match_all' => []]
                    ],
                'index' => $this->index,
                'type' => $this->type,
            ]
        )->deleteByQuery();
    }
}
