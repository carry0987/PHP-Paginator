<?php
namespace carry0987;

class Paginator
{
    const NUM_PLACEHOLDER = '(:num)';
    private $itemArray;
    private $totalItem;
    private $totalPage;
    private $itemsPerPage;
    private $currentPage;
    private $urlPattern = null;
    private $maxPagesToShow = 10;

    /**
     * @param array $totalItem The total number of items
     * @param int $itemsPerPage The number of items per page
     * @param int $currentPage The current page number
     * @param string $urlPattern A URL for each page, with (:num) as a placeholder for the page number. Ex. '/foo/page/(:num)'
     */
    public function __construct(array $totalItem, int $itemsPerPage, int $currentPage, $urlPattern = '')
    {
        $this->itemArray = $totalItem;
        $this->totalItem = (is_array($totalItem)) ? count($totalItem) : 0;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = $currentPage;
        $this->urlPattern = $urlPattern;
        $this->updateNumPage();
    }

    private function updateNumPage()
    {
        $this->totalPage = ($this->itemsPerPage == 0) ? 0 : (int) ceil($this->totalItem / $this->itemsPerPage);
    }

    /**
     * @param int $maxPagesToShow
     * @throws \InvalidArgumentException if $maxPagesToShow is less than 3
     */
    public function setMaxPagesToShow($maxPagesToShow)
    {
        if ($maxPagesToShow < 3) {
            throw new \InvalidArgumentException('The value of [MaxPagesToShow] cannot be less than 3');
        }
        $this->maxPagesToShow = $maxPagesToShow;
    }

    /**
     * @return int
     */
    public function getMaxPagesToShow()
    {
        return $this->maxPagesToShow;
    }

    /**
     * @param int $currentPage
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @param int $itemsPerPage
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->updateNumPage();
    }

    /**
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * @param int $totalItem
     */
    public function setTotalItem($totalItem)
    {
        $this->totalItem = $totalItem;
        $this->updateNumPage();
    }

    /**
     * @return int
     */
    public function getTotalItem()
    {
        return $this->totalItem;
    }

    /**
     * @return int
     */
    public function getTotalPage()
    {
        return $this->totalPage;
    }

    /**
     * @param string $urlPattern
     */
    public function setUrlPattern($urlPattern)
    {
        $this->urlPattern = $urlPattern;
    }

    /**
     * @return string
     */
    public function getUrlPattern()
    {
        return $this->urlPattern;
    }

    /**
     * @param int $pageNum
     * @return string
     */
    private function getPageUrl($pageNum)
    {
        return str_replace(self::NUM_PLACEHOLDER, $pageNum, $this->urlPattern);
    }

    public function getNextPage()
    {
        if ($this->currentPage < $this->totalPage) {
            return $this->currentPage + 1;
        }
        return null;
    }

    public function getPrevPage()
    {
        if ($this->currentPage > 1) {
            return $this->currentPage - 1;
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getNextUrl()
    {
        if (!$this->getNextPage()) {
            return null;
        }
        return $this->getPageUrl($this->getNextPage());
    }

    /**
     * @return string|null
     */
    public function getPrevUrl()
    {
        if (!$this->getPrevPage()) {
            return null;
        }
        return $this->getPageUrl($this->getPrevPage());
    }

    /**
     * @return string|null
     */
    public function getLastPageUrl()
    {
        if (!$this->getNextPage()) {
            return null;
        }
        return $this->getPageUrl($this->getTotalPage());
    }

    /**
     * @return string|null
     */
    public function getFirstPageUrl()
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
    public function getPage()
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
     * @return Array
     */
    private function createPage($page_num, $is_current = false)
    {
        return array(
            'num' => $page_num,
            'url' => $this->getPageUrl($page_num),
            'is_current' => $is_current
        );
    }

    /**
     * @return array
     */
    private function createPageEllipsis($ellipsis = '...')
    {
        return array(
            'num' => $ellipsis,
            'url' => null,
            'is_current' => false
        );
    }

    public function getCurrentPageFirstItem()
    {
        $first = ($this->currentPage - 1) * $this->itemsPerPage + 1;
        if ($first > $this->totalItem) {
            return null;
        }
        return $first;
    }

    public function getCurrentPageLastItem()
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

    public function getResult()
    {
        if (empty($this->currentPage) === false) {
            $this->page = $this->currentPage;
        } else {
            $this->page = 1;
        }
        $this->pages = ceil($this->totalItem / $this->itemsPerPage);
        $this->start = ceil(($this->page - 1) * $this->itemsPerPage);
        $result = (is_array($this->itemArray)) ? array_slice($this->itemArray, $this->start, $this->itemsPerPage) : false;
        return $result;
    }

    public function getFullResult(&$result, &$total_page, &$total_item)
    {
        $result = $this->getResult();
        $total_page = $this->getTotalPage();
        $total_item = $this->getTotalItem();
    }
}
