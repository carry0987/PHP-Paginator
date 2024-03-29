<?php
namespace carry0987\Paginator;

use carry0987\Paginator\Exceptions\PaginatorException;

class Paginator
{
    private const NUM_PLACEHOLDER = '(:num)';
    private array $itemArray;
    private int $totalItem = 0;
    private int $totalPage = 0;
    private int $itemsPerPage = 0;
    private int $currentPage = 1;
    private ?string $urlPattern = null;
    private int $maxPagesToShow = 10;
    private bool $alwaysShowPagination = false;

    /**
     * @param array $itemArray An array of items to paginate
     * @param int $itemsPerPage The number of items per page
     * @param int $currentPage The current page number
     * @param string $urlPattern A URL for each page, with (:num) as a placeholder for the page number. Ex. '/foo/page/(:num)'
     */
    public function __construct(array $itemArray, int $itemsPerPage, int $currentPage, string $urlPattern = '')
    {
        $this->itemArray = $itemArray;
        $this->setTotalItem(count($itemArray))
            ->setCurrentPage($currentPage)
            ->setUrlPattern($urlPattern)
            ->setItemsPerPage($itemsPerPage);
    }

    /**
     * @param bool $alwaysShow Set to true to always show pagination controls, even if there's only one page
     * 
     * @return Paginator
     */
    public function setAlwaysShowPagination(bool $alwaysShow): self
    {
        $this->alwaysShowPagination = $alwaysShow;

        return $this;
    }

