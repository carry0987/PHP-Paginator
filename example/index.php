<?php
require dirname(__DIR__).'/vendor/autoload.php';

use carry0987\Paginator\Paginator;

//Use for loop to create variable $totalItems that have 1000 items
$totalItems = array();
for ($i = 0; $i < 1000; $i++) {
    $totalItems[] = $i;
}
$itemsPerPage = 50;
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 8;
$urlPattern = '?page=(:num)';

$paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
$paginator->setMaxPagesToShow(10);

$get_all_page = $paginator->getPage();
$total_page = $paginator->getTotalPage();
$prev_page = $paginator->getPrevUrl();
$next_page = $paginator->getNextUrl();
$first_page = $paginator->getFirstPageUrl();
$last_page = $paginator->getLastPageUrl();
?>
<html>
    <head>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    </head>
    <body>
        <!---Pagination--->
<?php if ($total_page > 1) { ?>
        <nav aria-label="Page navigation">
            <ul class="pagination">
<?php if ($first_page !== null) { ?>
            <li class="page-item"><a class="page-link" href="<?=$first_page;?>">&laquo;</a></li>
<?php } if ($prev_page !== null) { ?>
            <li class="page-item"><a class="page-link" href="<?=$prev_page;?>">Prev</a></li>
<?php } if (is_array($get_all_page)) foreach ($get_all_page as $page) { if ($page['is_current'] === true) { ?>
            <li class="page-item active" aria-current="page"><a class="page-link" href="<?=$page['url'];?>"><?=$page['num'];?></a></li>
<?php } elseif ($page['url'] === null) { ?>
            <li class="page-item"><a class="page-link"><?=$page['num'];?></a></li>
<?php } else { ?>
            <li class="page-item"><a class="page-link" href="<?=$page['url'];?>"><?=$page['num'];?></a></li>
<?php } ?>
<?php } if ($next_page !== null) { ?>
            <li class="page-item"><a class="page-link" href="<?=$next_page;?>">Next</a></li>
<?php } if ($last_page !== null) { ?>
            <li class="page-item"><a class="page-link" href="<?=$last_page;?>">&raquo;</a></li>
<?php } ?>
            </ul>
		</nav>
<?php } ?>
        <p>
            <?php echo $paginator->getTotalItem(); ?> found.
            Showing 
            <?php echo $paginator->getCurrentPageFirstItem(); ?> 
            - 
            <?php echo $paginator->getCurrentPageLastItem(); ?>.
        </p>
    </body>
</html>
