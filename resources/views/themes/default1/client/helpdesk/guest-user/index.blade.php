@extends('themes.default1.client.layout.client')

@section('home')
    class = "nav-item active"
@stop

@section('breadcrumb')
@stop
@section('content')
@if(!Session::has('error') && count($errors)>0)
    <div class="alert alert-danger alert-dismissable">
        <i class="fa fa-ban"></i>
        <b>{!! Lang::get('lang.alert') !!} !</b>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

<div class="col-12">
    <section class="corp-hero" data-parallax>
        <div class="corp-hero__media" data-parallax-layer></div>
        <div class="corp-hero__overlay"></div>
        <div class="corp-hero__content">
            <p class="corp-muted text-uppercase mb-2">Enterprise Support Hub</p>
            <h1 class="corp-hero__title">Support in einer neuen Dimension.</h1>
            <p class="corp-hero__lead">
                Eine klare, sichere und moderne Plattform für Kunden, Partner und interne Teams. Alles, was Sie brauchen, um Tickets schneller zu lösen, Wissen zu teilen und Kommunikation auf Premium-Niveau zu halten.
            </p>
            <div class="corp-hero__actions">
                <?php $system = App\Model\helpdesk\Settings\System::where('id', '=', '1')->first(); ?>
                @if($system != null && $system->status == 1)
                    <a href="{!! URL::route('form') !!}" class="btn btn-custom btn-primary">{!! Lang::get('lang.submit_a_ticket') !!}</a>
                @endif
                <a href="{{url('/knowledgebase')}}" class="btn btn-outline">{!! Lang::get('lang.knowledge_base') !!}</a>
                <a href="{{url('mytickets')}}" class="btn btn-outline">{!! Lang::get('lang.my_tickets') !!}</a>
                @if(!Auth::user())
                    <a href="{{url('auth/register')}}" class="btn btn-outline">{!! Lang::get('lang.register') !!}</a>
                @endif
            </div>
            <div class="corp-hero__meta">
                <div class="corp-hero__meta-card">
                    <strong>24/7 Ticketfluss</strong>
                    <div class="corp-muted">Transparente Priorisierung, automatische Zuweisung und klare SLA-Übersicht.</div>
                </div>
                <div class="corp-hero__meta-card">
                    <strong>Knowledge Base</strong>
                    <div class="corp-muted">Wissen zentral bündeln, Artikel finden, Lösungen teilen.</div>
                </div>
            </div>
        </div>
    </section>

    <section class="corp-section">
        <div class="corp-section__header">
            <h2 class="corp-section__title">Einheitliches Erlebnis für Kunden & Teams</h2>
            <p class="corp-section__subtitle">Eine durchgängige Oberfläche, die sowohl im Customer Portal als auch im Agent Backend dieselbe Sprache spricht.</p>
        </div>
        <div class="corp-card-grid">
            <div class="corp-card">
                <div class="corp-card__title">Schneller Einstieg</div>
                <p class="corp-card__text">Klare Navigation, reduzierte Komplexität und ein Fokus auf die nächsten Schritte.</p>
            </div>
            <div class="corp-card">
                <div class="corp-card__title">Strukturierte Workflows</div>
                <p class="corp-card__text">Tickets, Wissensartikel und Benachrichtigungen bleiben konsistent sichtbar.</p>
            </div>
            <div class="corp-card">
                <div class="corp-card__title">Corporate-Ready</div>
                <p class="corp-card__text">Design Tokens, starke Kontraste und professionelle Oberflächen für jedes Gerät.</p>
            </div>
        </div>
    </section>

    <section class="corp-section">
        <div class="corp-cta">
            <div>
                <h3 class="corp-cta__title">Bereit für Premium Support?</h3>
                <p class="corp-cta__text">Starten Sie mit einem Ticket oder öffnen Sie die Knowledge Base, um Antworten in Sekunden zu erhalten.</p>
            </div>
            <div class="corp-hero__actions">
                @if($system != null && $system->status == 1)
                    <a href="{!! URL::route('form') !!}" class="btn btn-custom btn-primary">{!! Lang::get('lang.submit_a_ticket') !!}</a>
                @endif
                <a href="{{url('/knowledgebase')}}" class="btn btn-outline">{!! Lang::get('lang.knowledge_base') !!}</a>
            </div>
        </div>
    </section>
</div>
<script type="text/javascript">
    $(function () {
        $('.dialogerror, .dialoginfo, .dialogalert').fadeIn('slow');
        $("form").bind("submit", function (e) {$(this).find("input:submit").attr("disabled", "disabled");});
        var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReducedMotion) {
            return;
        }
        var ticking = false;
        var heroLayer = document.querySelector('[data-parallax-layer]');
        function onScroll() {
            if (!heroLayer) {
                return;
            }
            if (!ticking) {
                window.requestAnimationFrame(function () {
                    var offset = window.scrollY * 0.25;
                    heroLayer.style.transform = 'translate3d(0,' + offset + 'px,0)';
                    ticking = false;
                });
                ticking = true;
            }
        }
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    });
</script>
<script type="text/javascript" >try {if (top.location.hostname != self.location.hostname) { throw 1; }} catch (e) { top.location.href = self.location.href; }</script>

@stop
