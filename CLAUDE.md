# Statamic projekt — Claude-guide

## Generelle regler — ændringsdisciplin

- **Meld altid tydeligt ud** hvis en ændring potentielt kan påvirke andre komponenter, selv hvis risikoen er lille.
- **Rør aldrig vendor-filer som sideeffekt** af andet arbejde. Vendor-filer (`vendor/`, `public/vendor/`) synkroniseres kun bevidst og eksplicit, når det er formålet med opgaven.

---

## Teknisk stack
- Statamic v6 + Laravel
- Vue 3 custom fieldtypes via `resources/js/cp.js`
- Tailwind v4 via `@tailwindcss/vite` i `resources/css/cp.css`
- Antlers templating i `resources/views/`

---

## CP CSS — lag-regler

Alle utilities i `cp.css` skal wrappes i `@layer addon-utilities`:

```css
@layer addon-utilities {
    @import "tailwindcss/utilities";
    /* egne utility-klasser her */
}
```

**Hvorfor:** Top-level CSS overskriver Statamics `@layer utilities` uanset specificity — save-knappen (`.hidden.md:flex`) forsvinder ellers.

---

## CP komponenter — Dropdowns skal bruge DOM-portal

Dropdowns i `cp.js` Vue-komponenter skal appendes til `document.body` manuelt:

```js
const portalEl = { value: null };

function openPortal(rect) {
    const div = document.createElement('div');
    div.style.cssText = `position:fixed;z-index:99999;top:${rect.bottom + 4}px;left:${rect.left}px;`;
    document.body.appendChild(div);
    portalEl.value = div;
    window.addEventListener('scroll', updatePos, true);
}

function removePortal() {
    if (portalEl.value) {
        document.body.removeChild(portalEl.value);
        portalEl.value = null;
        window.removeEventListener('scroll', updatePos, true);
    }
}
```

- `onClickOutside` skal tjekke både `container.value` og `portalEl.value`
- Kald `removePortal()` i `onUnmounted()`

**Hvorfor:** Replicator og page_sections skaber stacking contexts. `window.Vue.Teleport` er undefined i Statamics build.

Se `ColorSchemeSelectorFieldtype` i `resources/js/cp.js` for komplet eksempel.

---

## ThemeColorPicker — oklch farveblanding

`app/Fieldtypes/ThemeColorPicker.php` bygger swatches med oklch-farverum:
- Konverterer hex → oklch, bevarer grå-trinnenes lightness (L), tilføjer subtil chroma fra primærfarven (maks 2,5%)
- `neutralScale()`: `$chroma = min($pC * 0.3, 0.025)`
- Grå-trin: `['#fafafa','#f5f5f5','#e5e5e5','#d4d4d4','#a3a3a3','#737373','#525252','#404040','#262626','#171717','#0a0a0a']`

Swatches injiceres i Bard color picker via `AppServiceProvider`:
```php
Statamic::booted(function () {
    $swatches = ThemeColorPicker::buildSwatches();
    Statamic::provideToScript(['bard-color-picker' => [
        'swatches'  => $swatches,
        'allow_any' => config('statamic.bard-color-picker.allow_any', true),
    ]]);
});
```

**Hvorfor `Statamic::booted()`:** Skal køre efter alle service providers er bootet, ellers overskrives af addonens egen `provideToScript`.

---

## Live Preview — opsætning

Hot reload + bidirektionel scroll-sync i Statamic Live Preview via Alpine.morph.

### Filer der skal til
1. `resources/views/partials/live_preview.antlers.html` — selve scriptet (kopier fra dette projekt)
2. `config/statamic/live_preview.php` — devices + `hot_reload_contents: true` (kopier fra dette projekt)

### `site.js` — Alpine + morph
```js
import Alpine from 'alpinejs'
import morph from '@alpinejs/morph'

window.Alpine = Alpine
Alpine.plugin(morph)
Alpine.start()
```
Kræver `alpinejs` og `@alpinejs/morph` i `package.json`.

### `layout.antlers.html` — inkluder partial
```antlers
{{ if live_preview }}{{ partial:live_preview }}{{ /if }}
```
Placer det lige før `</body>`, efter `site.js`-scriptet.

### `default.antlers.html` — wrap sections
```antlers
{{ page_sections }}
    {{ if live_preview }}<div data-section-index="{{ index }}" data-section-type="{{ type }}">{{ endif }}
        {{ partial :src="'partials/page_sections/' + type + '/' + type" }}
    {{ if live_preview }}</div>{{ endif }}
{{ /page_sections }}
```

### I section-templates — markér felter
```antlers
<div data-field="title">{{ title }}</div>
<div data-field="image">{{ image }}</div>
```
For replicator-items:
```antlers
{{ links }}
    <span data-field-item="{{ index }}" data-focus-field="text">{{ text }}</span>
{{ /links }}
```

---

## Bard Texstyle — eksklusive span-marks

