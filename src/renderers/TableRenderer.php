<?php

/**
 * @link https://github.com/unclead/yii2-multiple-input
 * @copyright Copyright (c) 2014 unclead
 * @license https://github.com/unclead/yii2-multiple-input/blob/master/LICENSE.md
 */

namespace unclead\widgets\renderers;

use yii\base\InvalidConfigException;
use yii\bootstrap\Button;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use unclead\widgets\components\BaseRenderer;
use unclead\widgets\components\BaseColumn;

/**
 * Class TableRenderer
 * @package unclead\widgets\renderers
 */
class TableRenderer extends BaseRenderer
{
    const ACTION_ADD    = 'plus';
    const ACTION_REMOVE = 'remove';

    /**
     * @return mixed
     */
    protected function internalRender()
    {
        $content = [];

        if ($this->hasHeader()) {
            $content[] = $this->renderHeader();
        }

        $content[] = $this->renderBody();
        $content = Html::tag('table', implode("\n", $content), [
            'class' => 'multiple-input-list table table-condensed'
        ]);

        return Html::tag( 'div', $content, [
            'id' => $this->id,
            'class' => 'multiple-input'
        ]);
    }

    /**
     * Renders the header.
     *
     * @return string
     */
    public function renderHeader()
    {
        $cells = [];
        foreach ($this->columns as $column) {
            /* @var $column BaseColumn */
            $cells[] = $this->renderHeaderCell($column);
        }
        if (is_null($this->limit) || $this->limit > 1) {
            $cells[] = Html::tag('th', '', [
                'class' => 'list-cell__button'
            ]);
        }

        return Html::tag('thead', Html::tag('tr', implode("\n", $cells)));
    }

    /**
     * Check that at least one column has a header.
     *
     * @return bool
     */
    private function hasHeader()
    {
        foreach ($this->columns as $column) {
            /* @var $column BaseColumn */
            if (!empty($column->title)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Renders the header cell.
     * @param BaseColumn $column
     * @return null|string
     */
    private function renderHeaderCell($column)
    {
        if ($column->isHiddenInput()) {
            return null;
        }
        $options = $column->headerOptions;
        Html::addCssClass($options, 'list-cell__' . $column->name);
        return Html::tag('th', $column->title, $options);
    }

    /**
     * Renders the body.
     *
     * @return string
     */
    protected function renderBody()
    {
        $rows = [];
        foreach ($this->data as $index => $item) {
            $rows[] = $this->renderRowContent($index, $item);
        }
        return Html::tag('tbody', implode("\n", $rows));
    }

    /**
     * Renders the row content.
     *
     * @param int $index
     * @param ActiveRecord|array $item
     * @return mixed
     * @throws InvalidConfigException
     */
    private function renderRowContent($index = null, $item = null)
    {
        $cells = [];
        $hiddenInputs = [];
        foreach ($this->columns as $column) {
            /* @var $column BaseColumn */
            $column->setModel($item);
            if ($column->isHiddenInput()) {
                $hiddenInputs[] = $this->renderCellContent($column, $index, $item);
            } else {
                $cells[] = $this->renderCellContent($column, $index, $item);
            }
        }

        if (is_null($this->limit) || $this->limit > 1) {
            $cells[] = $this->renderActionColumn($index);
        }

        if (!empty($hiddenInputs)) {
            $hiddenInputs = implode("\n", $hiddenInputs);
            $cells[0] = preg_replace('/^(<td[^>]+>)(.*)(<\/td>)$/', '${1}' . $hiddenInputs . '$2$3', $cells[0]);
        }

        $content = Html::tag('tr', implode("\n", $cells), [
            'class' => 'multiple-input-list__item',
        ]);

        return $content;
    }

    /**
     * Renders the cell content.
     *
     * @param BaseColumn $column
     * @param int|null $index
     * @param $data
     * @return string
     */
    public function renderCellContent($column, $index, $data)
    {
        $id    = $column->getElementId($index);
        $value = $column->prepareValue($data);
        $name  = $column->getElementName($index);
        $input = $column->renderInput($name, $value, [
            'id' => $id
        ]);

        if ($column->isHiddenInput()) {
            return $input;
        }

        $hasError = false;
        if ($column->enableError) {
            $error = $column->getFirstError($index);
            $hasError = !empty($error);
            $input .= "\n" . $column->renderError($error);
        }

        $wrapperOptions = [
            'class' => 'form-group field-' . $id
        ];

        if ($hasError) {
            Html::addCssClass($wrapperOptions, 'has-error');
        }
        $input = Html::tag('div', $input, $wrapperOptions);

        return Html::tag('td', $input, [
            'class' => 'list-cell__' . $column->name,
        ]);
    }


    /**
     * Renders the action column.
     *
     * @param null|int $index
     * @return string
     * @throws \Exception
     */
    private function renderActionColumn($index = null)
    {
        if (is_null($index)) {
            $action = self::ACTION_REMOVE;
            $type = '{multiple-btn-type}';
        } else {
            $action = $index == 0 ? self::ACTION_ADD : self::ACTION_REMOVE;
            $type = $index == 0 ? 'btn-default' : 'btn-danger';
        }

        $button = Button::widget(
            [
                'tagName' => 'div',
                'encodeLabel' => false,
                'label' => Html::tag('i', null, ['class' => 'glyphicon glyphicon-' . $action]),
                'options' => [
                    'class' => $type . ' multiple-input-list__btn btn js-input-' . $action,
                ]
            ]
        );
        return Html::tag('td', $button, [
            'class' => 'list-cell__button',
        ]);
    }

    /**
     * Returns template for using in js.
     *
     * @return string
     */
    protected function prepareTemplate()
    {
        return $this->renderRowContent();
    }
}