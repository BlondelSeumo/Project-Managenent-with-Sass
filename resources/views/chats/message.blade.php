<div class="message-wrapper chat-content rounded">
    @if(count($messages) > 0)
        <ul class="messages pl-1">
            @foreach($messages as $message)
                <li class="message clearfix">
                    <div class="{{ ($message->from == Auth::id()) ? 'sent' : 'received' }}">
                        <p>{{ $message->message }}</p>
                        <p class="date">{{ $message->created_at->diffForHumans() }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <h3 class="text-center mt-5 pt-5">{{__('No Message Found.!')}}</h3>
    @endif
</div>
<div class="card chat-box">
    <div class="card-footer chat-form">
        <input type="text" class="submit form-control" name="message"  placeholder="Type a message">
        <button class="btn btn-primary">
            <i class="fa fa-paper-plane"></i>
        </button>
    </div>
</div>