For at gøre en gruppe `btsSpan`-styles eksklusive (kun én ad gangen), brug `onTransaction`-hook i en Tiptap Extension via `Statamic.$bard.addExtension()`. Dette er den eneste pålidelige tilgang.

**Hvad IKKE virker:**
- `addCommands()` override — command-merge rækkefølge er uforudsigelig
- `Statamic.$bard.buttons()` transformer — Statamics button factory bevarer ikke custom properties som `btsStyle`

**Korrekt mønster (se `resources/js/cp.js` — `btsSpanExclusive`):**
```js
Statamic.$bard.addExtension(({ tiptap }) => {
    return tiptap.core.Extension.create({
        name: 'btsSpanExclusive',
        onTransaction({ editor, transaction }) {
            if (!transaction.docChanged || transaction.getMeta('btsSpanCleanup')) return;
            const markType = editor.state.schema.marks.btsSpan;
            if (!markType) return;
            let justAddedClass = null;
            transaction.steps.forEach(step => {
                if (step.toJSON?.()?.stepType === 'addMark' && step.mark?.type === markType) {
                    const cls = step.mark.attrs.class;
                    if (EXCLUSIVE_CLASSES.has(cls)) justAddedClass = cls;
                }
            });
            if (!justAddedClass) return;
            const removals = [];
            editor.state.doc.descendants((node, pos) => {
                if (!node.isText) return;
                node.marks.forEach(mark => {
                    if (mark.type === markType && EXCLUSIVE_CLASSES.has(mark.attrs.class) && mark.attrs.class !== justAddedClass)
                        removals.push({ mark, pos, size: node.nodeSize });
                });
            });
            if (!removals.length) return;
            const tr = editor.state.tr;
            removals.forEach(({ mark, pos, size }) => tr.removeMark(pos, pos + size, mark));
            tr.setMeta('btsSpanCleanup', true);
            editor.view.dispatch(tr);
        },
    });
});
```

**Nøgler:**
- `step.toJSON()?.stepType === 'addMark'` skelner AddMarkStep fra RemoveMarkStep
- `tr.setMeta('btsSpanCleanup', true)` + guard forhindrer uendelig løkke
- `btsSpan` mark har `excludes: ''` i Bard Texstyle — derfor stakker de uden fix
- `attr = 'class'` når `store = 'class'` i `bard_texstyle.php`

---

## Antlers — komplet syntaksreference

### Variables & assignment
```antlers
{{ title }}                          {{# output variabel #}}
{{ meta:city }}                      {{# nested med kolon #}}
{{ _var = 'hello' }}                 {{# lokal variabel (prefix _ anbefalet) #}}
{{ total = 0 }}  {{ total += 1 }}    {{# assignment operators: = += -= *= /= %= #}}
{{ val ?? 'default' }}               {{# null coalescing #}}
{{ show_bio ?= author:bio }}         {{# gatekeeper: output kun hvis truthy #}}
```

**Vigtig:** Brug ALDRIG `layout` som field-handle — det er et reserveret Antlers-variabelnavn (layout-template). Brug fx `gallery_mode` i stedet.

### Modifiers — syntaks
```antlers
{{ title | upper }}                          {{# pipe med parenteser #}}
{{ price | multiply(1.25) }}                 {{# parameter i parenteser #}}
{{ title | upper | truncate(50, '...') }}    {{# kan chaines #}}
{{ _w = settings.size | multiply(10) }}      {{# assignment + modifier virker #}}
```

Modifiers bruger **altid** `| modifier(param)` — aldrig `| modifier:param`.

### Tilgængelige modifiers — komplet liste

**Math:** `add`, `subtract`, `multiply`, `divide`, `mod`, `ceil`, `floor`, `round`, `sum`, `format_number`

**String:** `upper`, `lower`, `slugify`, `replace`, `truncate`, `safe_truncate`, `trim`, `reverse`, `word_count`, `camelize`, `dashify`, `headline`, `title`, `snake`, `kebab`, `studly`, `underscored`, `lcfirst`, `ucfirst`, `swap_case`, `ensure_left`, `ensure_right`, `remove_left`, `remove_right`, `backspace`, `insert`, `substr`, `str_pad_left`, `surround`, `widont`, `smartypants`, `entities`, `sanitize`, `ascii`, `mark`, `regex_mark`, `regex_replace`, `collapse_whitespace`, `spaceless`, `nl2br`, `markdown`, `antlers`, `tidy`, `read_time`, `at`, `segment`, `count_substring`, `explode`, `split`

**Array:** `count`, `length`, `sort`, `limit`, `offset`, `where`, `where-in`, `pluck`, `group_by`, `join`, `flatten`, `unique`, `filter_empty`, `first`, `last`, `reverse`, `shuffle`, `random`, `chunk`, `compact`, `collapse`, `flip`, `keys`, `values`, `select`, `pad`, `piped`, `list`, `ol`, `ul`, `dl`, `ampersand_list`, `sentence_list`, `option_list`, `key_by`, `sum`, `in_array`, `contains`

