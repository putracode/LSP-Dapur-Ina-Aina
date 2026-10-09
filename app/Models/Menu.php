<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id_kategori', 'nama_menu', 'harga', 'stok', 'foto'])]
class Menu extends Model
{
    protected $table = 'menu';

    protected $primaryKey = 'id_menu';

    /**
     * @return BelongsTo<Kategori, $this>
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    /**
     * @return HasMany<DetailTransaksi, $this>
     */
    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'id_menu', 'id_menu');
    }

    public function isOutOfStock(): bool
    {
        return $this->stok <= 0;
    }

    public function isLowStock(int $threshold = 5): bool
    {
        return $this->stok > 0 && $this->stok <= $threshold;
    }
}
