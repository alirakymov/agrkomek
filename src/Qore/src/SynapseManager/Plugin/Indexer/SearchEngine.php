<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Indexer;

use Cake\Collection\CollectionInterface;
use Manticoresearch\Index;
use Manticoresearch\Search;
use Qore\Manticore\Manticore;

/**
 * Class: IndexerFactory
 *
 */
class SearchEngine implements SearchEngineInterface
{
    /**
     * @var \Qore\Manticore\Manticore
     */
    private Manticore $_manticore;

    /**
     * @var string 
     */
    private string $indexName;

    /**
     * @var \Manticoresearch\Index
     */
    private Index $index;

    /**
     * @var ModelInterface 
     */
    private ModelInterface $mapping;

    /**
     * Constructor
     *
     * @param \Qore\Manticore\Manticore $_manticore
     */
    public function __construct(Manticore $_manticore)
    {
        $this->_manticore = $_manticore;
    }

    /**
     * @inheritdoc
     */
    public function setIndexName(string $_index): void
    {
        $this->indexName = $_index;
        $this->index = new Index($this->_manticore);
        $this->index->setName($this->indexName);
    }

    /**
     * @inheritdoc
     */
    public function setMapping(ModelInterface $_mapping): void
    {
        $this->mapping = $_mapping;
    }

    /**
     * @inheritdoc
     */
    public function make(): void
    {
        # - Drop index if exists
        $this->_manticore->indices()->drop([
            'index' => $this->indexName,
            'body' => ['silent' => true],
        ]);

        # - Prepare schema and create index
        list($attributes, $settings) = $this->prepareSchema($this->mapping);
        $this->index->create($attributes, $settings);
    }

    /**
     * Prepare index schema
     *
     * @param ModelInterface $_mapping
     *
     * @return array
     */
    private function prepareSchema(): array
    {
        $attributes = [];

        foreach ($this->mapping['properties'] as $attribute => $options) {
            if ($options instanceof ModelInterface) {
                $attributes[$attribute] = ['type' => $options['type']];
            } else {
                $attributes[$attribute] = $options;
            } 

            if ( $options['type'] == 'json') {
                $attributes[$this->getFieldNameForJsonValues($attribute)] = ['type' => 'text', 'json_values' => $attribute];
            }
        }

        $settings = array_merge(
            [
                'engine' => 'rowwise',
                'morphology' => 'lemmatize_ru_all, stem_enru'
            ],
            $_mapping['settings'] ?? []
        );

        return [ $attributes, $settings, ];
    }

    /**
     * @inheritdoc
     */
    public function index(array $_data): void
    {
        list($attributes, $settings) = $this->prepareSchema($this->mapping);

        $data = [];
        foreach ($_data as &$_item) {
            $item = [];
            foreach ($attributes as $attribute => $options) {
                if (isset($options['json_values'])) {
                    $flat = $this->toFlat($_item[$options['json_values']] ?? []);
                    $item[$attribute] = $flat ? implode(' ', array_values($flat)) : '';
                } else {
                    $item[$attribute] = $this->prepareAttribute($_item[$attribute] ?? null, $options);
                }
            }
            $data[] = $item;
        }

        try {
            $result = $this->index->replaceDocuments($data);
        } catch (\Throwable $e) {
            # TODO monolog
        }
    
    }

    /**
     * Get field name for values of json data
     *
     * @param string $_attribute 
     *
     * @return string 
     */
    private function getFieldNameForJsonValues(string $_attribute): string
    {
        return sprintf('%s_values', $_attribute);
    }

    /**
     * Prepare attribute value
     *
     * @param mixed $_value 
     * @param array $_options 
     *
     * @return mixed
     */
    private function prepareAttribute($_value, array $_options)
    {
        switch(true) {
            case $_options['type'] == 'int':
                return (int)$_value;
            case $_options['type'] == 'float':
                return (float)$_value;
            case $_options['type'] == 'multi':
                return $this->toMulti($_value ?? []);
            case $_options['type'] == 'json' || is_array($_value):
                return json_encode(
                    $this->toFlat($_value ?? []), 
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK
                );
        }

        return $_value ?? '';
    }

    /**
     * Convert array to flat structure
     *
     * @param array $_data 
     *
     * @return array
     */
    private function toFlat(array $_data): array
    {
        $result = [];

        ($flat = function($_data, $_path = []) use (&$result, &$flat) {
            foreach ($_data as $key => $item) {
                $path = implode('.', array_merge($_path, [$key]));
                if (! is_iterable($item)) {
                    $result[$path] = $item;
                } else {
                    $flat($item, array_merge($_path, [$key]));
                }
            }
        })($_data);
        
        return $result;
    }

    /**
     * Convert data to mva structure
     * e.g [7, 10, 88]
     *
     * @param array $_data 
     * @return array 
     */
    private function toMulti(array $_data): array 
    {
        $result = [];
        foreach ($_data ?? [] as $item) {
            $result[] = (int)$item['id'];
        }
        
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function search(array $_filters, array $_query = [])
    {
        $search = $this->index->search($_query['query'] ?? '');

        $filters = $this->prepareFilters($_filters);

        foreach ($filters as $attr => $param) {
            $param = ! is_array($param) ? ['equals', $param] : $param;
            $param[0] = $this->getOperator($param[0]);
            $search->filter($attr, ...$param);
        }

        # - Set cutoff option
        $search->option('cutoff', 0);

        # - Set limit with offset
        isset($_query['limit']) && $search->limit($_query['limit'])
            ->offset($_query['offset'] ?? 0);

        if (isset($_query['limit'], $_query['offset']) && $_query['limit'] + $_query['offset'] > 1000) {
            $search->option('max_matches', $_query['limit'] + $_query['offset']);
        }


        foreach ($_query['sort'] ?? [] as $attr => $direction) {
            if (is_int($attr)) {
                $attr = $direction;
                $direction = 'asc';
            }
            $search->sort($attr, $direction);
        }

        foreach ($_query['options'] ?? [] as $option => $value) {
            $search->option($option, $value);
        }

        $results = $search->get();
        return $results;
    }

    /**
     * Return index object
     *
     * @return \Manticoresearch\Index
     */
    public function getIndex(): Index
    {
        return $this->index;
    }

    /**
     * Prepare filters
     *
     * @param array $_filters 
     *
     * @return array 
     */
    private function prepareFilters(array $_filters): array
    {

        $result = [];
        foreach ($_filters as $filter) {
            if (! count($filter)) {
                continue;
            }

            $result = array_merge($result, $filter->prepareFilters());
        }
    
        return $result;
    }

    /**
     * Convert operator
     *
     * @param string $_op 
     *
     * @return string 
     */
    private function getOperator(string $_op): string
    {
        $map = [
            '=' => 'equals',
            '>' => 'gte',
            '>=' => 'gte',
            '<=' => 'lte',
            '<' => 'lt',
            'AND' => 'and',
            'OR' => 'or',
        ];

        return $map[$_op] ?? $_op;
    }

}
