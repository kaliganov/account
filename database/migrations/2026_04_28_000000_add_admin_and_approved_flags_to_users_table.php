<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('check_number');
            $table->boolean('is_approved')->default(false)->after('is_admin');
        });

        // Делаем первого пользователя администратором и активным для первичного доступа.
        $firstUser = DB::table('users')->orderBy('id')->first();
        if ($firstUser !== null) {
            DB::table('users')
                ->where('id', $firstUser->id)
                ->update([
                    'is_admin' => true,
                    'is_approved' => true,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'is_approved']);
        });
    }
};
