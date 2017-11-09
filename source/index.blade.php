@extends('_layouts.master')

@section('body')

  @include('_partials.header')

  <div class="m-2 border-t border-b py-4">
    @foreach ($posts as $post)
    <div class="mb-3">
      <p class="text-grey text-sm mb-2">{{ date('d/m/Y', $post->date) }}</p>
      <h2>
        <a href="{{ $post->getUrl() }}" class="no-underline text-grey-darkest">{{ $post->title }}</a>
      </h2>
      <p class="mt-2 text-sm leading-tight">
        <a href="{{ $post->getUrl() }}" class="no-underline text-grey-darker">
          {!! $post->short !!}
        </a>
      </p>
      <p class="text-center text-2xl">
        . . .
      </p>
    </div>
    @endforeach
  </div>
@endsection
