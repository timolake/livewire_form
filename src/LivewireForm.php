<?php

namespace timolake\livewireForms;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Livewire\Component;

abstract class LivewireForm extends Component
{
    public ?string $modelClass = null;

    public ?Model $model = null;

    public bool $showDeleteModal = false;

    public bool $showRestoreModal = false;

    public string $idField = 'id';

    //----------------------------------------------------
    // url params
    //----------------------------------------------------
    public $search;

    public $sortField;

    public $sortDir;

    public $paginationPage;

    public $redirectMessage;

    abstract public function model(): string;

    abstract public function rules(): array;

    abstract public function render(): View;

    abstract public function saveRelations(): void;

    public function mount(Request $request, $id = null)
    {
        $this->idField = ((new ($this->model()))->getKeyName());
        $this->modelClass = $this->model();

        $this->model = $id == null
            ? new $this->modelClass
            : $this->modelClass::find($id);

        $this->rules = $this->rules();
        $this->search = $request->search ?? null;
        $this->sortField = $request->sortField ?? null;
        $this->sortDir = $request->sortDir ?? null;
        $this->paginationPage = $request->paginationPage ?? null;
        $this->showInvoiced = $request->showInvoiced ?? false;
        $this->showItemsToApprove = $request->showItemsToApprove ?? false;
    }

    //----------------------------------------------------
    // crud
    //----------------------------------------------------

    public function save()
    {
        $this->validate();

        $this->beforeSave();
        $this->model->save();
        $this->saveRelations();
        $this->afterSave();
        $this->redirectToIndex();
    }

    public function delete()
    {
        if (! $this->hasId()) {
            throw new \Exception("cannot delete without id for model $this->modelClass ");
        }

        $this->beforeDelete();
        $this->model->delete();

        $this->redirectToIndex();
    }

    public function restore()
    {
        if (! $this->hasId()) {
            throw new \Exception("cannot restore without id for model $this->modelClass ");
        }

        $this->model->restore();

        $this->redirectToIndex();
    }

    //----------------------------------------------------
    // redirect
    //----------------------------------------------------

    abstract public function getRedirectRoute(): string;

    public function getRedirectParams(): array
    {
        $params = [];

        //check if model uses softdeletes
        if (method_exists($this->model, 'trashed')) {
            //check if model is trashed
            if ($this->model->trashed()) {
                $params['trashed'] = true;
            }
        }

        if ($this->search) {
            $params['search'] = $this->search;
        }

        if ($this->sortField) {
            $params['sortField'] = $this->sortField;
        }

        if ($this->sortDir) {
            $params['sortDir'] = $this->sortDir;
        }

        if ($this->paginationPage) {
            $params['paginationPage'] = $this->paginationPage;
        }

        return $params;
    }

    protected function redirectToIndex()
    {
        return redirect(self::addParamsToUrl($this->getRedirectRoute(), $this->getRedirectParams()))->with('status', $this->redirectMessage);
    }

    private static function addParamsToUrl(string $url, array $params): string
    {
        if (empty($params)) {
            return $url;
        }

        foreach ($params as $key => $value) {
            $url .= ! Str::contains($url, '?')
                ? '?'
                : '&';

            $url .= $key.'='.$value;
        }

        return $url;
    }

    //----------------------------------------------------
    // helper
    //----------------------------------------------------
    public function beforeSave()
    {
    }

    public function afterSave()
    {
    }

    public function hasId()
    {
        $idfield = $this->idField;

        return isset($this->model->$idfield);
    }

    public function getId()
    {
        $idfield = $this->idField;

        return $this->model->$idfield;
    }

    public function beforeDelete()
    {
    }
}
