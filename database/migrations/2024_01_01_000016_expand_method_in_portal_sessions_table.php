<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portal_sessions', function (Blueprint $table) {
            $table->string('method', 50)->default('survey')->change();
        });
    }

    public function down(): void
    {
        Schema::table('portal_sessions', function (Blueprint $table) {
            $table->string('method', 50)->default('survey')->change();
        });
    }
};
