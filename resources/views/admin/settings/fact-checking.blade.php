@extends('admin.layouts.app')

@section('title', 'Fact-checking')
@section('page-title', 'Page Fact-checking')

@section('content')
    <form class="admin-form" method="POST" action="{{ route('admin.settings.fact-checking.update') }}" style="max-width: 760px;">
        @csrf
        @method('PUT')

        <p class="form-hint" style="margin-bottom: 20px;">Contenu affiché sur la page <a href="{{ route('fact-checking') }}" target="_blank">Fact-checking</a>.</p>

        @foreach ([
            'fr' => ['label' => 'Français', 'default' => true, 'intro' => $intro, 'cta' => $cta, 'criteria' => $criteria, 'prefix' => 'criteria'],
            'en' => ['label' => 'English', 'default' => false, 'intro' => $introEn, 'cta' => $ctaEn, 'criteria' => $criteriaEn, 'prefix' => 'criteria_en'],
            'pt' => ['label' => 'Português', 'default' => false, 'intro' => $introPt, 'cta' => $ctaPt, 'criteria' => $criteriaPt, 'prefix' => 'criteria_pt'],
        ] as $code => $block)
            <div class="locale-block">
                <h3 class="locale-block-title">
                    <i class="fa-solid fa-language" aria-hidden="true"></i> {{ $block['label'] }}
                    @if ($block['default'])
                        <span class="badge badge-published">Par défaut</span>
                    @endif
                </h3>

                <div class="form-group">
                    <label for="intro_{{ $code }}">Introduction{{ $block['default'] ? ' *' : '' }}</label>
                    <textarea id="intro_{{ $code }}" name="{{ $block['default'] ? 'intro' : 'intro_'.$code }}" rows="3" @if($block['default']) required @endif>{{ old($block['default'] ? 'intro' : 'intro_'.$code, $block['intro']) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="cta_{{ $code }}">Appel à l'action{{ $block['default'] ? ' *' : '' }}</label>
                    <input type="text" id="cta_{{ $code }}" name="{{ $block['default'] ? 'cta' : 'cta_'.$code }}" value="{{ old($block['default'] ? 'cta' : 'cta_'.$code, $block['cta']) }}" @if($block['default']) required @endif>
                </div>

                <h4 style="margin: 16px 0 10px; font-size: 13px; text-transform: uppercase; color: #666;">Critères</h4>
                <div class="criteria-list" data-prefix="{{ $block['prefix'] }}">
                    @foreach (old($block['prefix'], $block['criteria']) as $index => $item)
                        <div class="criteria-row sidebar-box">
                            <div class="form-group">
                                <label>Titre{{ $block['default'] ? ' *' : '' }}</label>
                                <input type="text" name="{{ $block['prefix'] }}[{{ $index }}][title]" value="{{ $item['title'] ?? '' }}" @if($block['default']) required @endif>
                            </div>
                            <div class="form-group">
                                <label>Description{{ $block['default'] ? ' *' : '' }}</label>
                                <textarea name="{{ $block['prefix'] }}[{{ $index }}][text]" rows="2" @if($block['default']) required @endif>{{ $item['text'] ?? '' }}</textarea>
                            </div>
                            @if ($block['default'] && $index > 0)
                                <button type="button" class="btn btn-sm btn-danger remove-criterion"><i class="fa-solid fa-trash" aria-hidden="true"></i> Retirer</button>
                            @elseif (! $block['default'] && $index > 0)
                                <button type="button" class="btn btn-sm btn-danger remove-criterion"><i class="fa-solid fa-trash" aria-hidden="true"></i> Retirer</button>
                            @endif
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-secondary btn-sm add-criterion" data-prefix="{{ $block['prefix'] }}" style="margin-bottom: 8px;">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Ajouter un critère
                </button>
            </div>
        @endforeach

        <div class="form-actions" style="border: none; padding-top: 0; justify-content: flex-start;">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>

    <template id="criterion-template">
        <div class="criteria-row sidebar-box">
            <div class="form-group">
                <label>Titre</label>
                <input type="text" data-name="title">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea rows="2" data-name="text"></textarea>
            </div>
            <button type="button" class="btn btn-sm btn-danger remove-criterion"><i class="fa-solid fa-trash" aria-hidden="true"></i> Retirer</button>
        </div>
    </template>

    <script>
        document.querySelectorAll('.add-criterion').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const prefix = btn.dataset.prefix;
                const list = document.querySelector('.criteria-list[data-prefix="' + prefix + '"]');
                const index = list.querySelectorAll('.criteria-row').length;
                const tpl = document.getElementById('criterion-template').content.cloneNode(true);

                tpl.querySelector('[data-name="title"]').name = prefix + '[' + index + '][title]';
                tpl.querySelector('[data-name="text"]').name = prefix + '[' + index + '][text]';

                list.appendChild(tpl);
            });
        });

        document.addEventListener('click', function (event) {
            const btn = event.target.closest('.remove-criterion');
            if (btn) btn.closest('.criteria-row')?.remove();
        });
    </script>
@endsection
