<div class="container">
    <h3 class="page-intro__title">{{ $page->name }}</h3>
    @if((int)($page->has_breadcrumb??1)>0)
    {!! Theme::breadcrumb()->render() !!}
    @endif
</div>
<div>
    {!! apply_filters(PAGE_FILTER_FRONT_PAGE_CONTENT, clean($page->content), $page) !!}
</div>
