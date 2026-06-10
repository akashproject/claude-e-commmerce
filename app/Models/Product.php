<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'base_image', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Attributes actually used by this product, derived from its variants'
     * attribute values. Each Attribute is hydrated with a `values` relation
     * containing only the values present on this product's variants — exactly
     * what the front-end variant selector needs.
     *
     * Requires the product to be loaded with `variants.attributeValues.attribute`.
     *
     * @return Collection<int, Attribute>
     */
    public function usedAttributes(): Collection
    {
        return $this->variants
            ->flatMap(fn (ProductVariant $v) => $v->attributeValues)
            ->groupBy('attribute_id')
            ->map(function (Collection $values) {
                /** @var Attribute $attribute */
                $attribute = $values->first()->attribute;
                $attribute->setRelation('values', $values->unique('id')->values());

                return $attribute;
            })
            ->values();
    }

    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists')->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
