<?php
use Elasticsearch\ClientBuilder;

class ElasticIndex
{
    protected $client;
    protected $index;
    protected $untoucheds = [ 'product' => [], 'category' => [] ];
    protected $fielddatas = [ 'product' => [], 'category' => [] ];
    protected $collections = [ 'product' => [], 'category' => [] ];

    public function __construct($host, $user, $password){
        $this->client = ClientBuilder::create()
            ->setHosts([ 'https://'.$user.':'.$password.'@'.$host ])
            ->build();
    }

    public function setIndex($name){
        $this->setIndexName($name);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setIndexName($index)
    {
        $this->index = $index;
    }

    public function sendProducts($idParam, $values)
    {
        if (!$this->existsIndex()) {
            // $this->mapperCollectionFields('product', $values[0]);
            $this->mapperFielddataFields('product', $values[0]);
            $this->sendMappers('product');
        }
        $this->send($idParam, $values);
    }

    public function sendCategories($idParam, $values)
    {
        if (!$this->existsIndex()) {
            // $this->mapperCollectionFields('category', $values[0]);
            $this->mapperFielddataFields('category', $values[0]);
            $this->sendMappers('category');
        }
        $this->send($idParam, $values);
    }

    public function deleteCategories ($ids)
    {
        $this->delete('category', $ids);
    }

    public function deleteProducts ($ids)
    {
        $this->delete('product', $ids);
    }

    //Verifica se existe o index
    private function existsIndex(){
        $params = array('index' => $this->index);
        return $this->client->indices()->exists($params);
    }

    private function isCollection($arr)
    {
        if (!is_array($arr)) return false;
        if (!isset($arr[0]) && is_array($arr)) return true;
        return false;
    }

    public function mapperUntouchedField ($type, $field2)
    {
        $this->untoucheds[$type][$field2] = [
            'type' => 'text',
            'fields' => [
                'untouched' => [
                    'type' => 'keyword'
                ]
            ]
        ];
    }

    private function mapperFielddataFields ($type, $element)
    {
        $collections = array_keys($this->collections[$type]);
        $untoucheds = array_keys($this->untoucheds[$type]);
        $notIncludes = array_merge($collections, $untoucheds);

        foreach ($element as $attr => $value) {
            if (!in_array($attr, $notIncludes) && gettype($value) === 'string') {
                $this->fielddatas[$type][$attr] = [
                    'type' => 'text',
                    'fielddata' => true,
                ];
            }
        }

    }

    private function mapperCollectionFields ($type, $element)
    {
        foreach ($element as $attr => $value) {
            if ($this->isCollection($value)) {
                $this->collections[$type][$attr] = [
                    'properties' => array_reduce(array_keys($value), function ($collection, $attribute) {
                    	$collection[$attribute] = [
	                    	'type' => 'text',
	                    	'fielddata' => true,
                    	];
                    	return $collection;
                    }, [])
                ];
            }
        }

    }

    private function sendMappers ($type)
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'settings' => [
                    'number_of_shards' => 2,
                    'number_of_replicas' => 0
                ],
                'mappings' => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => array_merge($this->collections[$type], $this->untoucheds[$type], $this->fielddatas[$type])
                ]
            ]
        ];

        $this->client->indices()->create($params);
    }

    protected function send($idParam, $values)
    {
        try {
            $objects = ['body' => []];
            foreach ($values as $body) {
                $objects['body'][] = [
                    'index' => [
                        '_index' => $this->index,
                        '_id'    => $body[$idParam]
                    ]
                ];

                $objects['body'][] = $body;
            }

            $this->client->bulk($objects);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    protected function delete($type, $ids)
    {
        try {
            foreach ($ids as $id) {
                $value = [];
                $value['index'] = $this->index;
                $value['id'] = $id;
                $value['type'] = $type;

                $this->client->delete($value);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