    /**
     * @param int $maxPagesToShow
     * @throws PaginatorException if $maxPagesToShow is less than 3
     * 
     * @return Paginator
     */
    public function setMaxPagesToShow(int $maxPagesToShow): self
    {
        if ($maxPagesToShow < 3) {
            throw new PaginatorException('The value of [MaxPagesToShow] cannot be less than 3');
        }
        $this->maxPagesToShow = $maxPagesToShow;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxPagesToShow(): int
    {
        return $this->maxPagesToShow;
    }

    /**
     * @param int $currentPage
     * 
     * @return Paginator
     */
    public function setCurrentPage(int $currentPage): self
    {
        if ($currentPage <= 0) {
            throw new PaginatorException('The value of [CurrentPage] cannot be less than 1');
        }
        $this->currentPage = $currentPage;

        return $this;
    }

    /**
     * @param int $itemsPerPage
     * 
     * @return Paginator
     */
    public function setItemsPerPage(int $itemsPerPage): self
    {
        if ($itemsPerPage <= 0) {
            throw new PaginatorException('The value of [ItemsPerPage] cannot be less than 1');
        }
        $this->itemsPerPage = $itemsPerPage;
        $this->updateNumPage();

        return $this;
    }

    /**
     * @param int $totalItem
     * 
     * @return Paginator
     */
    public function setTotalItem(int $totalItem): self
    {
        if ($totalItem < 0) {
            throw new PaginatorException('The value of [TotalItem] cannot be less than 0');
        }
        $this->totalItem = $totalItem;
        $this->updateNumPage();

        return $this;
    }

    /**
     * @param string $urlPattern A URL for each page, with (:num) as a placeholder for the page number. Ex. '/foo/page/(:num)'
     * 
     * @return Paginator
     */
    public function setUrlPattern(string $urlPattern): self
    {
        $this->urlPattern = $urlPattern;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * @return int The total number of items
     */
    public function getTotalItem(): int
    {
        return $this->totalItem;
    }

    /**
     * @return int The total number of pages
     */
    public function getTotalPage(): int
    {
        return $this->totalPage;
    }

    /**
     * @return string
     */
    public function getUrlPattern(): string
    {
        return $this->urlPattern;
    }

    /**
     * @param int $pageNum The page number
     * 
     * @return string
     */
    public function getPageUrl(int $pageNum): string
    {
        return str_replace(self::NUM_PLACEHOLDER, $pageNum, $this->urlPattern);
    }

    /**
     * @return int|null
     */
    public function getNextPage(): ?int
    {
        if ($this->currentPage < $this->totalPage) {
            return $this->currentPage + 1;
        }

        return null;
    }

    /**
     * @return int|null
     */
    public function getPrevPage(): ?int
    {
        if ($this->currentPage > 1) {
            return $this->currentPage - 1;
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getNextUrl(): ?string
    {
        if (!$this->getNextPage()) {
            return null;
        }

        return $this->getPageUrl($this->getNextPage());
    }

    /**
     * @return string|null
     */
    public function getPrevUrl(): ?string
    {
        if (!$this->getPrevPage()) {
            return null;
        }

        return $this->getPageUrl($this->getPrevPage());
    }

    /**
     * @return string|null
     */
    public function getLastPageUrl(): ?string
    {
        if (!$this->getNextPage()) {
            return null;
        }

        return $this->getPageUrl($this->getTotalPage());
    }

    /**
     * @return string|null
     */
    public function getFirstPageUrl(): ?string
    {
        if (!$this->getPrevPage()) {
            return null;
        }

        return $this->getPageUrl(1);
    }

    /**
     * @return array
     */
    public function getPage(): array
    {
        return $this->calculatePage();
    }

    /**
     * @return int|null
     */
    public function getCurrentPageFirstItem(): ?int
    {
        $first = ($this->currentPage - 1) * $this->itemsPerPage + 1;
        if ($first > $this->totalItem) {
            return null;
        }

        return $first;
    }

    /**
     * @return int|null
     */
    public function getCurrentPageLastItem(): ?int
    {
        $first = $this->getCurrentPageFirstItem();
        if ($first === null) {
            return null;
        }
        $last = $first + $this->itemsPerPage - 1;
        if ($last > $this->totalItem) {
            return $this->totalItem;
        }

        return $last;
    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        $page = $this->getCurrentPage();
        $start = ceil(($page - 1) * $this->itemsPerPage);
        if ($this->totalItem > 0) {
            return array_slice($this->itemArray, $start, $this->itemsPerPage);
        }

        return [];
    }

    /**
     * Retrieve the full pagination result set along with additional pagination details.
     *
     * This method compiles the paginated result set, the total number of pages,
     * and the total number of items, returning them in an array for convenient access.
     *
     * Example usage:
     * [$result, $total_page, $total_item] = $paginator->getFullResult();
     *
     * @return array Returns an array containing the paginated result set,
     *               the total number of pages, and the total number of items.
     */
    public function getFullResult(): array
    {
        $result = $this->getResult();
        $total_page = $this->getTotalPage();
        $total_item = $this->getTotalItem();

        return [$result, $total_page, $total_item];
    }

    /**
     * Update the total number of pages.
     * 
     * @return void
     */
    private function updateNumPage(): void
    {
        $this->totalPage = ($this->itemsPerPage === 0) ? 0 : (int) ceil($this->totalItem / $this->itemsPerPage);
    }

    /**
     * Create a page data structure.
     *
     * @param int $page_num
     * @param bool $is_current
     * @return array
     */
    private function createPage(int $page_num, bool $is_current = false): array
    {
        return array(
            'num' => $page_num,
            'url' => $this->getPageUrl($page_num),
            'is_current' => $is_current
        );
    }

    /**
     * Create an ellipsis data structure.
     * 
     * @param string $ellipsis
     * 
     * @return array
     */
    private function createPageEllipsis(string $ellipsis = '...'): array
    {
        return array(
            'num' => $ellipsis,
            'url' => null,
            'is_current' => false
        );
    }

    /**
     * Get an array of paginated page data.
     *
     * Example:
     * array(
     *     array ('num' => 1,     'url' => '/example/page/1',  'isCurrent' => false),
     *     array ('num' => '...', 'url' => NULL,               'isCurrent' => false),
     *     array ('num' => 3,     'url' => '/example/page/3',  'isCurrent' => false),
     *     array ('num' => 4,     'url' => '/example/page/4',  'isCurrent' => true ),
     *     array ('num' => 5,     'url' => '/example/page/5',  'isCurrent' => false),
     *     array ('num' => '...', 'url' => NULL,               'isCurrent' => false),
     *     array ('num' => 10,    'url' => '/example/page/10', 'isCurrent' => false),
     * )
     *
     * @return array
     */
    private function calculatePage(): array
    {
        $pages = [];
        if ($this->totalPage <= 1 && !$this->alwaysShowPagination) {
            return [];
        }
        if ($this->totalPage <= $this->maxPagesToShow) {
            for ($i = 1; $i <= $this->totalPage; $i++) {
                $pages[] = $this->createPage($i, $i === $this->currentPage);
            }
        } else {
            //Determine the sliding range, centered around the current page
            $numAdjacents = (int) floor(($this->maxPagesToShow - 3) / 2);
            if ($this->currentPage + $numAdjacents > $this->totalPage) {
                $slidingStart = $this->totalPage - $this->maxPagesToShow + 2;
            } else {
                $slidingStart = $this->currentPage - $numAdjacents;
            }
            if ($slidingStart < 2) {
                $slidingStart = 2;
            }
            $slidingEnd = $slidingStart + $this->maxPagesToShow - 3;
            if ($slidingEnd >= $this->totalPage) {
                $slidingEnd = $this->totalPage - 1;
            }
            //Build the list of pages
            $num_middle = $this->maxPagesToShow - 2;
            //Create first page
            $pages[] = $this->createPage(1, $this->currentPage === 1);
            if ($slidingStart > 2) {
                $pages[] = $this->createPageEllipsis();
            }
            if (($slidingEnd - $slidingStart + 1) < $num_middle) {
                $slidingStart -= ($num_middle - ($slidingEnd - $slidingStart + 1));
            }
            for ($i = $slidingStart; $i <= $slidingEnd; $i++) {
                $pages[] = $this->createPage($i, $this->currentPage === $i);
            }
            if ($slidingEnd < $this->totalPage - 1) {
                $pages[] = $this->createPageEllipsis();
            }
            //Create last page
            $pages[] = $this->createPage($this->totalPage, $this->currentPage === $this->totalPage);
        }

        return $pages;
    }
}
