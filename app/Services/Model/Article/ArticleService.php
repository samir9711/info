<?php

namespace App\Services\Model\Article;

use App\Services\Basic\BasicCrudService;
use App\Services\Basic\ModelColumnsService;
use App\Models\Article;
use App\Http\Resources\Model\ArticleResource;
use App\Models\ArticleSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Basic\BasicRequest;
use Illuminate\Support\Str;

class ArticleService extends BasicCrudService
{
    /**
     * Override to set up modelColumnsService and resource.
     */
    protected function setVariables(): void
    {
        $this->modelColumnsService = ModelColumnsService::getServiceFor(
            $this->model = Article::class
        );

        $this->resource = ArticleResource::class;
        $this->relations = ['sections', 'category'];
    }


    public function create(BasicRequest $request): mixed
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $article = $this->model::create([
                'title'      => $data['title'] ?? [],
                'intro'      => $data['intro'] ?? null,
                'conclusion' => $data['conclusion'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'is_important' => $data['is_important'] ?? false,
                'image'      => isset($data['image']) && is_string($data['image']) && $data['image'] !== '' ? $data['image'] : null,
            ]);

            $sections = $data['sections'] ?? [];
            foreach (array_values($sections) as $index => $s) {
                $attrs = [
                    'title'      => $s['title'] ?? null,
                    'body'       => $s['body'] ?? null,
                    'conclusion' => $s['conclusion'] ?? null,
                    'position'   => $s['position'] ?? $index,
                    'image'      => isset($s['image']) && is_string($s['image']) && $s['image'] !== '' ? $s['image'] : null,
                ];

                $article->sections()->create($attrs);
            }

            $article->load('sections');
            return $this->resource::make($article);
        });
    }


    public function update(BasicRequest $request): mixed
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $request) {
            $article = $this->model::with('sections')->findOrFail($request->id);

            $article->update([
                'title'      => $data['title'] ?? $article->title,
                'intro'      => array_key_exists('intro', $data) ? $data['intro'] : $article->intro,
                'conclusion' => array_key_exists('conclusion', $data) ? $data['conclusion'] : $article->conclusion,
                'category_id' => array_key_exists('category_id', $data) ? $data['category_id'] : $article->category_id,
                'is_important' => array_key_exists('is_important', $data) ? $data['is_important'] : $article->is_important,
                'image'      => isset($data['image']) && is_string($data['image']) && $data['image'] !== '' ? $data['image'] : null,
            ]);

            $incoming = $data['sections'] ?? [];
            $keptIds = [];

            foreach (array_values($incoming) as $s) {
                if (!empty($s['id'])) {

                    $section = $article->sections->firstWhere('id', $s['id']);
                    if (!$section) {

                        continue;
                    }

                    $section->title      = $s['title'] ?? $section->title;
                    $section->body       = $s['body'] ?? $section->body;
                    $section->conclusion = $s['conclusion'] ?? $section->conclusion;


                    if (!empty($s['remove_image'])) {
                        $section->image = null;
                    } elseif (array_key_exists('image', $s) && is_string($s['image'])) {

                        $section->image = $s['image'] !== '' ? $s['image'] : null;
                    }

                    $section->save();
                    $keptIds[] = $section->id;
                } else {

                    $attrs = [
                        'title'      => $s['title'] ?? null,
                        'body'       => $s['body'] ?? null,
                        'conclusion' => $s['conclusion'] ?? null,
                        'image'      => isset($s['image']) && is_string($s['image']) && $s['image'] !== '' ? $s['image'] : null,
                    ];

                    $new = $article->sections()->create($attrs);
                    $keptIds[] = $new->id;
                }
            }


            $existingIds = $article->sections()->pluck('id')->toArray();
            $toDelete = array_diff($existingIds, $keptIds);
            if (!empty($toDelete)) {
                foreach ($toDelete as $delId) {
                    $sec = ArticleSection::find($delId);
                    if ($sec) {
                        $sec->forceDelete();
                    }
                }
            }

            $article->load('sections');
            return $this->resource::make($article);
        });
    }
}
