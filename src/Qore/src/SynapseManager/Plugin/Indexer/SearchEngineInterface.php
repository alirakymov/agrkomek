<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Indexer;

use Manticoresearch\Index;

interface SearchEngineInterface
{
    /**
     * Set index name
     *
     * @param string $_index 
     *
     * @return void 
     */
    public function setIndexName(string $_index): void;

    /**
     * Set mapping structure 
     *
     * @param ModelInterface $_mapping 
     *
     * @return void 
     */
    public function setMapping(ModelInterface $_mapping): void;

    /**
     * Build index in search engine
     *
     * @return void
     */
    public function make(): void;

    /**
     * Put data to index of search engine
     *
     * @param array $_data 
     *
     * @return void
     */
    public function index(array $_data): void;

    /**
     * Return Index object
     *
     * @return \Manticoresearch\Index 
     */
    public function getIndex(): Index;

    /**
     * Search objects in index
     *
     * @param array $_filters 
     * @param array $_query (optional) 
     *
     * @return mixed
     */
    public function search(array $_filters, array $_query = []);

}
