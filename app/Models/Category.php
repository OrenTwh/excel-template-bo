<?php

namespace App\Models;

use DateTimeInterface;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\HasTranslations;

use Helper;

class Category extends Model
{
    use HasFactory, LogsActivity, HasTranslations;

    protected $fillable = [
        'parent_id',
        'title',
        'description',
        'image',
        'thumbnail',
        'url_slug',
        'strucuture',
        'sort',
        'status',
        'type',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Define the children relationship
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Recursive function to get descendants
    public function descendants()
    {
        // First get the immediate children
        $children = $this->children()->get();
        
        // Then loop through each child and get their children (grandchildren)
        $descendants = $children->map(function ($child) {
            return $child->descendants()->add($child);
        });

        // Return all descendants
        return $descendants->flatten();
    }

    public function childrens() {
        return $this->hasMany( Categoriestructure::class, 'parent_id' )->where( 'status', 10 )->orderBy( 'level', 'ASC' );
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'products_categories')
                    ->withPivot('is_child', 'status')
                    ->withTimestamps();
    }

    public function getNumberOfProductsAttribute()
    {
        return $this->products()->count();
    }

    // Calculate the total stock quantity of related products
    public function getStockQuantityAttribute()
    {
        return $this->products()->with('productInventories')->get()->sum(function ($product) {
            return $product->productInventories->sum('quantity');
        });
    }

    // Calculate the total stock worth of related products
    public function getStockWorthAttribute()
    {
        return $this->products()->with('productInventories')->get()->sum(function ($product) {
            return $product->productInventories->sum(function ($inventory) use ($product) {
                return $inventory->quantity * $product->price;
            });
        });
    }
    
    public function getImagePathAttribute() {
        return $this->attributes['image'] ? asset( 'storage/' . $this->attributes['image'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getThumbnailPathAttribute() {
        return $this->attributes['thumbnail'] ? asset( 'storage/'.$this->attributes['thumbnail'] ) : asset( 'admin/images/placeholder.png' ) . Helper::assetVersion();
    }

    public function getEncryptedIdAttribute() {
        return Helper::encode( $this->attributes['id'] );
    }

    public $translatable = [ 'title', 'description' ];

    protected function serializeDate( DateTimeInterface $date ) {
        return $date->timezone( 'Asia/Kuala_Lumpur' )->format( 'Y-m-d H:i:s' );
    }

    protected static $logAttributes = [
        'parent_id',
        'title',
        'description',
        'image',
        'thumbnail',
        'url_slug',
        'strucuture',
        'sort',
        'status',
        'type',
    ];

    protected static $logName = 'categories';

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()->logFillable();
    }

    public function getDescriptionForEvent( string $eventName ): string {
        return "{$eventName} category";
    }
}
