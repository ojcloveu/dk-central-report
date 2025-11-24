<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BetRequest;
use App\Models\Bet;
use App\Models\Channel;
use App\Models\Master;
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
    use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(Bet::class);
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

        // Add date range filter for trandate with today as default
        CRUD::filter('trandate')
            ->type('date_range')
            ->label('Transaction Date')
            ->default(date('Y-m-d') . ' - ' . date('Y-m-d'))
            ->whenActive(function ($value) {
                $dates = explode(' - ', $value);
                // if (count($dates) == 2) {
                    CRUD::addClause('whereDate', 'trandate', '>=', $dates[0]);
                    CRUD::addClause('whereDate', 'trandate', '<=', $dates[1]);
                // }
            });

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

    // -----------------------------------------------------------------
    //  BACKPACK FETCH OPERATION METHODS
    // -----------------------------------------------------------------
    /**
     * Fetches masters for selection.
     */
    public function fetchMasters()
    {
        return $this->fetch([
            'model' => Master::class,
            'searchable_attributes' => ['name'],
            'paginate' => 5,
            'select' => ['name'],
            'query' => function ($query) {
                return $query->where('is_active', true)
                    ->orderBy('name', 'asc');
            }
        ]);
    }

    /**
     * Fetches channels for selection.
     */
    public function fetchChannels()
    {
        return $this->fetch([
            'model' => Channel::class,
            'searchable_attributes' => ['channel_name'],
            'paginate' => 5,
            'select' => ['channel_name'],
            'query' => function ($query) {
                return $query->where('is_active', true)
                    ->orderBy('channel_name', 'asc');
            },
        ]);
    }

    /**
     * Fetches accounts for selection.
     */
    public function fetchAccounts()
    {
        return $this->fetch([
            'model' => Bet::class,
            'searchable_attributes' => ['account'],
            'paginate' => 5,
            'select' => ['account'],
            'query' => function ($query) {
                return $query->select('account')
                    ->groupBy('account')
                    ->orderBy('account', 'asc');
            },
        ]);
    }
}
