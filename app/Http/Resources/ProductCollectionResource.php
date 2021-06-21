<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollectionResource extends ResourceCollection
{
    private $pagination;

    public function __construct($resource)
    {
        if (method_exists($resource, 'previous_page_url')) {
            $this->pagination = [
                'prev_page' => (is_null($resource->previousPageUrl()) ? 0 : $resource->currentPage() - 1),
                'current_page' => (int) $resource->currentPage(),
                'next_page' => (is_null($resource->nextPageUrl()) ? 0 : $resource->currentPage() + 1),
                'current_items_total' => (int) $resource->count(),
                'items_total' => (int) $resource->total(),
                'pages_total' => (int) $resource->lastPage(),
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
            'products' => $this->collection->transform(function ($data) {
                return [
                    'id' => $data->id,
                    'name' => $data->name,
                    'description' => $data->description,
                    'price' => $data->price,
                    'is_disabled' => $data->isDisabled,
                    'blah' => 'foo',
                     'created_at' => $data->created_at ? $data->created_at->toDateTimeString() : null,
                     'updated_at' => $data->updated_at ? $data->updated_at->toDateTimeString() : null,
                ];
            }),
            'meta' => $meta,
        ];
    }
}
