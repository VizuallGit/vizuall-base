<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Vizuall Bard Styles — erstatning for bard-texstyle
    |--------------------------------------------------------------------------
    |
    | Felter for SPAN-styles (inline):
    |   handle  — unikt nøgleord
    |   type    — 'span' (standard) eller 'paragraph'
    |   name    — tooltip i toolbar
    |   ident   — knaptekst (maks 3 tegn)
    |   prop    — CSS-property (fx 'font-size', 'text-transform')
    |   value   — CSS-værdien (fx 'var(--size-500)', 'uppercase')
    |   group   — (valgfri) styles med samme group-navn samles i én dropdown-knap.
    |             Styles UDEN group får hver sin individuelle knap i pickeren.
    |
    | Felter for PARAGRAPH-styles (sætter CSS-klasse på <p>, <h1> osv.):
    |   handle  — unikt nøgleord
    |   type    — 'paragraph'
    |   name    — tooltip i toolbar
    |   ident   — knaptekst
    |   class   — CSS-klasse der sættes på blok-elementet
    |   cp_css  — preview-CSS der vises i CP-editoren
    |
    | Felter for DIV-styles (wrapper-div rundt om blok-indhold):
    |   handle  — unikt nøgleord
    |   type    — 'div'
    |   name    — tooltip i toolbar
    |   ident   — knaptekst
    |   class   — CSS-klasse der sættes på div-elementet
    |   cp_css  — preview-CSS der vises i CP-editoren
    |
    | 'groups' — metadata for dropdown-grupper (én post per unik group-værdi):
    |   name    — tooltip og label på dropdown-knappen i pickeren
    |   ident   — knaptekst på knappen når ingen style er aktiv
    |
    */

    'groups' => [
        'sizes-dropdown' => ['name' => 'Tekststørrelse', 'ident' => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 8 8"><path d="M0 0h8v8H0z" fill="none" /><path fill="currentColor" d="M5 7V5H4V4h3v1H6v2M2 2H0V1h5v1H3v5H2" /></svg>'],
        'flow-spacing-dropdown' => ['name' => 'Flow spacing', 'ident' => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none" /><path fill="currentColor" d="M4 20h16v2H4zM4 2h16v2H4zm8.5 4h-1c-.43 0-.81.27-.94.67L6.64 18h2.12l1.04-3h4.42l1.04 3h2.12L13.46 6.67c-.14-.4-.52-.67-.94-.67Zm1.02 7h-3.04L12 8.61z" /></svg>'],
    ],

    'styles' => [

        // ── Div-wrapper styles — individuel knap ─────────────────────────────
        ['handle' => 'two_columns', 'type' => 'div', 'name' => 'Two Columns', 'ident' => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"> <path d="M0 0h24v24H0z" fill="none" /> <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"> <rect width="18" height="18" x="3" y="3" rx="2" /> <path d="M12 3v18" /> </g> </svg>', 'class' => 'two-columns', 'cp_css' => 'column-count: 2; column-gap: 16px'],
        ['handle' => 'three_columns', 'type' => 'div', 'name' => 'Three Columns', 'ident' => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"> <path d="M0 0h24v24H0z" fill="none" /> <path fill="currentColor" d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2m12 2v12h4V6zM4 6v12h4V6zm6 0v12h4V6z" /> </svg>', 'class' => 'three-columns', 'cp_css' => 'column-count: 3; column-gap: 16px'],

        // ── Paragraph-niveau styles — individuel knap ────────────────────────
        [
            'handle' => 'title',
            'type'   => 'paragraph',
            'name'   => 'Title text',
            'ident'  => 'Ti',
            'class'  => 'title',
            'cp_css' => 'text-transform: uppercase; letter-spacing: 2px; font-size: 0.875em; line-height: 1;',
        ],

        [
            'handle' => 'highlighted',
            'type'   => 'span',
            'name'   => 'Highlighted color',
            'ident'  => 'Hc',
            'prop'   => 'color',
            'value'  => 'var(--highlighted-color)',
        ],

        ['handle' => 'flow_100', 'target' => 'block', 'type' => 'span', 'name' => 'flow 100', 'ident' => 'F1', 'prop' => '--flow-space', 'value' => 'var(--size-100)', 'group' => 'flow-spacing-dropdown'],
        ['handle' => 'flow_200', 'target' => 'block', 'type' => 'span', 'name' => 'flow 200', 'ident' => 'F2', 'prop' => '--flow-space', 'value' => 'var(--size-200)', 'group' => 'flow-spacing-dropdown'],
        ['handle' => 'flow_300', 'target' => 'block', 'type' => 'span', 'name' => 'flow 300', 'ident' => 'F3', 'prop' => '--flow-space', 'value' => 'var(--size-300)', 'group' => 'flow-spacing-dropdown'],
        ['handle' => 'flow_400', 'target' => 'block', 'type' => 'span', 'name' => 'flow 400', 'ident' => 'F4', 'prop' => '--flow-space', 'value' => 'var(--size-400)', 'group' => 'flow-spacing-dropdown'],
        ['handle' => 'flow_500', 'target' => 'block', 'type' => 'span', 'name' => 'flow 500', 'ident' => 'F5', 'prop' => '--flow-space', 'value' => 'var(--size-500)', 'group' => 'flow-spacing-dropdown'],
        ['handle' => 'flow_600', 'target' => 'block', 'type' => 'span', 'name' => 'flow 600', 'ident' => 'F6', 'prop' => '--flow-space', 'value' => 'var(--size-600)', 'group' => 'flow-spacing-dropdown'],
        ['handle' => 'flow_700', 'target' => 'block', 'type' => 'span', 'name' => 'flow 700', 'ident' => 'F7', 'prop' => '--flow-space', 'value' => 'var(--size-700)', 'group' => 'flow-spacing-dropdown'],
        ['handle' => 'flow_800', 'target' => 'block', 'type' => 'span', 'name' => 'flow 800', 'ident' => 'F8', 'prop' => '--flow-space', 'value' => 'var(--size-800)', 'group' => 'flow-spacing-dropdown'],
        ['handle' => 'flow_900', 'target' => 'block', 'type' => 'span', 'name' => 'flow 900', 'ident' => 'F9', 'prop' => '--flow-space', 'value' => 'var(--size-900)', 'group' => 'flow-spacing-dropdown'],
        ['handle' => 'flow_1em', 'target' => 'block', 'type' => 'span', 'name' => 'flow 1em', 'ident' => 'F1em', 'prop' => '--flow-space', 'value' => '1em', 'group' => 'flow-spacing-dropdown'],
        ['handle' => 'flow_2em', 'target' => 'block', 'type' => 'span', 'name' => 'flow 2em', 'ident' => 'F2em', 'prop' => '--flow-space', 'value' => '2em', 'group' => 'flow-spacing-dropdown'],
        ['handle' => 'flow_3em', 'target' => 'block', 'type' => 'span', 'name' => 'flow 3em', 'ident' => 'F3em', 'prop' => '--flow-space', 'value' => '3em', 'group' => 'flow-spacing-dropdown'],

        // ── Tekststørrelser — samlet i dropdown via 'group' ──────────────────
        ['handle' => 'text_100', 'type' => 'span', 'name' => 'Size 100', 'ident' => 'T1', 'prop' => 'font-size', 'value' => 'var(--size-100)', 'group' => 'sizes-dropdown'],
        ['handle' => 'text_200', 'type' => 'span', 'name' => 'Size 200', 'ident' => 'T2', 'prop' => 'font-size', 'value' => 'var(--size-200)', 'group' => 'sizes-dropdown'],
        ['handle' => 'text_300', 'type' => 'span', 'name' => 'Size 300', 'ident' => 'T3', 'prop' => 'font-size', 'value' => 'var(--size-300)', 'group' => 'sizes-dropdown'],
        ['handle' => 'text_400', 'type' => 'span', 'name' => 'Size 400', 'ident' => 'T4', 'prop' => 'font-size', 'value' => 'var(--size-400)', 'group' => 'sizes-dropdown'],
        ['handle' => 'text_500', 'type' => 'span', 'name' => 'Size 500', 'ident' => 'T5', 'prop' => 'font-size', 'value' => 'var(--size-500)', 'group' => 'sizes-dropdown'],
        ['handle' => 'text_600', 'type' => 'span', 'name' => 'Size 600', 'ident' => 'T6', 'prop' => 'font-size', 'value' => 'var(--size-600)', 'group' => 'sizes-dropdown'],
        ['handle' => 'text_700', 'type' => 'span', 'name' => 'Size 700', 'ident' => 'T7', 'prop' => 'font-size', 'value' => 'var(--size-700)', 'group' => 'sizes-dropdown'],
        ['handle' => 'text_800', 'type' => 'span', 'name' => 'Size 800', 'ident' => 'T8', 'prop' => 'font-size', 'value' => 'var(--size-800)', 'group' => 'sizes-dropdown'],
        ['handle' => 'text_900', 'type' => 'span', 'name' => 'Size 900', 'ident' => 'T9', 'prop' => 'font-size', 'value' => 'var(--size-900)', 'group' => 'sizes-dropdown'],

        // ── Transform — individuel knap ──────────────────────────────────────
        ['handle' => 'uppercase', 'type' => 'span', 'name' => 'Uppercase', 'ident' => 'U', 'prop' => 'text-transform', 'value' => 'uppercase'],

    ],

];
