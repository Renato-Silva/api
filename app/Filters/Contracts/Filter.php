<?php

declare(strict_types=1);

namespace VOSTPT\Filters\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Filter
{
    /**
     * Get the table name.
     *
     * @return string
     */
    public function getTable(): string;

    /**
     * Get the order values.
     *
     * @return array
     */
    public static function getOrderValues(): array;

    /**
     * Get the sortable columns.
     *
     * @return array
     */
    public static function getSortableColumns(): array;

    /**
     * Get the searchable columns.
     *
     * @return array
     */
    public static function getSearchableColumns(): array;

    /**
     * Get the columns to be selected.
     *
     * @return array
     */
    public function getColumns(): array;

    /**
     * Get the qualified sorting column.
     *
     * @return string
     */
    public function getQualifiedSortColumn(): string;

    /**
     * Get the sorting column.
     *
     * @return string
     */
    public function getSortColumn(): string;

    /**
     * Set the sorting column.
     *
     * @param string $column
     *
     * @throws \OutOfBoundsException
     *
     * @return self
     */
    public function setSortColumn(string $column): self;

    /**
     * Get the sorting order.
     *
     * @return string
     */
    public function getSortOrder(): string;

    /**
     * Set the sorting order.
     *
     * @param string $order
     *
     * @throws \OutOfBoundsException
     *
     * @return self
     */
    public function setSortOrder(string $order): self;

    /**
     * Get the page number.
     *
     * @return int
     */
    public function getPageNumber(): int;

    /**
     * Set the page number.
     *
     * @param int $page
     *
     * @throws \OutOfBoundsException
     *
     * @return self
     */
    public function setPageNumber(int $page): self;

    /**
     * Get the number of items per page.
     *
     * @return int
     */
    public function getPageSize(): int;

    /**
     * Set the number of items per page.
     *
     * @param int $items
     *
     * @throws \OutOfBoundsException
     *
     * @return self
     */
    public function setPageSize(int $items): self;

    /**
     * Add a search for filtering.
     *
     * @param string $text
     *
     * @return self
     */
    public function withSearch(string $text): self;

    /**
     * Add relations to eager load.
     *
     * @param array $relations
     *
     * @return self
     */
    public function withRelations(array $relations): self;

    /**
     * Apply filters to the Query Builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    public function apply(Builder $builder): void;

    /**
     * Get the necessary elements to form a Filter signature.
     *
     * @return array
     */
    public function getSignatureElements(): array;

    /**
     * Get the Filter signature.
     *
     * @param bool $hash
     *
     * @return string
     */
    public function getSignature(bool $hash = false): string;
}
