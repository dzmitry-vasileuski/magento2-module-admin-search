<?php

declare(strict_types=1);

namespace Vasileuski\AdminSearch\Model;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Vasileuski\AdminSearch\Api\ClientInterface;

class Search implements ArgumentInterface
{
    public function __construct(
        private UrlInterface $url,
        private Session $session,
        private IndexerConfig $indexerConfig,
        private ClientInterface $client,
    ) {}

    public function search(string $query): array
    {
        $allowedIndices = $this->getAllowedIndices();

        if (!$allowedIndices) {
            return [];
        }

        $request = [
            'index' => implode(',', $allowedIndices),
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => [

                        ],
                        'minimum_should_match' => 1,
                    ],
                ],
                'sort' => [
                    '_score' => 'desc',
                    '_id' => 'desc',
                ],
            ],
        ];

        foreach ($allowedIndices as $index) {
            $properties = $this->indexerConfig->get($index, 'properties');

            $indexedProperties = array_filter(
                $properties,
                fn($property) => $property['index'] ?? true
            );

            foreach ($indexedProperties as $property => $propertyConfig) {
                $request['body']['query']['bool']['should'][] = [
                    'match_bool_prefix' => [
                        $property => [
                            'query' => $query,
                            'boost' => 2,
                        ],
                    ],
                ];

                $request['body']['query']['bool']['should'][] =[
                    'match' => [
                        $property => [
                            'query' => $query,
                            'boost' => 4,
                            'operator' => 'or',
                        ],
                    ],
                ];

                $request['body']['query']['bool']['should'][] =[
                    'match' => [
                        $property => [
                            'query' => $query,
                            'boost' => 6,
                            'operator' => 'and',
                        ],
                    ],
                ];

                $request['body']['query']['bool']['should'][] = [
                    'match_phrase_prefix' => [
                        $property => [
                            'query' => $query,
                            'boost' => 8,
                        ],
                    ],
                ];

                $request['body']['query']['bool']['should'][] = [
                    'match_phrase' => [
                        $property => [
                            'query' => $query,
                            'boost' => 10,
                        ]
                    ]
                ];
            }
        }

        $result = $this->client->search($request);
        $hits = $result['hits']['hits'];

        return array_map(function ($hit) {
            $type = $this->indexerConfig->get($hit['_index'], 'type');
            $url = $this->indexerConfig->get($hit['_index'], 'url');

            $hit['_source']['_type'] = $type;
            $hit['_source']['_url'] = $this->url->getUrl($url['path'], [$url['param'] => $hit['_id']]);

            return $hit['_source'];
        }, $hits);
    }

    public function getAllowedIndices(): array
    {
        $allowedIndices = [];

        if (!$this->session->isAllowed('Magento_Backend::global_search')) {
            return $allowedIndices;
        }

        foreach ($this->indexerConfig->list() as $indexId) {
            $resource = $this->indexerConfig->get($indexId, 'resource');

            if ($this->session->isAllowed($resource)) {
                $allowedIndices[] = $indexId;
            }
        }

        return $allowedIndices;
    }
}
