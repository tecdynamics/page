<?php

namespace Tec\Page\Http\Controllers;

use Assets;
use Tec\Base\Events\BeforeEditContentEvent;
use Tec\Base\Events\CreatedContentEvent;
use Tec\Base\Events\DeletedContentEvent;
use Tec\Base\Events\UpdatedContentEvent;
use Tec\Base\Forms\FormBuilder;
use Tec\Base\Http\Controllers\BaseController;
use Tec\Base\Http\Responses\BaseHttpResponse;
use Tec\Base\Traits\HasDeleteManyItemsTrait;
use Tec\LanguageAdvanced\Models\PageTranslation;
use Tec\Page\Forms\PageForm;
use Tec\Page\Http\Requests\PageRequest;
use Tec\Page\Repositories\Interfaces\PageInterface;
use Tec\Page\Tables\PageTable;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class PageController extends BaseController
{

    use HasDeleteManyItemsTrait;

    /**
     * @var PageInterface
     */
    protected $pageRepository;

    /**
     * PageController constructor.
     * @param PageInterface $pageRepository
     */
    public function __construct(PageInterface $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * @param PageTable $dataTable
     * @return JsonResponse|View
     *
     * @throws Throwable
     */
    public function index(PageTable $dataTable)
    {
        page_title()->setTitle(trans('packages/page::pages.menu_name'));
        Assets::addScriptsDirectly(['vendor/core/core/base/js/admin_duplicate.js'],'header');
        return $dataTable->renderTable();
    }

    public function DuplicatePage($id, Request $request){

        if((int)$id<1) return redirect(route('pages.index'));
        $menu =  $this->pageRepository->getModel()::where('id','=',$id)->firstOrFail();
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
     * @return string
     */
    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle(trans('packages/page::pages.create'));

        return $formBuilder->create(PageForm::class)->renderForm();
    }

    /**
     * @param PageRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function store(PageRequest $request, BaseHttpResponse $response)
    {
        $page = $this->pageRepository->createOrUpdate(array_merge($request->input(), [
            'user_id' => Auth::id(),
        ]));

        event(new CreatedContentEvent(PAGE_MODULE_SCREEN_NAME, $request, $page));

        return $response->setPreviousUrl(route('pages.index'))
            ->setNextUrl(route('pages.edit', $page->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    /**
     * @param Request $request
     * @param int $id
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function edit($id, FormBuilder $formBuilder, Request $request)
    {
        $page = $this->pageRepository->findOrFail($id);

        page_title()->setTitle(trans('packages/page::pages.edit') . ' "' . $page->name . '"');

        event(new BeforeEditContentEvent($request, $page));

        return $formBuilder->create(PageForm::class, ['model' => $page])->renderForm();
    }

    /**
     * @param $id
     * @param PageRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function update($id, PageRequest $request, BaseHttpResponse $response)
    {
        $page = $this->pageRepository->findOrFail($id);
        $page->fill($request->input());

        $page = $this->pageRepository->createOrUpdate($page);

        event(new UpdatedContentEvent(PAGE_MODULE_SCREEN_NAME, $request, $page));

        return $response
            ->setPreviousUrl(route('pages.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    /**
     * @param Request $request
     * @param int $id
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function destroy(Request $request, $id, BaseHttpResponse $response)
    {
        try {
            $page = $this->pageRepository->findOrFail($id);
            $this->pageRepository->delete($page);

            event(new DeletedContentEvent(PAGE_MODULE_SCREEN_NAME, $request, $page));

            return $response->setMessage(trans('packages/page::pages.deleted'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     * @throws Exception
     */
    public function deletes(Request $request, BaseHttpResponse $response)
    {
        return $this->executeDeleteItems($request, $response, $this->pageRepository, PAGE_MODULE_SCREEN_NAME);
    }
}
