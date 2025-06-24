<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    //garded none
    protected $guarded = [];
    //primary key is mac address
    protected $primaryKey = 'mac';
    public $timestamps = false;
    //table name
    protected $table = 'devices';
    //casts
    protected $casts = [
        'mac' => 'string',
        'first_found' => 'datetime',
        'last_seen' => 'datetime',
    ];

    //children
    public function children()
    {
        return $this->hasMany(Device::class, 'parent_mac', 'mac')->orderBy('parent_port')->orderBy('name');
    }
    //parent
    public function parent()
    {
        return $this->belongsTo(Device::class, 'parent_mac', 'mac');
    }

    /**
     * A recursive relationship to load all descendants.
     * This will eager load children, and their children, and so on.
     */
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

}
