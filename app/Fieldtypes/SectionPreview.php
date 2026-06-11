<?php

namespace App\Fieldtypes;

use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Fieldset;
use Statamic\Fields\Fieldtype;

class SectionPreview extends Fieldtype
{
    protected static $handle = 'section_preview';
    protected $selectable = false;

    public function component(): string
    {
        return 'section-preview';
    }

    public function preload(): array
    {
        $setHandle = $this->config('set_handle');
        if (! $setHandle) return ['image_url' => null, 'label' => null];

        $fieldset = Fieldset::find('page_sections');
        if (! $fieldset) return ['image_url' => null, 'label' => null];

        $contents = $fieldset->contents();
        $sets = $contents['fields'][0]['field']['sets']['items']['sets'] ?? [];
        $set  = $sets[$setHandle] ?? null;

        if (! $set) return ['image_url' => null, 'label' => null];

        $image = $set['image'] ?? null;
        $imageUrl = null;

        if ($image) {
            $all   = Storage::disk('assets')->allFiles();
            $match = collect($all)->first(fn($f) => basename($f) === basename($image));
            $imageUrl = $match ? '/assets/' . $match : null;
        }

        return [
            'image_url' => $imageUrl,
            'label'     => $set['display'] ?? $setHandle,
        ];
    }

    public function preProcess($value): null { return null; }
    public function process($value): null    { return null; }
}
