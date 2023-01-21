<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class campaignController extends Controller
{
    //========================= Get all Campaign ==========================
    //=====================================================================
    public function index()
    {
        try {
            $campaigns = Campaign::all();
            if (!$campaigns->count()) {
                return response()->json(['error' => 'No campaign found.'], 400);
            }
            return response()->json(["data" => $campaigns]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    //=========================Get a single campaign ======================
    //=====================================================================
    public function show(Campaign $campaign)
    {
        if (is_null($campaign)) {
            return response()->json(['error' => 'No campaign found.'], 400);
        }
        try {
            return response()->json(["data" => $campaign]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
    //============== Route Controller For Storing a new Campaign===========
    //=====================================================================
    public function store(Request $request)
    {
        try {
            // Validate Field 
            $request->validate(([
                'name' => 'required|string',
                'from_date' => 'required|date',
                'to_date' => 'required|date',
                'total_budget' => 'required|numeric|min:100|max:1000',
                'daily_budget' => 'required|numeric|min:10|max:100',
                'creative_upload' => 'required|image|mimes:jpeg,png,jpg',
            ]));

            // Store image on cloudinary 
            $uploadedFileUrl = Cloudinary::upload($request->file('creative_upload')->getRealPath());
            $cloudinaryImagePublicId = $uploadedFileUrl->getPublicId();
            $cloudinaryImageUrl = $uploadedFileUrl->getSecurePath();

            Campaign::create([
                "name" => $request->name,
                "from_date" => $request->from_date,
                "to_date" => $request->to_date,
                'total_budget' => $request->total_budget,
                "daily_budget" => $request->daily_budget,
                "creative_upload" => $cloudinaryImageUrl,
                "creative_upload_id" => $cloudinaryImagePublicId
            ]);
            return response()->json(["data" => "Campign Created"]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
    //================= Update Campaign Route Controller===================
    //=====================================================================
    public function updateCampaign(Request $request, $id)
    {
        try {

            // Get cloudinary image id in the database and delete image if user include files
            $campaignId = Campaign::find($id);
            $cloudinaryImagePublicId = $campaignId->creative_upload_id;
            $cloudinaryImageUrl = $campaignId->creative_upload;
            if ($request->file('creative_upload')) {
                Cloudinary::destroy($cloudinaryImagePublicId);
                $uploadedFileUrl = Cloudinary::upload($request->file('creative_upload')->getRealPath());
                $cloudinaryImagePublicId = $uploadedFileUrl->getPublicId();
                $cloudinaryImageUrl = $uploadedFileUrl->getSecurePath();
            };

            //Perform an update to data in the database
            $campaignId->update([
                "name" => $request->name ?  $request->name : $campaignId->name,
                "from_date" => $request->from_date  ?  $request->to_date : $campaignId->from_date,
                "to_date" => $request->to_date  ?  $request->to_date : $campaignId->to_date,
                'total_budget' => $request->total_budget  ?  $request->total_budget : $campaignId->total_budget,
                "daily_budget" => $request->daily_budget  ?  $request->daily_budget : $campaignId->daily_budget,
                "creative_upload" => $cloudinaryImageUrl,
                "creative_upload_id" => $cloudinaryImagePublicId
            ]);

            return response()->json(["data" => "Campign Updated"]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
    //================== Route For Deleting a Campaign ====================
    //=====================================================================
    public function destroy($id)
    {
        try {
            $campaignId = Campaign::find($id);
            if (is_null($campaignId)) {
                return response()->json(['error' => 'No campaign found.'], 400);
            }
            $cloudinaryImagePublicId = $campaignId->creative_upload_id;
            Cloudinary::destroy($cloudinaryImagePublicId);
            Campaign::destroy($id);
            return response()->json(["data" => "Campign Deleted"]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
