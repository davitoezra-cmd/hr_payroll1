<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
class Document extends Model {
     use HasFactory;
      protected $fillable = 
      [ 'name', 
      'category', 
      'file_name', 
      'file_path', 
      'file_size', '
      mime_type', 
      'description', 
      'uploaded_by', 
      ]; 
      
      
      public function uploader(): BelongsTo { 
    
      return $this->belongsTo(User::class, 'uploaded_by'); } }