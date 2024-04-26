<?php

namespace Tec\Page\Http\Controllers;

use Tec\Base\Events\BeforeUpdateContentEvent;
use Tec\Base\Events\CreatedContentEvent;
use Tec\Base\Events\DeletedContentEvent;
use Tec\Base\Events\UpdatedContentEvent;
use Tec\Base\Facades\Assets;
use Tec\Base\Facades\PageTitle;
use Tec\Base\Forms\FormBuilder;
use Tec\Base\Http\Controllers\BaseController;
use Tec\Base\Http\Responses\BaseHttpResponse;
use Tec\LanguageAdvanced\Models\PageTranslation;
use Tec\Page\Forms\PageForm;
use Tec\Page\Http\Requests\PageRequest;
use Tec\Page\Models\Page;
use Tec\Page\Repositories\Interfaces\PageInterface;
use Tec\Page\Tables\PageTable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageController extends BaseController
{
    /**
     * @var PageInterface
     */
    protected $pageRepository;
    public function __construct(PageInterface $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    public function index(PageTable $dataTable)
    {
        page_title()->setTitle(trans('packages/page::pages.menu_name'));
        Assets::addScriptsDirectly(['vendor/core/core/base/js/admin_duplicate.js'],'header');
        return $dataTable->renderTable();
    }

    public function DuplicatePage($id, Request $request){

        if((int)$id<1) return redirect(route('pages.index'));
        $menu =  Page::where('id','=',$id)->firstOrFail();
        $PageTranslation=new PageTranslation();
        $pagetrans = $PageTranslation->getModel()::where('pages_id','=',$id)->get();
        $new = $menu->replicate();
        $new->name=$request->input('name','Copy Page');
        $new->save();
        if($pagetrans->count()>0) {
            foreach ($pagetrans as $pagetran) {
                $newreplica = $pagetran->replicate();
                $newreplica->name = $request->input('name', 'Copy Page');
                $newreplica->pages_id = $new->id;
                $newreplica->save();
            }
        }
        return redirect(route('pages.index'));
    }
    /**
		 * /]
     * @return string
     */
    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('packages/page::pages.create'));

        return $formBuilder->create(PageForm::class)->renderForm();
    }

    public function store(PageRequest $request, BaseHttpResponse $response)
    {
        $page = Page::query()->create(array_merge($request->input(), [
            'user_id' => Auth::guard()->id(),
        ]));

        event(new CreatedContentEvent(PAGE_MODULE_SCREEN_NAME, $request, $page));

        return $response->setPreviousUrl(route('pages.index'))
            ->setNextUrl(route('pages.edit', $page->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit($id, FormBuilder $formBuilder)
    {
        $page  = $this->pageRepository->findOrFail($id);
        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $page->name]));

        return $formBuilder->create(PageForm::class, ['model' => $page])->renderForm();
    }

    public function update(Page $page, PageRequest $request, BaseHttpResponse $response)
    {
        event(new BeforeUpdateContentEvent($request, $page));

        $page->fill($request->input());
        $page->save();

        event(new UpdatedContentEvent(PAGE_MODULE_SCREEN_NAME, $request, $page));

        return $response
            ->setPreviousUrl(route('pages.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Page $page, Request $request, BaseHttpResponse $response)
    {
        try {
            $page->delete();

            event(new DeletedContentEvent(PAGE_MODULE_SCREEN_NAME, $request, $page));

            return $response->setMessage(trans('packages/page::pages.deleted'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
