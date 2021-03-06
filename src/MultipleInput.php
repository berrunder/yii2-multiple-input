<?php

/**
 * @link https://github.com/unclead/yii2-multiple-input
 * @copyright Copyright (c) 2014 unclead
 * @license https://github.com/unclead/yii2-multiple-input/blob/master/LICENSE.md
 */

namespace unclead\widgets;

use Yii;
use yii\base\Model;
use yii\widgets\InputWidget;
use yii\db\ActiveRecord;
use unclead\widgets\renderers\TableRenderer;


/**
 * Widget for rendering multiple input for an attribute of model.
 *
 * @author Eugene Tupikov <unclead.nsk@gmail.com>
 */
class MultipleInput extends InputWidget
{
    /**
     * @var ActiveRecord[]|array[] input data
     */
    public $data = null;

    /**
     * @var array columns configuration
     */
    public $columns = [];

    /**
     * @var integer inputs limit
     */
    public $limit;

    /**
     * @var array client-side attribute options, e.g. enableAjaxValidation. You may use this property in case when
     * you use widget without a model, since in this case widget is not able to detect client-side options
     * automatically.
     */
    public $attributeOptions = [];


    /**
     * Initialization.
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->guessColumns();
        $this->initData();
        parent::init();
    }

    /**
     * Initializes data.
     */
    protected function initData()
    {
        if (is_null($this->data) && $this->model instanceof Model) {
            foreach ((array) $this->model->{$this->attribute} as $index => $value) {
                $this->data[$index] = $value;
            }
        }
    }

    /**
     * This function tries to guess the columns to show from the given data
     * if [[columns]] are not explicitly specified.
     */
    protected function guessColumns()
    {
        if (empty($this->columns) && $this->hasModel()) {
            $this->columns = [
                [
                    'name' => $this->attribute,
                    'type' => MultipleInputColumn::TYPE_TEXT_INPUT
                ]
            ];
        }
    }

    /**
     * Run widget.
     */
    public function run()
    {
        $renderer = $this->createRenderer();
        return $renderer->render();
    }

    /**
     * @return TableRenderer
     */
    private function createRenderer()
    {
        return new TableRenderer([
            'id'                => $this->options['id'],
            'columns'           => $this->columns,
            'limit'             => $this->limit,
            'attributeOptions'  => $this->attributeOptions,
            'data'              => $this->data,
            'columnClass'       => MultipleInputColumn::className(),
            'context'           => $this
        ]);
    }
}