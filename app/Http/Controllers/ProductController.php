<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductCollectionResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductReviewCollectionResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $data = $request->all();

        $rules = [
            'page' => 'required|numeric',
            'per_page' => 'required|numeric',
            'order_by' => 'required|string',
            'order_direction' => 'required|in:asc,desc',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $this->createErrorResponse(400, 'The request data was invalid', 'request_data_invalid', $validator->errors());
        }

        $items = Product::query()->orderBy($data['order_by'], $data['order_direction']);

        // Some other conditional queries

        $data = new ProductCollectionResource($items->paginate($data['per_page']));

        return $this->createSuccessResponse(200, 'Successfully retrieved Items', 'items_index_success', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'is_disabled' => 'required|boolean',
            'price' => 'required|numeric',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $this->createErrorResponse(400, 'The request data was invalid', 'request_data_invalid', $validator->errors());
        }

        $item = new Product;

        $item->name = $data['name'];
        $item->description = $data['description'];
        $item->price = $data['price'];
        $item->is_disabled = $data['is_disabled'];

        if ($item->save()) {

            $data = new ProductResource($item);
            return $this->createSuccessResponse(200, 'Successfully saved Item', 'item_store_success', $data);

        }

        return $this->createErrorResponse(500, 'Item could not be saved', 'item_store_failure');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $item = Product::find($id);

        if ($item) {

            $data = new ProductResource($item);
            return $this->createSuccessResponse(200, 'Successfully retrieved Items', 'items_show_success', $data);

        }

        return $this->createErrorResponse(404, 'The Item was not found', 'item_not_found');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();

        $rules = [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:2000',
            'is_disabled' => 'sometimes|boolean',
            'price' => 'sometimes|numeric',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $this->createErrorResponse(400, 'The request data was invalid', 'request_data_invalid', $validator->errors());
        }

        $item = Product::find($id);

        if ($item) {

            // @todo What if request is empty?

            if (isset($data['name'])) {
                $item->name = $data['name'];
            }

            if (isset($data['description'])) {
                $item->description = $data['description'];
            }

            if (isset($data['price'])) {
                $item->price = $data['price'];
            }

            if (isset($data['is_disabled'])) {
                $item->is_disabled = $data['is_disabled'];
            }

            if ($item->save()) {

                $data = new ProductResource($item);
                return $this->createSuccessResponse(200, 'Successfully updated Item', 'item_update_success', $data);

            }

            return $this->createErrorResponse(500, 'Item could not be updated', 'item_update_failure');

        }

        return $this->createErrorResponse(404, 'The Item was not found', 'item_not_found');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $item = Product::find($id);

        if ($item) {

            $itemReviews = $item->reviews;

            $itemReviewsToDelete = count($itemReviews);
            $itemReviewsDeleted = 0;

            // Delete reviews
            foreach ($item->reviews as $itemReview) {

                if ($itemReview->delete()) {
                    $itemReviewsDeleted++;
                }

            }

            if ($itemReviewsToDelete === $itemReviewsDeleted && $item->delete()) {
                return $this->createSuccessResponse(200, 'Successfully deleted Item', 'item_destroy_success');
            }

            return $this->createErrorResponse(500, 'Item could not be deleted', 'item_destroy_failure');

        }

        return $this->createErrorResponse(404, 'The Item was not found', 'item_not_found');
    }


}
