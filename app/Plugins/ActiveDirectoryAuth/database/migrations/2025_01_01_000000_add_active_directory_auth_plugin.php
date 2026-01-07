<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
    public function up()
    {
        $exists = DB::table('plugins')->where('name', 'ActiveDirectoryAuth')->exists();
        if (!$exists) {
            DB::table('plugins')->insert([
                'name' => 'ActiveDirectoryAuth',
                'path' => 'ActiveDirectoryAuth',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('plugins')->where('name', 'ActiveDirectoryAuth')->delete();
    }
};
