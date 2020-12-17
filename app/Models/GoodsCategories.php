<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $title
 */
class GoodsCategories extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function goods()
    {
        return $this->belongsToMany(Goods::class, 'goods_2_goods_categories', 'goods_categories_id', 'goods_id');
    }
}
