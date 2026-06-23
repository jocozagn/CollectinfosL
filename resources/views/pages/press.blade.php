@extends('layouts.app')



@section('title', 'Relations presse – Collectinfos')



@section('content')

    <section class="page-hero page-hero--compact">

        <div class="container">

            <h1 class="page-title">Relations presse</h1>

            <p class="page-subtitle">{{ $press['intro'] }}</p>

        </div>

    </section>



    <section class="section-page">

        <div class="container">

            <div class="page-grid">

                <div class="page-content">

                    <h2 class="content-subtitle">Nos services presse</h2>

                    <ul class="check-list">

                        @foreach ($press['services'] as $service)

                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> {{ $service }}</li>

                        @endforeach

                    </ul>



                    <div class="info-card info-card--highlight" style="margin-top: 24px;">

                        <i class="fa-solid fa-headset" aria-hidden="true"></i>

                        <h3>Contact presse</h3>

                        <p>E-mail : <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></p>

                        <p>Tél : <strong>{{ $contact['phone'] }}</strong></p>

                    </div>

                </div>



                <div class="page-form-wrap" id="press-form">

                    <h2 class="form-heading"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i> Relation presse</h2>



                    @if (session('press_success'))

                        <div class="form-alert form-alert--success" role="status">

                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>

                            {{ session('press_success') }}

                        </div>

                    @endif



                    @if ($errors->any())

                        <div class="form-alert form-alert--error" role="alert">

                            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>

                            Veuillez corriger les erreurs ci-dessous.

                        </div>

                    @endif



                    <form class="ci-form" method="POST" action="{{ route('press.store') }}">

                        @csrf



                        <div class="form-group">

                            <label for="company_name">Nom de l'entreprise *</label>

                            <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required>

                            @error('company_name')<span class="field-error">{{ $message }}</span>@enderror

                        </div>



                        <div class="form-group">

                            <label for="press-email">E-mail *</label>

                            <input type="email" id="press-email" name="email" value="{{ old('email') }}" required>

                            @error('email')<span class="field-error">{{ $message }}</span>@enderror

                        </div>



                        <div class="form-group">

                            <label for="experience_years">Expérience de l'entreprise</label>

                            <div class="range-field">

                                <input

                                    type="range"

                                    id="experience_years"

                                    name="experience_years"

                                    min="0"

                                    max="10"

                                    step="1"

                                    value="{{ old('experience_years', 0) }}"

                                    class="range-input"

                                    data-range-output="experience-years-value"

                                >

                                <div class="range-meta">

                                    <span>Valeur sélectionnée : <strong id="experience-years-value">0</strong></span>

                                    <span class="range-hint">de 1 à 10 ans et plus</span>

                                </div>

                            </div>

                            @error('experience_years')<span class="field-error">{{ $message }}</span>@enderror

                        </div>



                        <div class="form-group">

                            <label for="company_experience">Présentation de l'entreprise</label>

                            <textarea id="company_experience" name="company_experience" rows="3" placeholder="Décrivez brièvement l'expérience et les activités de votre organisation…">{{ old('company_experience') }}</textarea>

                            @error('company_experience')<span class="field-error">{{ $message }}</span>@enderror

                        </div>



                        <div class="form-group">

                            <label>Thématiques (Sujets) *</label>

                            <p class="form-hint">Sélectionnez une ou plusieurs thématiques.</p>

                            <div class="chip-group" role="group" aria-label="Thématiques">

                                @foreach ($pressTopics as $key => $label)

                                    <label class="chip-token">

                                        <input

                                            type="checkbox"

                                            name="topics[]"

                                            value="{{ $key }}"

                                            @checked(in_array($key, old('topics', []), true))

                                            @if ($key === 'other') data-toggle-other="topics-other-wrap" @endif

                                        >

                                        <span class="chip-token__text">{{ $label }}@if ($key === 'other')…@endif</span>

                                    </label>

                                @endforeach

                            </div>

                            @error('topics')<span class="field-error">{{ $message }}</span>@enderror

                            @error('topics.*')<span class="field-error">{{ $message }}</span>@enderror

                        </div>



                        <div class="form-group" id="topics-other-wrap" @if (! in_array('other', old('topics', []), true)) hidden @endif>

                            <label for="topics_other">Précisez « Autre »</label>

                            <input type="text" id="topics_other" name="topics_other" value="{{ old('topics_other') }}">

                            @error('topics_other')<span class="field-error">{{ $message }}</span>@enderror

                        </div>



                        <div class="form-group">

                            <label for="country">Pays *</label>

                            <select id="country" name="country" required>

                                <option value="">— Choisir votre pays —</option>

                                @foreach ($pressCountries as $countryName)

                                    <option value="{{ $countryName }}" @selected(old('country') === $countryName)>{{ $countryName }}</option>

                                @endforeach

                            </select>

                            @error('country')<span class="field-error">{{ $message }}</span>@enderror

                        </div>



                        <div class="form-group">

                            <label for="press-message">Que pouvons-nous faire pour vous ? *</label>

                            <textarea id="press-message" name="message" rows="5" required placeholder="Décrivez votre demande…">{{ old('message') }}</textarea>

                            @error('message')<span class="field-error">{{ $message }}</span>@enderror

                        </div>



                        <button type="submit" class="ci-btn ci-btn--primary">

                            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Envoyer la demande

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </section>

@endsection


