<?php $pager->setSurroundCount(2) ?>

<?php if ($pager->getPageCount() > 1): ?>
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center mb-0">

        <?php if ($pager->hasPreviousPage()): ?>
        <li class="page-item">
            <a class="page-link" href="<?= $pager->getPreviousPage() ?>">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        </li>
        <?php else: ?>
        <li class="page-item disabled">
            <span class="page-link"><i class="fa-solid fa-chevron-left"></i></span>
        </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link): ?>
        <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
            <a class="page-link" href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
        </li>
        <?php endforeach ?>

        <?php if ($pager->hasNextPage()): ?>
        <li class="page-item">
            <a class="page-link" href="<?= $pager->getNextPage() ?>">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        </li>
        <?php else: ?>
        <li class="page-item disabled">
            <span class="page-link"><i class="fa-solid fa-chevron-right"></i></span>
        </li>
        <?php endif ?>

    </ul>
</nav>
<?php endif ?>
