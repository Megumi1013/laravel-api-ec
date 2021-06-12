<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductReviewCollectionResource extends ResourceCollection
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
                'per_page' => (int) $resource->perPage(),
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
        $meta['order_by'] =  ($request->order_by ?: 'created_at');
        $meta['order_direction'] = ($request->order_direction ?: 'desc');

        return [
            'items' => $this->collection->transform(function ($data) {
                return [
                    'id' => $data->id,
                    'content' => $data->content,
                    'stars' => $data->stars,
                     'created_at' => $data->created_at ? $data->created_at->toDateTimeString() : null,
                     'updated_at' => $data->updated_at ? $data->updated_at->toDateTimeString() : null,
                ];
            }),
            'meta' => $meta,
        ];
    }
}
