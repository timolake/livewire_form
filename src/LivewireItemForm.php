<?php

namespace timolake\livewireForms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

abstract class LivewireItemForm extends LivewireForm
{
    public string $itemClass;

    public string $itemidField;

    public array $items;

    public null|array|Model $selectedItem = null;

    public ?int $selectedItemKey = null;

    public bool $showEditItemModal = false;

    abstract public function itemRelationshipName(): string;

    abstract public function itemRules(): array;

    abstract public function itemValidationAttributes(): array;

    public function mount(Request $request, $id = null)
    {
        parent::mount($request, $id);
        $this->itemClass = $this->getItemClass();
        $this->itemidField = $this->getItemIdField();
        $this->items = $this->initItems();
        $this->initSelectedItem();
    }

    public function initItems(): array
    {
        $relationshipName = $this->itemRelationshipName();
        $collection = $this->model->$relationshipName;

        return $collection->toArray();
    }

    //----------------------------------------------------
    // crud items
    //----------------------------------------------------
    public function saveItem()
    {

        $this->validate($this->itemRules(), [], $this->itemValidationAttributes(), $this->selectedItem);
        //create or update item in form attribute
        if ($this->selectedItemKey === null) {
            $this->items[] = $this->selectedItem;
        } else {
            $this->items[$this->selectedItemKey] = $this->selectedItem;
        }
        $this->initSelectedItem();
        $this->closeEditModal();
    }

    public function removeItem(int $selectedKey)
    {
        //delete item in form attribute
        Arr::forget($this->items, $selectedKey);
    }

    public function addNewItem()
    {
        $this->selectedItemKey = null;
        $this->initSelectedItem();
        $this->openEditModal();
    }

    public function setSelectedItem(int $key)
    {
        $this->selectedItemKey = $key;
        $this->initSelectedItem();
        $this->openEditModal();
    }

    public function initSelectedItem(): void
    {
        if ($this->selectedItemKey !== null) {
            $this->selectedItem = $this->items[$this->selectedItemKey];
        } else {
            $this->selectedItem = (new $this->itemClass)->toArray();
            $this->selectedItem[$this->itemidField] = $this->model->id;
        }
    }

    public function getItemIdField(): string
    {
        $itemRelationship = $this->getRelationship($this->itemRelationshipName());
        [$parentTable, $parentId, $subTable, $subId] = $this->getRelationshipKeys($itemRelationship);

        return $subId;
    }

    public function getItemClass(): string
    {
        $itemRelationship = $this->getRelationship($this->itemRelationshipName());

        return $itemRelationship->getRelated()::class;
    }

    //----------------------------------------------------
    // relationships
    //----------------------------------------------------

    public function saveRelations(): void
    {
        $arrItemIds = [];
        foreach ($this->items as $item) {
            if (isset($item['id'])) {
                $itemModel = $this->itemClass::findOrFail($item['id']);
                $itemModel->update($item);
                $arrItemIds[] = $item['id'];
            } else {
                $item[$this->itemidField] = $this->model->id;
                $newItemModel = $this->itemClass::create($item);
                $arrItemIds[] = $newItemModel->id;
            }
        }

        $this->itemClass::where($this->itemidField, $this->model->id)
            ->whereNotIn('id', $arrItemIds)
            ->delete();
    }

    //----------------------------------------------------
    // helpers
    //----------------------------------------------------
    public function getRelationship(string $name, string $parent = null): Relation
    {
        if (isset($parent)) {
            $parentRelation = app($this->model())->$parent();

            return $parentRelation->getRelated()->$name();
        }

        return app($this->model())->$name();
    }

    public function getRelationshipKeys(Relation $relationship): array
    {
        $parentTable = null;
        $parentId = null;
        $subTable = null;
        $subId = null;

        $fullForeignKey = $relationship->getQualifiedForeignKeyName();

        if ($relationship instanceof HasOne) {
            [$parentTable, $parentId] = explode('.', $fullForeignKey);

            $fullOwnerKey = $relationship->getQualifiedForeignKeyName();
            [$subTable, $subId] = explode('.', $fullOwnerKey);
        }

        if ($relationship instanceof BelongsTo) {
            [$parentTable, $parentId] = explode('.', $fullForeignKey);

            $fullOwnerKey = $relationship->getQualifiedOwnerKeyName();
            [$subTable, $subId] = explode('.', $fullOwnerKey);
        }

        if ($relationship instanceof HasMany) {
            [$subTable, $subId] = explode('.', $fullForeignKey);

            $parentId = $relationship->getLocalKeyName();
        }

        return [$parentTable, $parentId, $subTable, $subId];
    }

    //----------------------------------------------------
    // modals
    //----------------------------------------------------
    public function openEditModal()
    {
        $this->showEditItemModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditItemModal = false;

    }
}
