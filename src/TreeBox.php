<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Ocl\TreeBox;

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;
use Osynapsy\Ocl\HiddenBox;
use Osynapsy\DataStructure\Tree;

/**
 * Description of TreeBox
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class TreeBox extends Component
{
    private $nodeOpenIds = [];
    private $refreshOnClick = [];
    private $refreshOnOpen = [];
    private $dataTree;

    const CLASS_SELECTED_LABEL = 'osy-treebox-label-selected';
    const ICON_NODE_CONNECTOR_EMPTY = '<span class="tree tree-null">&nbsp;</span>';
    const ICON_NODE_CONNECTOR_LINE = '<span class="tree tree-con-4">&nbsp;</span>';
    const ROOT_ID = 0;

    public function __construct($id)
    {
        parent::__construct('div', $id);
        $this->add(new HiddenBox("{$id}_sel"))->setClass('selectedNode');
        $this->add(new HiddenBox("{$id}_opn"))->setClass('openNodes');
        $this->setClass('osy-treebox');
        $this->requireJs('ocl-treebox.js');
        $this->requireCss('ocl-treebox.css');
    }

    protected function __build_extra__(): void
    {
        foreach ($this->dataTree->get() as $node) {
            $this->add($this->nodeFactory($node));
        }
        if (!empty($this->refreshOnClick)) {
            $this->att('data-refresh-on-click', implode(',', $this->refreshOnClick));
        }
        if (!empty($this->refreshOnOpen)) {
            $this->att('data-refresh-on-open', implode(',', $this->refreshOnOpen));
        }
    }

    protected function nodeFactory($item, $icons = []) : Tag
    {
        if ($item['_level'] > -1){
            $icons[$item['_level']] = $item['_position'] === Tree::POSITION_END ? self::ICON_NODE_CONNECTOR_EMPTY: self::ICON_NODE_CONNECTOR_LINE;
        }
        return empty($item['_childrens']) ? $this->leafFactory($item, $icons) : $this->branchFactory($item, $icons);
    }

    protected function leafFactory($item, $icons) : Tag
    {
       $leaf = new Tag('div', null, 'osy-treebox-leaf');
       if (!empty($this->refreshOnClick)) {
           $leaf->addClass('osy-treebox-node');
           $leaf->att(['data-level' => $item['_level'], 'data-node-id' => $item[0]]);
       }
       $leaf->add($this->iconFactory($item, $icons));
       $leaf->add(new Tag('span', null, 'osy-treebox-node-label'))->add(new Tag('span', null, 'osy-treebox-label'))->add($item[1]);
       if (count($item) > 4) {
           $leaf->add($this->commandFactory($item));
       }
       return $leaf;
    }

    protected function branchFactory($item, $icons) : Tag
    {
        $branch = new Tag('div', null, 'osy-treebox-branch');
        if (!empty($this->refreshOnClick)) {
            $branch->addClass('osy-treebox-node');
            $branch->att(['data-level' => $item['_level'], 'data-node-id' => $item[0]]);
        }
        $branch->add($this->branchHeadFactory($item, $icons));
        $branch->add($this->branchBodyFactory($item, $icons));
        return $branch;
    }

    protected function branchHeadFactory($item, $icons)
    {
        $head = new Tag('div', null, 'osy-treebox-node-head');
        $head->add($this->iconFactory($item, $icons));
        $label = $head->add(new Tag('span', '', 'osy-treebox-node-label'));
        $label->add($item[1]);
        if (!empty($this->refreshOnClick)) {
           $label->addClass('osy-treebox-label');
        }
        if (count($item) > 4) {
           $head->add($this->commandFactory($item));
       }
        return $head;
    }

    protected function branchBodyFactory($item, $icons)
    {
        $branchBody = new Tag('div', null, 'osy-treebox-branch-body');
        if (!in_array($item[0], $this->nodeOpenIds) && ($item[3] != '1')) {
            $branchBody->addClass('d-none');
        }
        foreach ($item['_childrens'] as $node) {
            $branchBody->add($this->nodeFactory($node, $icons));
        }
        return $branchBody;
    }

    private function iconFactory($node, $icons = [])
    {
        $class = "osy-treebox-branch-command tree-plus-".(!empty($node['_level']) && $node['_position'] === Tree::POSITION_BEGIN ? Tree::POSITION_BETWEEN : $node['_position']);
        if (empty($node['_childrens'])){
            $class = "tree-con-{$node['_position']}";
        } elseif (in_array($node[0], $this->nodeOpenIds) || !empty($node[3])) { //If node is open load minus icon
            $class .= ' minus';
        }
        //Sovrascrivo l'ultima icona con il l'icona/segmento corrispondente al comando / posizione
        $icons[$node['_level']] = sprintf('<span class="tree %s">&nbsp;</span>', $class);
        return implode('',$icons);
    }

    private function commandFactory($node)
    {
        $dummy = new Tag('dummy');
        if (count($node) < 4){
            return $dummy;
        }
        foreach($node as $i => $command) {
            if ($i <= 3 || empty($command) || !is_int($i)) {
                continue;
            }
            $dummy->add(new Tag('span', null, 'osy-treebox-node-command'))->add($command);
        }
        return $dummy;
    }

    public function getPath()
    {
        return $this->pathSelected;
    }

    public function onClickRefresh($componentId)
    {
        $this->refreshOnClick[] = $componentId;
        return $this;
    }

    public function onOpenRefresh($componentId)
    {
        $this->refreshOnOpen[] = $componentId;
        return $this;
    }

    public function setData($data, $keyId = 0, $keyParentId = 2, $keyIsOpen = 3)
    {
        parent::setData($data);
        if (empty($this->data)){
            return $this;
        }
        $this->dataTree = new Tree($keyId, $keyParentId, $keyIsOpen, $this->getData());
        return $this;
    }
}
