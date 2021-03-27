<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Http\Resources\ItemReviewCollectionResource;
use App\Http\Resources\ItemReviewResource;
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
        $item = Item::query()->find($itemId);

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $itemId)
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

        $itemReview = new ItemReview();

        $itemReview->content = $data['content'];
        $itemReview->stars = $data['stars'];
        $itemReview->item_id = $itemId;

        if ($itemReview->save()) {

            $data = new ItemReviewResource($itemReview);
            return $this->createSuccessResponse(200, 'Successfully saved Item Review', 'item_review_store_success', $data);

        }

        return $this->createErrorResponse(500, 'Item Review could not be saved', 'item_review_store_failure');
    }

    /**
     * Display the specified resource.
     *
     * @param int $itemId
     * @param int $reviewId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($itemId, $reviewId)
    {
        $itemReview = ItemReview::query()->where('item_id', $itemId)->where('id', $reviewId)->first();

        if ($itemReview) {

            $data = new ItemReviewResource($itemReview);
            return $this->createSuccessResponse(200, 'Successfully retrieved Item Review', 'item_reviews_show_success', $data);

        }

        return $this->createErrorResponse(404, 'The Item review was not found', 'item_review_not_found');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $itemId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $itemId, $reviewId)
    {
        $data = $request->all();

        $rules = [
            'content' => 'sometimes|string|max:2000',
            'stars' => 'sometimes|numeric',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $this->createErrorResponse(400, 'The request data was invalid', 'request_data_invalid', $validator->errors());
        }

        $itemReview = ItemReview::query()->where('item_id', $itemId)->find($reviewId);

        if ($itemReview) {

            // @todo: Check if better way of updating model in Laravel
            // What happens is request is empty?

            if (isset($data['content'])) {
                $itemReview->content = $data['content'];
            }

            if (isset($data['stars'])) {
                $itemReview->stars = $data['stars'];
            }

            if ($itemReview->save()) {

                $data = new ItemReviewResource($itemReview);
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($itemId, $reviewId)
    {
        $itemReview = ItemReview::query()->where('item_id', $itemId)->find($reviewId);

        if ($itemReview) {

            if ($itemReview->delete()) {
                return $this->createSuccessResponse(200, 'Successfully deleted Item Review', 'item_review_destroy_success');
            }

            return $this->createErrorResponse(500, 'Item review could not be deleted', 'item_review_destroy_failure');

        }

        return $this->createErrorResponse(404, 'The Item review was not found', 'item_review_not_found');

    }
}
