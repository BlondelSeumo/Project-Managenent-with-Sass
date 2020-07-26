@php(\App::setLocale( basename(App::getLocale())))
@foreach($messages as $message)
    @if($message->from_data)
        <a href="{{route('chats.index',$currantWorkspace->slug)}}" class="dropdown-item dropdown-item-unread">
            <div class="dropdown-item-avatar">
                <img alt="image" @if($message->from_data->avatar) src="{{asset('/storage/avatars/'.$message->from_data->avatar)}}" @else avatar="{{ $message->from_data->name }}" @endif class="rounded-circle">
            </div>
            <div class="dropdown-item-desc">
                <b>{{$message->from_data->name}}</b>
                <p>{!! $message->message !!}</p>
                <div class="time">{{$message->created_at->diffForHumans()}}</div>
            </div>
        </a>
    @endif
@endforeach