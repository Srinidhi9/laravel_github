<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('author');
            $table->date('published_date')->nullable();
            $table->string('published_by')->nullable();
            $table->integer('number_of_copies_sold')->default(0)->nullable();
            $table->string('genre')->nullable();
            $table->integer('number_of_available_items')->default(1)->nullable();
            $table->string('language')->nullable();
            $table->string('edition')->nullable();
            $table->string('isbn')->unique();

            $table->enum('status', ['available', 'borrowed'])->default('available');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropColumn('updated_by');
        });
    }
};
