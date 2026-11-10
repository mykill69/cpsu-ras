<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('original_name');
            $table->string('mime_type');
            $table->string('extension');
            $table->bigInteger('size');
            $table->string('disk');
            $table->string('path');
            $table->string('encrypted_path')->nullable();
            $table->string('checksum_sha256'); // File integrity
            $table->string('encryption_key_id')->nullable(); // For key rotation
            $table->foreignId('folder_id')->nullable()->constrained('folders')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->boolean('is_encrypted')->default(true);
            $table->integer('version')->default(1);
            $table->string('status')->default('active'); // active, archived, deleted
            $table->json('metadata')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('uuid');
            $table->index('mime_type');
            $table->index('status');
            $table->fullText(['name', 'original_name', 'description']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('files');
    }
};