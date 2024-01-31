<?php
namespace carry0987\Paginator;

use carry0987\Paginator\Exceptions\PaginatorException;

class Paginator
{
    private const NUM_PLACEHOLDER = '(:num)';
    private $itemArray;
    private $totalItem;
    private $totalPage;
    private $itemsPerPage;
    private $currentPage;
    private $urlPattern = null;
    private $maxPagesToShow = 10;
    private $page = null;
    private $start = null;

    /**
     * @param array $totalItem The total number of items
     * @param int $itemsPerPage The number of items per page
     * @param int $currentPage The current page number
     * @param string $urlPattern A URL for each page, with (:num) as a placeholder for the page number. Ex. '/foo/page/(:num)'
     */
    public function __construct(array $totalItem, int $itemsPerPage, int $currentPage, string $urlPattern = '')
    {
        $this->itemArray = $totalItem;
        $this->totalItem = count($totalItem);
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = $currentPage;
        $this->urlPattern = $urlPattern;
        $this->updateNumPage();
    }

    private function updateNumPage(): void
    {
        $this->totalPage = ($this->itemsPerPage === 0) ? 0 : (int) ceil($this->totalItem / $this->itemsPerPage);
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
    public function setCurrentPage(int $currentPage): Paginator
    {
        $this->currentPage = $currentPage;

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
     * @param int $itemsPerPage
     * 
     * @return Paginator
     */
    public function setItemsPerPage(int $itemsPerPage): Paginator
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->updateNumPage();

        return $this;
    }

    /**
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * @param int $totalItem
     * 
     * @return Paginator
     */
    public function setTotalItem(int $totalItem): Paginator
    {
        $this->totalItem = $totalItem;
        $this->updateNumPage();

        return $this;
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
     * @param string $urlPattern A URL for each page, with (:num) as a placeholder for the page number. Ex. '/foo/page/(:num)'
     * 
     * @return Paginator
     */
    public function setUrlPattern(string $urlPattern): Paginator
    {
        $this->urlPattern = $urlPattern;

        return $this;
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
    private function getPageUrl(int $pageNum): string
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
    public function getPage(): array
    {
        $pages = array();
        if ($this->totalPage <= 1) {
            return array();
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
        $this->page = $this->currentPage;
        if (empty($this->currentPage)) {
            $this->page = 1;
        }
        $this->pages = ceil($this->totalItem / $this->itemsPerPage);
        $this->start = ceil(($this->page - 1) * $this->itemsPerPage);
        if ($this->totalItem > 0) {
            return array_slice($this->itemArray, $this->start, $this->itemsPerPage);
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
}
