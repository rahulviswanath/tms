<?php
namespace App\Permissions;

use App\Permission;
use App\Role;
use Illuminate\Support\Facades\Cache;

trait HasPermissionsTrait {

    public function roles() {
        return $this->belongsToMany(Role::class,'user_roles')->whereNull('user_roles.deleted_at');

    }


    public function permissions() {
        return $this->belongsToMany(Permission::class,'users_permissions')->whereNull('users_permissions.deleted_at');

    }

    public function hasRole( ... $roles ) {
        foreach ($roles as $role) {
            $roles = $this->getRoles();
            if ($roles->contains('name', $role)) {
                return true;
            }
        }
        return false;
    }

    public function hasPermissionThroughRole($permission) {
       
        $permissionRoles = $permission->roles()->get();
        

        $roles = $this->getRoles();

        foreach ($permissionRoles as $role){
            if($roles->contains($role)) {
                return true;
            }
        }
        return false;
    }

    public function hasPermissionTo($permission) {
        return $this->hasPermissionThroughRole($permission) || $this->hasPermission($permission);
    }

    protected function hasPermission($permission) {

        $that = $this;
        $hasPermission =  $that->permissions->where('slug', $permission->slug)->count();

        return (bool) $hasPermission;
    }

    protected function getRoles() {
        $that = $this;
        return Cache::rememberForever('roles'.auth()->user()->id, function() use($that) {
            return $that->roles()->get();
        });
    }

}