@extends('_layouts.master')

@section('body')

  @include('_partials.header')

  <hr class="mx-4 border border-grey-light">

  <div class="m-4">
    @foreach ($posts as $post)
    <div>
      <p class="text-grey text-sm mb-2">{{ date('d/m/Y', $post->date) }}</p>
      <h2>
        <a href="{{ $post->getUrl() }}" class="no-underline text-grey-darkest">{{ $post->title }}</a>
      </h2>
      <p class="text-grey text-sm my-3 text-center">
        &#9679;&#9679;&#9679;
      </p>
    </div>
    @endforeach
  </div>
@endsection
