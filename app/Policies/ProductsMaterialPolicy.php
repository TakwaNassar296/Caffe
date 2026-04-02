<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\ProductsMaterial;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductsMaterialPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the admin can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('view_any_products::material');
    }

    /**
     * Determine whether the admin can view the model.
     */
    public function view(Admin $admin, ProductsMaterial $productsMaterial): bool
    {
        return $admin->can('view_products::material');
    }

    /**
     * Determine whether the admin can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->can('create_products::material');
    }

    /**
     * Determine whether the admin can update the model.
     */
    public function update(Admin $admin, ProductsMaterial $productsMaterial): bool
    {
        return $admin->can('update_products::material');
    }

    /**
     * Determine whether the admin can delete the model.
     */
    public function delete(Admin $admin, ProductsMaterial $productsMaterial): bool
    {
        return $admin->can('delete_products::material');
    }

    /**
     * Determine whether the admin can bulk delete.
     */
    public function deleteAny(Admin $admin): bool
    {
        return $admin->can('delete_any_products::material');
    }

    /**
     * Determine whether the admin can permanently delete.
     */
    public function forceDelete(Admin $admin, ProductsMaterial $productsMaterial): bool
    {
        return $admin->can('force_delete_products::material');
    }

    /**
     * Determine whether the admin can permanently bulk delete.
     */
    public function forceDeleteAny(Admin $admin): bool
    {
        return $admin->can('force_delete_any_products::material');
    }

    /**
     * Determine whether the admin can restore.
     */
    public function restore(Admin $admin, ProductsMaterial $productsMaterial): bool
    {
        return $admin->can('restore_products::material');
    }

    /**
     * Determine whether the admin can bulk restore.
     */
    public function restoreAny(Admin $admin): bool
    {
        return $admin->can('restore_any_products::material');
    }

    /**
     * Determine whether the admin can replicate.
     */
    public function replicate(Admin $admin, ProductsMaterial $productsMaterial): bool
    {
        return $admin->can('replicate_products::material');
    }

    /**
     * Determine whether the admin can reorder.
     */
    public function reorder(Admin $admin): bool
    {
        return $admin->can('reorder_products::material');
    }
}
