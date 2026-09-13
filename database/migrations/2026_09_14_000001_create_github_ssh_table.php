<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('github_ssh', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->text('pat');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('github_ssh');
    }
};
