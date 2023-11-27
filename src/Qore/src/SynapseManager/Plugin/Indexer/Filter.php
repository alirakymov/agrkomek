<?php

namespace Qore\SynapseManager\Plugin\Indexer;

use ArrayObject;
use Qore\SynapseManager\Structure\Entity\SynapseServiceSubject;

class Filter extends ArrayObject implements FilterInterface
{
    /**
     * @var string
     */
    private string $_path;

    /**
     * @var array
     */
    private array $_filters;

    /**
     * @var ?SynapseServiceSubject
     */
    private ?SynapseServiceSubject $_subject;

    /**
     * Constructor
     *
     * @param string $_path - subject path
     * @param array $_filters - flters of subject
     * @param SynapseServiceSubject|null $_subject - subject 
     */
    public function __construct(string $_path, array $_filters, ?SynapseServiceSubject $_subject)
    {
        $this->_path = $_path;
        $this->_filters = $_filters;
        $this->_subject = $_subject;

        parent::__construct($_filters);
    }

    /**
     * @inheritdoc
     */
    public function getPath(): string
    {
        return $this->_path;
    }

    /**
     * Get subject name
     *
     * @return string 
     */
    public function getSubject(): string
    {
        $path = explode('.', $this->_path);
        return array_shift($path);
    }

    /**
     * @inheritdoc
     */
    public function prepareFilters(): array
    {
        $result = [];

        if (! $this->_path) {
            foreach ($this as $property => $value) {
                $result[$property] = $value;
            }
        } else {
            $path = explode('.', $this->_path);
            $subject = array_shift($path);

            if (! is_null($this->_subject) && $this->_subject->isToOne()) {
                foreach ($this as $property => $value) {
                    $result[sprintf('%s.`%s`', $subject, implode('.', array_merge($path, [$property])))] = $value;
                }
            } elseif (isset($this['id'])) {
                $result[sprintf('any(%s)', $subject)] = $this['id'];
            }
        }

        return $result;
    }

}
