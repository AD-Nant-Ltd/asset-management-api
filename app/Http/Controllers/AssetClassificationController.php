<?php

namespace App\Http\Controllers;

use App\Models\AssetCondition;
use App\Models\AssetStatus;
use App\Models\AssetSubtype;
use App\Models\AssetType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetClassificationController extends Controller
{
    public function index()
    {
        $assetTypes = AssetType::orderBy('asset_type')->get();

        $assetSubtypes = AssetSubtype::with('assetType')
            ->orderBy('asset_subtype')
            ->get();

        $assetStatuses = AssetStatus::orderBy('asset_status')->get();

        $assetConditions = AssetCondition::orderBy('asset_condition')->get();

        return view('asset-classification.index', compact(
            'assetTypes',
            'assetSubtypes',
            'assetStatuses',
            'assetConditions'
        ));
    }

    public function storeAssetType(Request $request)
    {
        $validated = $request->validate([
            'asset_type' => [
                'required',
                'string',
                'max:100',
                'unique:asset_types,asset_type',
            ],
        ]);

        AssetType::create([
            'asset_type' => $validated['asset_type'],
            'active' => true,
        ]);

        return redirect()
            ->route('asset-classification.index')
            ->with('success', 'Asset type created successfully.');
    }

    public function updateAssetType(Request $request, AssetType $assetType)
    {
        $validated = $request->validate([
            'asset_type' => [
                'required',
                'string',
                'max:100',
                Rule::unique('asset_types', 'asset_type')
                    ->ignore($assetType->id),
            ],
            'active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $assetType->update([
            'asset_type' => $validated['asset_type'],
            'active' => $request->boolean('active'),
        ]);

        return redirect()
            ->route('asset-classification.index')
            ->with('success', 'Asset type updated successfully.');
    }

    public function storeAssetSubtype(Request $request)
    {
        $validated = $request->validate([
            'asset_type_id' => [
                'required',
                'integer',
                'exists:asset_types,id',
            ],
            'asset_subtype' => [
                'required',
                'string',
                'max:100',
                'unique:asset_subtypes,asset_subtype',
            ],
        ]);

        AssetSubtype::create([
            'asset_type_id' => $validated['asset_type_id'],
            'asset_subtype' => $validated['asset_subtype'],
            'active' => true,
        ]);

        return redirect()
            ->route('asset-classification.index')
            ->with('success', 'Asset subtype created successfully.');
    }

    public function updateAssetSubtype(
        Request $request,
        AssetSubtype $assetSubtype
    ) {
        $validated = $request->validate([
            'asset_type_id' => [
                'required',
                'integer',
                'exists:asset_types,id',
            ],
            'asset_subtype' => [
                'required',
                'string',
                'max:100',
                Rule::unique('asset_subtypes', 'asset_subtype')
                    ->ignore($assetSubtype->id),
            ],
            'active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $assetSubtype->update([
            'asset_type_id' => $validated['asset_type_id'],
            'asset_subtype' => $validated['asset_subtype'],
            'active' => $request->boolean('active'),
        ]);

        return redirect()
            ->route('asset-classification.index')
            ->with('success', 'Asset subtype updated successfully.');
    }

    public function storeAssetStatus(Request $request)
    {
        $validated = $request->validate([
            'asset_status' => [
                'required',
                'string',
                'max:100',
                'unique:asset_statuses,asset_status',
            ],
        ]);

        AssetStatus::create([
            'asset_status' => $validated['asset_status'],
            'active' => true,
        ]);

        return redirect()
            ->route('asset-classification.index')
            ->with('success', 'Asset status created successfully.');
    }

    public function updateAssetStatus(
        Request $request,
        AssetStatus $assetStatus
    ) {
        $validated = $request->validate([
            'asset_status' => [
                'required',
                'string',
                'max:100',
                Rule::unique('asset_statuses', 'asset_status')
                    ->ignore($assetStatus->id),
            ],
            'active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $assetStatus->update([
            'asset_status' => $validated['asset_status'],
            'active' => $request->boolean('active'),
        ]);

        return redirect()
            ->route('asset-classification.index')
            ->with('success', 'Asset status updated successfully.');
    }

    public function storeAssetCondition(Request $request)
    {
        $validated = $request->validate([
            'asset_condition' => [
                'required',
                'string',
                'max:100',
                'unique:asset_conditions,asset_condition',
            ],
        ]);

        AssetCondition::create([
            'asset_condition' => $validated['asset_condition'],
            'active' => true,
        ]);

        return redirect()
            ->route('asset-classification.index')
            ->with('success', 'Asset condition created successfully.');
    }

    public function updateAssetCondition(
        Request $request,
        AssetCondition $assetCondition
    ) {
        $validated = $request->validate([
            'asset_condition' => [
                'required',
                'string',
                'max:100',
                Rule::unique('asset_conditions', 'asset_condition')
                    ->ignore($assetCondition->id),
            ],
            'active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $assetCondition->update([
            'asset_condition' => $validated['asset_condition'],
            'active' => $request->boolean('active'),
        ]);

        return redirect()
            ->route('asset-classification.index')
            ->with('success', 'Asset condition updated successfully.');
    }
}