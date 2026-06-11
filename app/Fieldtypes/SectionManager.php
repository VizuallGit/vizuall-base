<?php

namespace App\Fieldtypes;

use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Fieldset;
use Statamic\Fields\Fieldtype;

class SectionManager extends Fieldtype
{
    protected static $handle = 'section_manager';
    protected $selectable = false;

    public function component(): string
    {
        return 'section-manager';
    }

    public function preload(): array
    {
        $fieldset = Fieldset::find('page_sections');
        if (! $fieldset) return ['sets' => []];

        $contents = $fieldset->contents();
        $rawSets  = $contents['fields'][0]['field']['sets']['items']['sets'] ?? [];
        $allFiles = Storage::disk('assets')->allFiles();

        $sets = [];
        foreach ($rawSets as $handle => $set) {
            $image    = $set['image'] ?? null;
            $imageUrl = null;

            if ($image) {
                $match    = collect($allFiles)->first(fn($f) => basename($f) === basename($image));
                $imageUrl = $match ? '/assets/' . $match : null;
            }

            $sets[] = [
                'handle'    => $handle,
                'display'   => $set['display'] ?? $handle,
                'image_url' => $imageUrl,
                'hidden'    => ($set['hide'] ?? false) === true,
            ];
        }

        return ['sets' => $sets];
    }

    public function process($value): null
    {
        if (! is_array($value)) return null;

        $fieldset = Fieldset::find('page_sections');
        if (! $fieldset) return null;

        $contents = $fieldset->contents();
        $sets     = &$contents['fields'][0]['field']['sets']['items']['sets'];

        foreach ($value as $handle => $changes) {
            if (! isset($sets[$handle])) continue;

            if (array_key_exists('display', $changes) && $changes['display'] !== '') {
                $sets[$handle]['display'] = $changes['display'];
            }

            if (array_key_exists('hidden', $changes)) {
                if ($changes['hidden']) {
                    $sets[$handle]['hide'] = true;
                } else {
                    unset($sets[$handle]['hide']);
                }
            }
        }

        $fieldset->setContents($contents)->save();

        return null;
    }

    public function preProcess($value): null { return null; }
}
