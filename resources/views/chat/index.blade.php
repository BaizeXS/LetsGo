@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/chat.css') }}">
@endsection

@section('content')
<div class="chat-page-container">
    <div id="chat-container">
        <ul id="chat-messages">
            <li class="ai-message">Hi! I'm your travel assistant. How can I help you?</li>
        </ul>
    </div>

    <form id="chat-form">
        <div class="input-row">
            <input type="text" id="message-input" placeholder="Ask me anything..." autocomplete="off">
            <button type="submit" id="send-button" title="Send">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>

        <div class="button-row">
            <button type="button" id="tip-btn" title="Get travel tips"><i class="fas fa-lightbulb"></i></button>
            <button type="button" id="plan-btn" title="Generate 7-day random travel plan"><i class="fas fa-calendar-alt"></i></button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="{{ asset('js/chat.js') }}"></script>
@endpush