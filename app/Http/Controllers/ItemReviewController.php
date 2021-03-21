<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Http\Resources\ItemReviewCollectionResource;
use App\Models\Item;
use App\Models\ItemReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param int $itemId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($itemId)
    {
        $item = Item::find($itemId)->with('reviews');

        if ($item) {

            $data = new ItemReviewCollectionResource($item->reviews);
            return $this->createSuccessResponse(200, 'Successfully retrieved Item Reviews', 'items_reviews_index_success', $data);

        }

        return $this->createErrorResponse(404, 'The Item was not found', 'item_not_found');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $rules = [
            'content' => 'required|string|max:2000',
            'stars' => 'required|numeric',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $this->createErrorResponse(400, 'The request data was invalid', 'request_data_invalid', $validator->errors());
        }

        $review  = new ItemReview;

        $review->name = $data['name'];
        $review->description = $data['description'];
        $review->price = $data['price'];
        $review->is_disabled = $data['is_disabled'];

        if ($review->save()) {

            $data = new ItemReviewCollectionResource($review);
            return $this->createSuccessResponse(200, 'Successfully saved Item Review', 'item_review_store_success', $data);

        }

        return $this->createErrorResponse(500, 'Item Review could not be saved', 'item_review_store_failure');
    }

    /**
     * Display the specified resource.
     *
     * @param int $itemId
     * @param int $reviewId
     * @return \Illuminate\Http\Response
     */
    public function show($itemId, $reviewId)
    {
        $item = Item::find($itemId)->with('reviews');

        if ($item) {

            $data = new ItemReviewCollectionResource($item);
            return $this->createSuccessResponse(200, 'Successfully retrieved Item Reviews', 'item_reviews_index_success', $data);

        }

        return $this->createErrorResponse(404, 'The Item review was not found', 'item_review_not_found');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $itemId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $itemId)
    {
        $data = $request->all();

        $rules = [
            'content' => 'required|string|max:2000',
            'stars' => 'required|numeric',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $this->createErrorResponse(400, 'The request data was invalid', 'request_data_invalid', $validator->errors());
        }

        $item = Item::find($itemId)->with('reviews');

        if ($item) {

            $item->content = $data['content'];
            $item->stars = $data['stars'];

            if ($item->save()) {

                $data = new ItemReviewCollectionResource($item);
                return $this->createSuccessResponse(200, 'Successfully updated Item Review', 'item_review_update_success', $data);

            }

            return $this->createErrorResponse(500, 'Item Review could not be saved', 'item_review_store_failure');

        }

        return $this->createErrorResponse(404, 'The Item review was not found', 'item_review_not_found');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $itemId
     * @return \Illuminate\Http\Response
     */
    public function destroy($itemId)
    {
        $item = Item::find($itemId)->with('reviews');

        if ($item) {

            if ($item->delete()) {
                return $this->createSuccessResponse(200, 'Successfully deleted Item Review', 'item_review_destroy_success');
            }

            return $this->createErrorResponse(500, 'Item review could not be deleted', 'item_review_destroy_failure');

        }

        return $this->createErrorResponse(404, 'The Item review was not found', 'item_review_not_found');

    }
}
