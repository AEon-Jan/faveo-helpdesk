<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActiveDirectorySettingsTable extends Migration
{
    public function up()
    {
        Schema::create('active_directory_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('enabled')->default(false);
            $table->string('host')->nullable();
            $table->string('base_dn')->nullable();
            $table->string('bind_dn')->nullable();
            $table->text('bind_password')->nullable();
            $table->unsignedInteger('port')->default(389);
            $table->string('encryption')->nullable();
            $table->boolean('tls_verify')->default(true);
            $table->string('ca_cert')->nullable();
            $table->string('login_attribute')->default('sAMAccountName');
            $table->string('user_filter')->default('(objectClass=user)');
            $table->string('match_field')->default('email');
            $table->boolean('auto_provision')->default(false);
            $table->boolean('sync_attributes')->default(false);
            $table->string('default_role')->default('user');
            $table->string('map_user_name')->nullable();
            $table->string('map_email')->default('mail');
            $table->string('map_first_name')->default('givenName');
            $table->string('map_last_name')->default('sn');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('active_directory_settings');
    }
}
