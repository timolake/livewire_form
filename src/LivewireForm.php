<?php

namespace timolake\livewireForms;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Livewire\Component;
use timolake\livewireForms\Traits\ConvertEmptyStringsToNull;
use Illuminate\Support\Facades\DB;

abstract class LivewireForm extends Component
{

    use ConvertEmptyStringsToNull;

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

    public $sessionId;

    //----------------------------------------------------
    // functions
    //----------------------------------------------------

    abstract public function model(): string;

    abstract public function rules(): array;

    abstract public function render(): View;

    abstract public function saveRelations(): void;

    public function mount(Request $request, $id = null)
    {
        $this->modelClass = $this->model();
        $tempModel = (new ($this->modelClass));
        $this->idField = $tempModel->getKeyName();

        $isSoftDeleting = in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($tempModel));
        $this->model = $id == null
            ? new $this->modelClass
            : ($isSoftDeleting ? $this->modelClass::withTrashed()->findOrFail($id) : $this->modelClass::findOrFail($id));

        $this->rules = $this->rules();
        $this->search = $request->search ?? null;
        $this->sortField = $request->sortField ?? null;
        $this->sortDir = $request->sortDir ?? null;
        $this->paginationPage = $request->paginationPage ?? null;
        $this->sessionId = $request->sessionId ?? null;
    }

    //----------------------------------------------------
    // crud
    //----------------------------------------------------

    public function save()
    {
        $this->validate();
        DB::transaction(function () {
            $this->beforeSave();
            $this->model->save();
            $this->afterSave();
            $this->saveRelations();
        });

        $this->redirectToIndex();
    }

    public function delete()
    {
        if (!$this->hasId()) {
            throw new \Exception("cannot delete without id for model $this->modelClass ");
        }

        $this->beforeDelete();
        $this->model->delete();

        $this->redirectToIndex();
    }

    public function restore()
    {
        if (!$this->hasId()) {
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

        if ($this->sessionId) {
            $params['sessionId'] = $this->sessionId;
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
            $url .= !Str::contains($url, '?')
                ? '?'
                : '&';

            $url .= $key.'='.$value;
        }

        return $url;
    }

    //----------------------------------------------------
    // helper
    //----------------------------------------------------
    public function beforeSave(): void
    {
    }

    public function afterSave(): void
    {
    }

    public function hasId(): bool
    {
        $idfield = $this->idField;

        return isset($this->model->$idfield);
    }
    
    public function beforeDelete(): void
    {
    }

    //----------------------------------------------------
    // modals
    //---------------------------------------------------
    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
    }

    public function openDeleteModal(): void
    {
        $this->showDeleteModal = true;
    }

    public function closeRestoreModal(): void
    {
        $this->showRestoreModal = false;
    }

    public function openRestoreModal(): void
    {
        $this->showRestoreModal = true;
    }
}
