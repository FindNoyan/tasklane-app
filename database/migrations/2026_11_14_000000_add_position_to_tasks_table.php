<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Manual order within a column, set by drag-and-drop
            // reorder: 0 until a column is reordered, then 0..n in
            // the order the user dropped the cards. No ->after()
            // here: SQLite (used by the test suite) does not support
            // placing a column after another one.
            $table->integer('position')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
