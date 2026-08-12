<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SystemModule extends Model
{
    protected $fillable = ['code','name','icon','route','is_base_module','display_order','is_active'];
    protected $casts = ['is_base_module'=>'boolean','is_active'=>'boolean'];
}
