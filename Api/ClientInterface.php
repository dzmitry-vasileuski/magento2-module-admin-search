<?php

declare(strict_types=1);

namespace Vasileuski\AdminSearch\Api;

interface ClientInterface
{
    /**
     * Searches for documents based on the provided query.
     *
     * @param array $query The search query.
     *
     * @return array
     */
    public function search(array $query): array;

    /**
     * Bulks documents for indexing.
     *
     * @param array $query The bulk query.
     *
     * @return void
     */
    public function bulk(array $query): void;

    /**
     * Deletes an index.
     *
     * @param string $index The index to delete.
     *
     * @return void
     */
    public function deleteIndex(string $index): void;

    /**
     * Creates an index.
     *
     * @param string $index The index to create.
     * @param array $params Additional parameters for creating the index.
     *
     * @return void
     */
    public function createIndex(string $index, array $params = []): void;

    /**
     * Checks if an index exists.
     *
     * @param string $index The index to check.
     *
     * @return bool
     */
    public function indexExists(string $index): bool;
}
