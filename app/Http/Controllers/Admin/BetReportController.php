<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

/**
 * Class ReportController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BetReportController extends Controller
{
    public function index()
    {
        return Inertia::render('BetReport', [
            'message' => 'Hello from the Laravel Backend!',
            'user' => User::find(1),
        ]);
    }
}
