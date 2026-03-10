<?php
namespace App\Support;

use App\Models\Tag;
use Illuminate\Support\Arr;

trait HasTags
{

    public function tags()
    {
        // جدول pivot مفترض: taggables
        return $this->morphToMany(Tag::class, 'taggable', 'taggables', 'taggable_id', 'tag_id');
    }

    public function syncTags(array $items): void
    {
        $ids = collect($items)
            ->map(fn($t) => $this->resolveTagIdOrNull($t))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->tags()->sync($ids);
    }

    /**
     * Attach (اضافة بدون ازالة الموجودين)
     */
    public function attachTags(array $items): void
    {
        $ids = collect($items)
            ->map(fn($t) => $this->resolveTagIdOrNull($t))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!empty($ids)) {
            $this->tags()->syncWithoutDetaching($ids);
        }
    }

    /**
     * Detach محدّد
     */
    public function detachTags(array $items): void
    {
        $ids = collect($items)
            ->map(fn($t) => $this->resolveTagIdOrNull($t))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!empty($ids)) {
            $this->tags()->detach($ids);
        }
    }

    /**
     * حلّل عنصر تاغ لإرجاع id (أو أنشئ التاغ وأرجع id)
     */
    protected function resolveTagIdOrNull($item): ?int
    {

        if (is_int($item) || (is_string($item) && ctype_digit($item))) {
            return (int) $item;
        }


        if (is_array($item)) {
            if (!empty($item['id'])) {
                return (int) $item['id'];
            }
            if (array_key_exists('name', $item)) {
                return $this->findOrCreateTagByName($item['name'])->id;
            }

            $allString = collect($item)->every(fn($v) => is_string($v) || is_null($v));
            if ($allString) {
                return $this->findOrCreateTagByName($item)->id;
            }
        }


        if (is_string($item) && trim($item) !== '') {
            return $this->findOrCreateTagByName($item)->id;
        }

        return null;
    }

    /**
     * ابحث عن تاغ حسب الاسم في الترجمات المتاحة، أو انشئ واحداً.
     * $name قد يكون string أو array ترجمي.
     */
    protected function findOrCreateTagByName($name)
    {

        $locales = config('app.locales', ['ar','en']);

        if (is_string($name)) {

            foreach ($locales as $loc) {
                $tag = Tag::where("name->{$loc}", $name)->first();
                if ($tag) return $tag;
            }

            $payload = [$locales[0] => $name];
            return Tag::create(['name' => $payload]);
        }

        if (is_array($name)) {

            foreach ($name as $loc => $val) {
                if (! $val) continue;
                $tag = Tag::where("name->{$loc}", $val)->first();
                if ($tag) return $tag;
            }

            return Tag::create(['name' => $name]);
        }


        throw new \InvalidArgumentException('Invalid tag name provided');
    }
}
