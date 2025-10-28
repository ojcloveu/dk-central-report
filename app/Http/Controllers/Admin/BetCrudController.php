<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BetRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class BetCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BetCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Bet::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/bet');
        CRUD::setEntityNameStrings('bet', 'bets');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('account')->type('text');
        CRUD::column('channel')->type('text');
        CRUD::column('trandate')->type('date');
        CRUD::column('master')->type('text');
        CRUD::column('min')->type('number');
        CRUD::column('max')->type('number');
        CRUD::column('count')->type('number');
        CRUD::column('turnover')->type('number')->prefix('$');
        CRUD::column('winlose')->type('number')->prefix('$');
        CRUD::column('lp')->type('number')->suffix('%');

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(BetRequest::class);
        
        CRUD::field('account')->type('text')->validationRules('required|string|max:255');
        CRUD::field('channel')->type('text')->validationRules('required|string|max:255');
        CRUD::field('trandate')->type('date')->validationRules('required|date');
        CRUD::field('master')->type('text')->validationRules('required|string|max:255');
        CRUD::field('min')->type('number')->validationRules('required|integer|min:0');
        CRUD::field('max')->type('number')->validationRules('required|integer|min:0');
        CRUD::field('count')->type('number')->validationRules('required|integer|min:0');
        CRUD::field('turnover')->type('number')->attributes(['step' => '0.01'])->validationRules('required|numeric|min:0');
        CRUD::field('winlose')->type('number')->attributes(['step' => '0.01'])->validationRules('required|numeric');
        CRUD::field('lp')->type('number')->attributes(['step' => '0.01', 'min' => '0', 'max' => '100'])->validationRules('required|numeric|min:0|max:100');

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
