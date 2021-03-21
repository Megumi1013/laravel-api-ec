<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ItemReviewCollectionResource extends ResourceCollection
{
    private $pagination;

    public function __construct($resource)
    {
        if (method_exists($resource, 'previousPageUrl')) {
            $this->pagination = [
                'prevPage' => (is_null($resource->previousPageUrl()) ? 0 : $resource->currentPage() - 1),
                'currentPage' => (int) $resource->currentPage(),
                'nextPage' => (is_null($resource->nextPageUrl()) ? 0 : $resource->currentPage() + 1),
                'currentItemsTotal' => (int) $resource->count(),
                'itemsTotal' => (int) $resource->total(),
                'pagesTotal' => (int) $resource->lastPage(),
                'perPage' => (int) $resource->perPage(),
                'from' => (int) $resource->firstItem(),
                'to' => (int) $resource->lastItem(),
            ];

            $resource = $resource->getCollection();
        }

        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $meta = $this->pagination;
        $meta['orderBy'] =  ($request->orderBy ?: 'created_at');
        $meta['orderDirection'] = ($request->orderDirection ?: 'desc');

        return [
            'items' => $this->collection->transform(function ($data) {
                return [
                    'id' => $data->id,
                    // 'created_at' => $data->created_at ? $data->created_at->toDateTimeString() : null,
                    // 'updated_at' => $data->updated_at ? $data->updated_at->toDateTimeString() : null,
                ];
            }),
            'meta' => $meta,
        ];
    }
}
