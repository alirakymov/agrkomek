<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Designer;

use ArrayObject;

class CanvasState extends ArrayObject
{
    /**
     * @var string - index of block id
     */
    const BLOCK_ID = 'block-id';

    /**
     * @var string - endpoint option name
     */
    const ENDPOINT = 'endpoint';

    /**
     * Constructor
     *
     * @param array $_input (optional)
     * @param int $_flags (optional)
     * @param string $_iteratorClass (optional)
     */
    public function __construct($_input = [], int $_flags = 0, string $_iteratorClass = 'ArrayIterator')
    {
        parent::__construct($_input, $_flags, $_iteratorClass);
        $this->initialize();
    }

    /**
     * Initialize
     *
     * @return void
     */
    protected function initialize() : void
    {
        if (! isset($this['blocks']) || $this['blocks'] instanceof CanvasState) {
            return;
        }

        $this['blocks'] = new static($this['blocks']);
        foreach ($this['blocks'] as &$block) {
            if (isset($block['endpoint'])) {
                $block = new static($block);
            }
        }
    }

    /**
     *
     * Find block with endpoint and id
     *
     * @param string $_endpoint
     * @param id|string $_id
     *
     * @return CanvasState|null
     */
    public function find(string $_endpoint, $_id) : ?CanvasState
    {
        if (! isset($this['blocks']) || ! $this['blocks'] instanceof CanvasState) {
            return null;
        }

        foreach ($this['blocks'] as $block) {
            if (($block['endpoint'] ?? null) === $_endpoint && (string)($block['block-id'] ?? '') == (string)$_id ) {
                return $block;
            }
        }

        return null;
    }

    /**
     * Find blocks with endpoint
     *
     * @param string $_endpoint
     *
     * @return array<int, CanvasState>
     */
    public function findAll(string $_endpoint) : array
    {
        if (! isset($this['blocks']) || ! $this['blocks'] instanceof CanvasState) {
            return [];
        }

        $result = [];
        foreach ($this['blocks'] as $index => $block) {
            if (($block['endpoint'] ?? null) === $_endpoint) {
                $result[$index] = $block;
            }
        }

        return $result;
    }

    /**
     * Remove block by index
     *
     * @param int $_index
     *
     * @return void
     */
    public function remove(int $_index): void
    {
        if (isset($this['blocks'][$_index])) {
            unset($this['blocks'][$_index]);
        }
    }

    /**
     * Add new block to canvas state
     *
     * @param array $_block
     *
     * @return CanvasState
     */
    public function add(array $_block) : CanvasState
    {
        $this['blocks'] ??= new static();
        $this['blocks'][] = ($_block = new static($_block));
        return $_block;
    }

    /**
     * Prepare
     *
     * @return array
     */
    public function prepare() : array
    {
        foreach (($this['blocks'] ?? []) as $index => &$block) {
            if (isset($block['entity'])) {
                unset($block['entity']);
            }
        }

        return (array)$this;
    }

    /**
     * __invoke
     *
     * @param mixed $_target
     */
    public function __invoke($_index) : Model
    {
        return $this->getState($_index);
    }

    /**
     * Generate new Or return exists state object
     *
     * @param string $_index
     *
     * @return Model
     */
    public function getState(string $_index) : Model
    {
        $this[$_index] ??= new static();
        return $this[$_index];
    }

}
