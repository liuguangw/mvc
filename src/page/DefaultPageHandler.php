<?php
namespace liuguang\mvc\page;

class DefaultPageHandler implements PageHandler
{

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\page\PageHandler::getPageUrl()
     */
    public function getPageUrl(int $page): string
    {
        if ($page == 1) {
            return '/list/';
        }
        return '/list/' . $page . '/';
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \liuguang\mvc\page\PageHandler::getHtml()
     */
    public function getHtml(Paginator $paginator): string
    {
        if ($paginator->totalPage == 0) {
            return '';
        }
        $btnArr = [];
        // 上一页按钮
        $prevBtn = '';
        if ($paginator->currentPage == 1) {
            $prevBtn .= '<li class="page-item disabled">';
            $prevUrl = '#';
        } else {
            $prevBtn .= '<li class="page-item">';
            $prevUrl = $this->getPageUrl($paginator->currentPage - 1);
        }
        $prevBtn .= ('<a class="page-link" href="' . $prevUrl . '" aria-label="上一页">
    <span aria-hidden="true">&laquo;</span>
    <span class="sr-only">上一页</span>
  </a>
</li>');
        $btnArr[] = $prevBtn;
        // ...5.6.7 [8] 9.10.11...
        $pageStart = $paginator->currentPage - 3;
        $pageEnd = $paginator->currentPage + 3;
        if ($pageStart < 1) {
            $pageStart = 1;
        }
        if ($pageEnd > $paginator->totalPage) {
            $pageEnd = $paginator->totalPage;
        }
        if ($pageStart >= 2) {
            // 第一页
            $btnArr[] = $this->getPageBtn(1, false);
            if ($pageStart > 2) {
                $btnArr[] = '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
            }
        }
        for ($i = $pageStart; $i <= $pageEnd; $i ++) {
            $btnArr[] = $this->getPageBtn($i, $i == $paginator->currentPage);
        }
        if ($pageEnd < $paginator->totalPage) {
            if ($pageEnd < ($paginator->totalPage - 1)) {
                $btnArr[] = '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
            }
            // 最后一页
            $btnArr[] = $this->getPageBtn($paginator->totalPage, false);
        }
        // 下一页按钮
        $nextBtn = '';
        if ($paginator->currentPage == $paginator->totalPage) {
            $nextBtn .= '<li class="page-item disabled">';
            $nextUrl = '#';
        } else {
            $nextBtn .= '<li class="page-item">';
            $nextUrl = $this->getPageUrl($paginator->currentPage + 1);
        }
        $nextBtn .= ('<a class="page-link" href="' . $nextUrl . '" aria-label="下一页">
    <span aria-hidden="true">&raquo;</span>
    <span class="sr-only">下一页</span>
  </a>
</li>');
        $btnArr[] = $nextBtn;
        return '<ul class="pagination">' . implode(PHP_EOL, $btnArr) . '</ul>';
    }

    private function getPageBtn(int $page, bool $isActive): string
    {
        $pageUrl = $this->getPageUrl($page);
        if ($isActive) {
            return '<li class="page-item active">
  <a class="page-link" href="' . $pageUrl . '">' . $page . ' <span class="sr-only">(current)</span></a>
</li>';
        } else {
            return '<li class="page-item"><a class="page-link" href="' . $pageUrl . '">' . $page . '</a></li>';
        }
    }
}

