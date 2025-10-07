<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('librarian_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requested_by'); // librarian who requested
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password'); // hashed
            $table->string('phone_number')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable(); // admin who approves
            $table->timestamps();

            $table->foreign('requested_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('librarian_requests');
    }
};
