@extends('layouts.marketing')

@section('title', 'Pricing | Brenora customer revenue intelligence')
@section('description', 'Compare Brenora Launch, Growth, and Enterprise plans. Choose monthly or annual billing and start with a 14-day free trial.')
@section('og_title', 'Brenora Pricing | Plans for customer revenue teams')
@section('canonical', route('pricing'))

@section('content')
    <section class="pricing-hero">
        <div class="section-inner">
            <p class="eyebrow">Plans for customer-facing teams</p>
            <h1>Make customer context your next advantage.</h1>
            <p>Choose the workspace that matches your team today. Every plan begins with a 14-day free trial.</p>
            <a class="button button-primary" href="#pricing">Compare plans <span aria-hidden="true">↓</span></a>
        </div>
    </section>
    @include('marketing.partials.pricing')
    @include('marketing.partials.faq')
    @include('marketing.partials.contact')
@endsection
