<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('first_name', 60);
            $table->string('last_name', 60);
            $table->string('email', 100);
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('membership_type', 20)->default('Regular');
            $table->string('status', 20)->default('Active');
            $table->date('join_date');
            $table->string('profile_slug', 80)->unique();
            $table->boolean('approved')->default(true);
            $table->timestamps();

            $table->index(['branch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
