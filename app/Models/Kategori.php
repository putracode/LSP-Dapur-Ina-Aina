<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama_kategori'])]
class Kategori extends Model
{
    protected $table = 'kategori';

    protected $primaryKey = 'id_kategori';

    /**
     * @return HasMany<Menu, $this>
     */
    public function menu(): HasMany
    {
        return $this->hasMany(Menu::class, 'id_kategori', 'id_kategori');
    }
}
