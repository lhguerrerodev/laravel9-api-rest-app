<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->require();       // For MySQL 8.0 use string('name', 125);
            $table->string('description')->require(); 
            $table->enum('reg_status', [0, 99])->default(0)->require();
            $table->bigInteger('created_by')->unsigned()->require();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->require(); 
            $table->string('description')->require(); 
            $table->enum('reg_status', [0, 99])->default(0)->require();
            $table->bigInteger('created_by')->unsigned()->require();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::create('user_permissions', function (Blueprint $table) {

            $table->primary(['user_id','permission_id']);
            
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('permission_id');
            $table->bigInteger('created_by')->unsigned()->require();
            $table->timestamps();
                    
            $table->foreign('user_id')
                ->references('id')
                ->on('users')->onDelete('cascade');
           
            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');

        });

        Schema::create('user_roles', function (Blueprint $table) {

            $table->primary(['user_id','role_id']);

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->bigInteger('created_by')->unsigned()->require();
            $table->timestamps();
                    
            $table->foreign('user_id')
                ->references('id')
                ->on('users')->onDelete('cascade');
           
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
        });

        Schema::create('role_permissions', function (Blueprint $table) {

            $table->primary(['role_id','permission_id']);
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->bigInteger('created_by')->unsigned()->require();
            $table->timestamps();
                    
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')->onDelete('cascade');
           
            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
