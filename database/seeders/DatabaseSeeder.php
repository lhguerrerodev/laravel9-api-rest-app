<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@lhguerrerodev.com',
            'created_by' => 0,
            'password' => bcrypt('superadmin')
        ]);

        $super_admin_role = Role::create([
            'name' => 'super_admin', 
            'description' => 'Super admin',
            'created_by' => $user->id,
        ]);


        $user->roles()->attach($super_admin_role->id,['created_by' => $user->id]);

        $permissions = [
            [   
                'name' => 'create_user', 
                'description' => 'Create users'
            ],
            [   
                'name' => 'read_user', 
                'description' => 'Read users'
            ],
            [   
                'name' => 'update_user', 
                'description' => 'Update users'
            ],
            [   
                'name' => 'delete_user', 
                'description' => 'Delete users'
            ],
            [   
                'name' => 'create_role', 
                'description' => 'Create Roles'
            ],
            [   
                'name' => 'read_role', 
                'description' => 'Read Roles'
            ],
            [   
                'name' => 'update_role', 
                'description' => 'Update Roles'
            ],
            [   
                'name' => 'delete_role', 
                'description' => 'Delete Roles'
            ],
            [   
                'name' => 'assign_role', 
                'description' => 'Assign roles'
            ],
            [   
                'name' => 'create_permission', 
                'description' => 'Create Permissions'
            ],
            [   
                'name' => 'read_permission', 
                'description' => 'Read Permissions'
            ],
            [   
                'name' => 'update_permission', 
                'description' => 'Update Permissions'
            ],
            [   
                'name' => 'delete_permission', 
                'description' => 'Delete Permissions'
            ],
            [   
                'name' => 'assign_permission_role', 
                'description' => 'Assign Permissions to Role'
            ],
            [   
                'name' => 'assign_permission_user', 
                'description' => 'Assign Permissions to User'
            ]
        ];
      
        foreach ($permissions as $permission) {
              $permission_created = Permission::create(['name' => $permission['name'], 'description' => $permission['description'], 'created_by' => $user->id]);

              $super_admin_role->permissions()->attach($permission_created->id,['created_by' => $user->id]); 
        }
    }
}
