<?php
/**
 * Created by PhpStorm.
 * User: Thans
 * Date: 2019/1/4
 * Time: 16:07
 */

namespace thans\layuiAdmin;

use thans\layuiAdmin\Traits\Load;

class Table
{
    use Load;

    public $tmpl = 'table';

    public $url = '';

    public $title = '';

    public $col = '12';

    public $id = '';

    public $refresh = true;

    public $search = true;

    public $classMap = [
        'status' => table\Status::class,
    ];

    public $filter = [];

    public $action = [];

    public $page = true;

    public $fields = [];

    public $tools = [];

    public $toolWidth = 110;


    /**
     * 增加筛选条件。支持type类型：input\select。
     * @param string $title
     * @param string $name
     * @param string $type
     * @param array $options 如果select必须包含：title和value。
     * @return $this
     * @throws
     */
    public function filter($title, $name, $type = 'input', $options = [])
    {
        $this->createFilter($title, $name, $type, $options);
        return $this;
    }

    private function createFilter($title, $name, $type = 'input', $options = [])
    {
        $id = uniqid();
        $this->filter[] = [
            'title' => $title,
            'name' => $name,
            'type' => $type,
            'id' => $id,
            'options' => $options
        ];
        return $id;
    }

    /**
     * 日期筛选。支持 type 类型：year，month，date，time，datetime。传入options
     * options支持参数：range 开启范围面板
     * @param $title
     * @param $name
     * @param array $options
     * @return void
     * @throws
     */
    public function timeFilter($title, $name, $options = [])
    {
        $id = $this->createFilter($title, $name);

        $options['elem'] = "#input-{$id}";

        $options = json_encode($options);

        $this->builder->module('laydate');

        $this->script[] = <<<EOD
laydate.render({$options});
EOD;
    }

    public function action($title, $href)
    {
        $this->action[] = [
            'title' => $title,
            'href' => $href
        ];
        return $this;
    }

    public function init()
    {
        $this->id = uniqid();
        $this->builder->module('table');
        $this->builder->module('jquery');
        $this->builder->module('form');
    }

    public function end()
    {
        $this->toolParse();
        $code = $this->display(__DIR__ . DIRECTORY_SEPARATOR.'table'.DIRECTORY_SEPARATOR.'stub'.DIRECTORY_SEPARATOR.'table.js.stub');
        $this->builder->script('table', $code);
    }

    public function column($field, $title, $width = 100, $tpl = '', $attr = [])
    {
        $column = ['field' => $field, 'title' => $title, 'width' => $width, 'templet' => $tpl ? '#' . $tpl : ''];
        $this->fields[] = array_merge($column, $attr);
        return $this;
    }

    public function tool($title, $url, $type = 'primary', $method = 'get', $action = 'ajax', $condition = '')
    {
        $this->tools[] = [
            'title' => $title,
            'action' => $action,
            'method' => $method,
            'url' => $url,
            'type' => $type,
            'condition' => $condition,
        ];
        return $this;
    }

    public function htmlTool($html = '')
    {
        $this->tools[] = [
            'html' => $html
        ];
    }

    private function toolParse()
    {
        if (!empty($this->tools)) {
            $html = '';
            foreach ($this->tools as $val) {
                if (isset($val['html'])) {
                    $html .= $val['html'];
                    continue;
                }
                if ($val['type']) {
                    $class = 'layui-btn-' . $val['type'];
                } else {
                    $class = '';
                }
                $val['title-tips'] = $val['title'];
                if ($val['action'] == 'confirmAjax') {
                    $val['title-tips'] = '确定' . $val['title-tips'] . '吗？';
                }
                $tmp = "<a href='javascript:;' refresh='{$this->id}' admin-event='{$val['action']}' data-title='{$val['title-tips']}' data-href='{$val['url']}' method='{$val['method']}' class='layui-btn layui-btn-xs {$class}'>{$val['title']}</a>";
                if ($val['condition']) {
                    $tmp = '{{#  if('.$val['condition'].'){ }}'.$tmp.'{{#  } }} ';//条件
                }
                $html .= $tmp;
            }
            $this->builder->html[] = <<<EOD
<script type="text/html" id="tools">
{$html}
</script>
EOD;
            $this->column('', '操作', $this->toolWidth, 'tools', ['fixed' => 'right']);
        }
    }
}