**Output/Debug:** `raw`, `dump`, `console_log`, `ray`, `to_json`, `decode`, `cdata`, `output`, `bool_string`

**Conditional:** `contains`, `contains_all`, `contains_any`, `starts_with`, `ends_with`, `in_array`, `overlaps`, `doesnt_overlap`, `is_numeric`, `is_alpha`, `is_alphanumeric`, `is_url`, `is_email`, `is_external_url`, `is_embeddable`, `is_array`, `is_json`, `is_empty`, `is_blank`, `is_between`, `is_lowercase`, `is_uppercase`, `is_numberwang`

**Date:** `format`, `format_translated`, `iso_format`, `relative`, `timezone`, `modify_date`, `is_past`, `is_future`, `is_today`, `is_tomorrow`, `is_yesterday`, `is_weekday`, `is_weekend`, `is_leap_year`, `is_after`, `is_before`, `is_between`, `days_ago`, `hours_ago`, `minutes_ago`, `seconds_ago`, `weeks_ago`, `months_ago`, `years_ago`

**Asset/URL:** `url`, `image`, `background_position`, `get`, `resolve`, `full_urls`, `embed_url`, `gravatar`, `favicon`, `link`, `mailto`, `obfuscate`, `obfuscate_email`, `urlencode`, `urldecode`, `rawurlencode`, `parse_url`, `pathinfo`, `to_qs`

**Utility:** `scope`, `as`, `partial`, `macro`, `wrap`, `classes`, `attribute`, `repeat`, `to_spaces`, `to_tabs`, `md5`, `hex_to_rgb`

**Custom (dette projekt):** `to_int` — caster LabeledValue (select-felt) til plain PHP int via `(int)(string)`. Bruges når aritmetik på select-feltværdier er nødvendig: `{{ settings.my_select | to_int | multiply(10) }}`

### Aritmetik
```antlers
{{ price * 1.25 }}       {{# direkte matematik virker #}}
{{ a + b }}              {{# + er TAL-addition (ikke string-concat) #}}
{{ _a ~ ' ' ~ _b }}      {{# ~ er string-konkatenering #}}
```

### Conditionals
```antlers
{{ if condition }} ... {{ elseif other }} ... {{ else }} ... {{ /if }}
{{ unless condition }} ... {{ /unless }}
{{ is_sold ? "Solgt" : "Til salg" }}   {{# ternary #}}
```

### Loops
```antlers
{{ items }}
    {{ value }}          {{# aktuel værdi #}}
    {{ index }}          {{# 0-baseret #}}
    {{ count }}          {{# 1-baseret #}}
    {{ first }}          {{# boolean #}}
    {{ last }}           {{# boolean #}}
    {{ total_results }}  {{# samlet antal #}}
{{ /items }}
```

### Tags & parametre
```antlers
{{# Statisk streng-parameter #}}
{{ partial:hero class="mt-4" }}

{{# Interpolation med ENKELT tuborg (ikke dobbelt!) #}}
{{ nav from="{segment_1}/{segment_2}" }}
{{ collection:blog limit="{entry_limit ?? 10}" }}

{{# Dynamisk binding med kolon-prefix #}}
{{ partial :src="my_var" }}
{{ glide :src="url" width="800" }}

{{# Self-closing tag #}}
{{ partial:footer /}}
```

**Vigtig:** Inde i tag-parametre bruges `{variable}` (enkelt tuborg), IKKE `{{ variable }}` (dobbelt). Nested `{{ }}` inde i attributter virker ikke.

### Partials
```antlers
{{ partial:components/image :imagePath="url" w="800" }}
{{ partial src="partials/page_sections/{ type }" id="{{ id }}" }}
```

### Style push / yield
```antlers
{{# I section-template: #}}
{{ style_push }}
<style>
    #{{ id }} { ... }
</style>
{{ /style_push }}

{{# I layout: #}}
{{ yield:style }}
```

### Glide (billedoptimering)
```antlers
{{ glide :src="url" width="800" height="600" quality="75" format="webp" fit="crop_focal" }}
    <img src="{{ url }}" alt="{{ alt }}">
{{ /glide }}
```

### Fieldset if-conditions (YAML)
```yaml
if:
  field_handle: 'equals value'      # vis hvis
  field_handle: 'isnt value'        # vis hvis ikke
  field_handle: 'not empty'         # vis hvis udfyldt
  $parent.field_handle: 'equals x'  # reference felt udenfor tabby
```
`unless:` er IKKE understøttet — brug `isnt` i stedet.

### Reserverede variabelnavne (undgå som field-handles)
- `layout` — layout-template navn
- `type` — replicator set type
- `id` — entry ID
- `url` — entry URL
- `slug` — entry slug
- `index`, `count`, `first`, `last` — loop-variabler
