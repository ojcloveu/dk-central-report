<?php

namespace App\Traits\Utils;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * CrudPermissionTrait: use Permissions to configure Backpack
 */
trait CrudPermissionTrait
{
    // the operations defined for CRUD controller
    public array $operations = ['list', 'show', 'create', 'update', 'delete'];

    /**
     * set CRUD access using spatie Permissions defined for logged in user
     *
     * @return void
     */
    public function setAccessUsingPermissions(string $name = '')
    {
        // default
        $this->crud->denyAccess($this->operations);

        // get context
        if ($name) {
            $table = str_replace(' ', '', ucwords($name));
        } else {
            $table = CRUD::getModel()->getTable();
        }

        $user = request()->user();

        // double check if no authenticated user
        if (!$user) {
            return; // allow nothing
        }

        // enable operations depending on permission
        $permissions = [
            'list' => 'canView' . ucfirst($table),
            'create' => 'canCreate' . ucfirst($table),
            'show' => 'canPreview' . ucfirst($table),
            'update' => 'canEdit' . ucfirst($table),
            'delete' => 'canDelete' . ucfirst($table),
        ];

        foreach ($permissions as $permission => $method) {
            if (method_exists($user, $method) && $user->$method()) {
                CRUD::allowAccess($permission);
            }
        }
    }
}
