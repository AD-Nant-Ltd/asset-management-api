<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCondition;
use App\Models\AssetStatus;
use App\Models\AssetSubtype;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
            'availability' => [
                'nullable',
                Rule::in([
                    'all',
                    'in_stock',
                    'assigned',
                    'unavailable',
                ]),
            ],
        ]);

        $search = trim($validated['search'] ?? '');
        $availability = $validated['availability'] ?? 'all';

        $query = Asset::with([
            'assetSubtype.assetType',
            'assetStatus',
            'assetCondition',
            'activeAssignment.assignedTo',
        ]);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query
                    ->where(
                        'asset_tag',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'serial_num',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhereHas(
                        'assetSubtype',
                        function ($query) use ($search) {
                            $query->where(
                                'asset_subtype',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    )
                    ->orWhereHas(
                        'assetSubtype.assetType',
                        function ($query) use ($search) {
                            $query->where(
                                'asset_type',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    );
            });
        }

        if ($availability === 'assigned') {
            $query->whereHas('activeAssignment');
        }

        if ($availability === 'in_stock') {
            $query
                ->whereDoesntHave('activeAssignment')
                ->whereNull('archived_at')
                ->whereHas(
                    'assetStatus',
                    function ($query) {
                        $query->where(
                            'asset_status',
                            'Operational'
                        );
                    }
                )
                ->where(function ($query) {
                    $query
                        ->whereNull('retired_date')
                        ->orWhereDate(
                            'retired_date',
                            '>',
                            today()
                        );
                })
                ->where(function ($query) {
                    $query
                        ->whereNull('disposal_date')
                        ->orWhereDate(
                            'disposal_date',
                            '>',
                            today()
                        );
                });
        }

        if ($availability === 'unavailable') {
            $query
                ->whereDoesntHave('activeAssignment')
                ->where(function ($query) {
                    $query
                        ->whereNotNull('archived_at')
                        ->orWhereHas(
                            'assetStatus',
                            function ($query) {
                                $query->where(
                                    'asset_status',
                                    '!=',
                                    'Operational'
                                );
                            }
                        )
                        ->orWhereDate(
                            'retired_date',
                            '<=',
                            today()
                        )
                        ->orWhereDate(
                            'disposal_date',
                            '<=',
                            today()
                        );
                });
        }

        $assets = $query
            ->orderBy('asset_tag')
            ->get();

        return view('assets.index', compact(
            'assets',
            'search',
            'availability'
        ));
    }

    public function create()
    {
        $assetSubtypes = AssetSubtype::with('assetType')
            ->where('active', true)
            ->whereHas('assetType', function ($query) {
                $query->where('active', true);
            })
            ->orderBy('asset_subtype')
            ->get();

        $assetStatuses = AssetStatus::where('active', true)
            ->orderBy('asset_status')
            ->get();

        $assetConditions = AssetCondition::where('active', true)
            ->orderBy('asset_condition')
            ->get();

        return view('assets.create', compact(
            'assetSubtypes',
            'assetStatuses',
            'assetConditions'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_subtype_id' => [
                'required',
                'integer',
                Rule::exists('asset_subtypes', 'id')
                    ->where('active', true),
            ],
            'asset_status_id' => [
                'required',
                'integer',
                Rule::exists('asset_statuses', 'id')
                    ->where('active', true),
            ],
            'asset_condition_id' => [
                'required',
                'integer',
                Rule::exists('asset_conditions', 'id')
                    ->where('active', true),
            ],
            'asset_tag' => [
                'required',
                'string',
                'max:100',
                'unique:assets,asset_tag',
            ],
            'serial_num' => [
                'nullable',
                'string',
                'max:255',
                'unique:assets,serial_num',
            ],
            'delivery_date' => [
                'nullable',
                'date',
            ],
            'purchase_date' => [
                'nullable',
                'date',
            ],
            'retired_date' => [
                'nullable',
                'date',
            ],
            'warranty_expiry' => [
                'nullable',
                'date',
            ],
            'disposal_date' => [
                'nullable',
                'date',
            ],
        ]);

        Asset::create($validated);

        return redirect()
            ->route('assets.index')
            ->with('success', 'Asset created successfully.');
    }

    public function show(Asset $asset)
    {
        $asset->load([
            'assetSubtype.assetType',
            'assetStatus',
            'assetCondition',
            'activeAssignment.assignedTo',
            'activeAssignment.assignedBy.staff',
            'assignments.assignedTo',
            'incidents',
        ]);

        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $this->ensureAssetIsEditable($asset);

        $assetSubtypes = AssetSubtype::with('assetType')
            ->where(function ($query) use ($asset) {
                $query->where('active', true)
                    ->orWhere('id', $asset->asset_subtype_id);
            })
            ->orderBy('asset_subtype')
            ->get();

        $assetStatuses = AssetStatus::where(function ($query) use ($asset) {
            $query->where('active', true)
                ->orWhere('id', $asset->asset_status_id);
        })
            ->orderBy('asset_status')
            ->get();

        $assetConditions = AssetCondition::where(function ($query) use ($asset) {
            $query->where('active', true)
                ->orWhere('id', $asset->asset_condition_id);
        })
            ->orderBy('asset_condition')
            ->get();

        return view('assets.edit', compact(
            'asset',
            'assetSubtypes',
            'assetStatuses',
            'assetConditions'
        ));
    }

    public function update(Request $request, Asset $asset)
    {
        $this->ensureAssetIsEditable($asset);

        $validated = $request->validate([
            'asset_subtype_id' => [
                'required',
                'integer',
                'exists:asset_subtypes,id',
            ],
            'asset_status_id' => [
                'required',
                'integer',
                'exists:asset_statuses,id',
            ],
            'asset_condition_id' => [
                'required',
                'integer',
                'exists:asset_conditions,id',
            ],
            'asset_tag' => [
                'required',
                'string',
                'max:100',
                Rule::unique('assets', 'asset_tag')
                    ->ignore($asset->id),
            ],
            'serial_num' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('assets', 'serial_num')
                    ->ignore($asset->id),
            ],
            'delivery_date' => [
                'nullable',
                'date',
            ],
            'purchase_date' => [
                'nullable',
                'date',
            ],
            'retired_date' => [
                'nullable',
                'date',
            ],
            'warranty_expiry' => [
                'nullable',
                'date',
            ],
            'disposal_date' => [
                'nullable',
                'date',
            ],
        ]);

        $asset->update($validated);

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Asset updated successfully.');
    }

    public function archive(Asset $asset)
    {
        if ($asset->archived_at === null) {
            $asset->update([
                'archived_at' => now(),
            ]);
        }

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Asset archived successfully.');
    }

    private function ensureAssetIsEditable(Asset $asset): void
    {
        if ($asset->archived_at !== null) {
            abort(403, 'Archived assets cannot be edited.');
        }
    }
}