@extends('layouts.app')

@section('title')
    <title>Dashboard - BlogBits</title>
@endsection

@section('content')

    @include('sidebar')

    <div class="grid col-xs-12 col-sm-12 col-lg-4 col-md-4">
        @foreach($blog->posts as $post)

            @foreach($post->photos as $photo)
                <div class="post">
                    <div class="thumbnail">

                        <img src="{{$photo->original_size->url}}">
                        <div class="caption">
                            <h3>{!! $post->caption !!} <small>{{$post->note_count}} Notes</small></h3>
                        </div>

                        <div class="actions">
                            <input type="text" class="form-control tags" data-id= data-role="tagsinput" value="{{implode (", ", $post->tags)}}" required>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach

        <ul class="pagination">
            <li>
                <a href="/tumblr/?offset={{$offset}}" rel="next">>></a>
            </li>
        </ul>
    </div>
@endsection
